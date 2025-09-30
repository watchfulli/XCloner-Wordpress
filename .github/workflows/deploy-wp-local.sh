#!/bin/bash

set -e

SLUG="xcloner-backup-and-restore"
SVN_URL="https://plugins.svn.wordpress.org/${SLUG}"
VERSION="4.8.0"
BATCH_SIZE=500  # Number of files per commit
MAIN_FILE="xcloner.php"  # Main file to exclude and commit last

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}→${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

validate_environment() {
    [[ -z "$VERSION" ]] && print_error "Usage: $0 VERSION" && exit 1
    [[ ! -d "xcloner-backup-and-restore-build" ]] && print_error "Missing build directory" && exit 1
    
    if [[ -z "$SVN_USERNAME" ]]; then
        read -p "SVN Username: " SVN_USERNAME
        read -sp "SVN Password: " SVN_PASSWORD
        echo
    fi
}

checkout_svn() {
    print_status "Preparing SVN"
    rm -rf svn-deploy
    svn checkout --quiet "${SVN_URL}/trunk" svn-deploy/trunk
}

sync_files() {
    print_status "Syncing files"
    rsync -a --delete --exclude=".svn" xcloner-backup-and-restore-build/xcloner-backup-and-restore/ svn-deploy/trunk/
    cd svn-deploy/trunk
}

prepare_changes() {
    print_status "Adding new files"
    svn status | grep '^?' | awk '{print $2}' | xargs -r svn add --parents --quiet

    print_status "Removing deleted files"
    svn status | grep '^!' | awk '{sub(/^! +/, ""); print}' | xargs -r -d '\n' -I{} svn rm --force --quiet "{}@"
}

commit_svn() {
    local message="$1"
    local files="$2"

    if ! svn commit -m "$message" ${files:+$files} \
        --no-auth-cache \
        --non-interactive \
        --username "$SVN_USERNAME" \
        --password "$SVN_PASSWORD" \
        --config-option=servers:global:http-timeout=600; then
        print_error "SVN commit failed: $message"
        return 1
    fi

    return 0
}

get_modified_files() {
    # Gets all modified files (excluding the main file if specified)
    svn status | grep -E '^[AM]' | awk '{print $2}' | while read -r file; do
        if [[ -n "$MAIN_FILE" && "$file" == "$MAIN_FILE" ]]; then
            continue
        fi
        echo "$file"
    done
}

commit_in_batches() {
    local files=()
    local total_count=0
    local batch_count=0
    local committed_files=0
    
    # Read all modified files into an array
    while IFS= read -r file; do
        files+=("$file")
        ((total_count++))
    done < <(get_modified_files)

    if [[ $total_count -eq 0 ]]; then
        print_warning "No files to commit (excluding $MAIN_FILE)"
        return 0
    fi

    print_status "Found $total_count files to commit in batches of $BATCH_SIZE"

    # Process files in batches
    for ((i=0; i<total_count; i+=BATCH_SIZE)); do
        ((batch_count++))
        local batch=()
        local end_idx=$((i + BATCH_SIZE - 1))

        # Limit end_idx to total number of files
        if [[ $end_idx -ge $total_count ]]; then
            end_idx=$((total_count - 1))
        fi

        # Build current batch
        for ((j=i; j<=end_idx; j++)); do
            file="${files[j]}"
            # Se il file contiene @, aggiungi un @ finale
            [[ "$file" == *"@"* ]] && file="${file}@"
            batch+=("$file")
        done

        local files_in_batch=${#batch[@]}
        ((committed_files += files_in_batch))

        print_status "Commit batch $batch_count ($files_in_batch files) - Progress: $committed_files/$total_count ($(( committed_files * 100 / total_count ))%)"

        if commit_svn "v${VERSION} - batch $batch_count" "${batch[*]}"; then
            print_success "Batch $batch_count committed successfully"
        else
            print_error "Error committing batch $batch_count"
            return 1
        fi

    done

    print_success "All batches committed: $batch_count batches, $committed_files files"
}

commit_main_file() {
    if [[ -n "$MAIN_FILE" ]] && svn status "$MAIN_FILE" 2>/dev/null | grep -q '^[AM]'; then
        print_warning "Main file '$MAIN_FILE' was excluded from previous commits"
        echo -n "Do you want to commit the file '$MAIN_FILE' now? (y/N): "
        read -r response

        if [[ "$response" =~ ^[Yy]$ ]]; then
            print_status "Committing main file '$MAIN_FILE'"
            if commit_svn "v${VERSION} - main file ($MAIN_FILE)" "$MAIN_FILE"; then
                print_success "Main file '$MAIN_FILE' committed successfully"
            else
                print_error "Error committing main file '$MAIN_FILE'"
                return 1
            fi
        else
            print_warning "Main file '$MAIN_FILE' NOT committed"
        fi
    fi
}

create_tag() {
    cd ../

    print_status "Creating tag"
    if ! svn cp "${SVN_URL}/trunk" "${SVN_URL}/tags/${VERSION}" \
        -m "Tag v${VERSION}" \
        --no-auth-cache \
        --non-interactive \
        --username "$SVN_USERNAME" \
        --password "$SVN_PASSWORD"; then
        print_error "Failed to create tag v${VERSION}"
        exit 1
    fi

    print_success "Tag v${VERSION} created successfully"
}

main() {
    validate_environment
    checkout_svn
    sync_files
    prepare_changes
    
    print_status "Committing files in batches"
    if ! commit_in_batches; then
      print_error "Batch commit failed"
      exit 1
    fi

    if ! commit_main_file; then
      print_error "Failed to commit main file"
      exit 1
    fi

    create_tag
    print_success "Deploy completed!"
}

# Execute main only if script is called directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi