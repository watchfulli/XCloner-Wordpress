#!/usr/bin/env bash
set -euo pipefail

WORK_DIR=$(mktemp -d /tmp/xcloner-build.XXXXXX)
trap 'rm -rf "$WORK_DIR"' EXIT

# Never reuse dependencies, generated assets or local configuration from the host.
rsync -a --no-owner --no-group \
    --exclude='.git/' --exclude='.env' --exclude='.env.*' \
    --exclude='node_modules/' --exclude='/xcloner-backup-and-restore/vendor/' \
    --exclude='/build/' --exclude='/xcloner-backup-and-restore-build/' \
    --exclude='/xcloner-backup-and-restore-svn/' --exclude='/svn-deploy/' \
    --exclude='/xcloner-backup-and-restore*.zip' --exclude='/php-scoper.phar' \
    /source/ "$WORK_DIR/"
cd "$WORK_DIR"

node --version
php --version
composer --version
npm ci --no-audit --no-fund
bash build.sh

archives=("$WORK_DIR"/xcloner-backup-and-restore-*.zip)
[[ ${#archives[@]} -eq 1 && -f "${archives[0]}" ]] || {
    echo "Expected exactly one build archive." >&2
    exit 1
}
unzip -tq "${archives[0]}"
archive_name=$(basename "${archives[0]}")
[[ ! -e "/output/$archive_name" ]] || {
    echo "Archive already exists: $archive_name" >&2
    exit 1
}
cp "${archives[0]}" "/output/$archive_name"
echo "Build available: $archive_name (project root)"
