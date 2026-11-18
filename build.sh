#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")"

DATE=$(date +"%Y%m%d%H%M%S")
SLUG="xcloner-backup-and-restore"
BUILD_ONLY="true"
BUILD_SCRIPT_PATH="./.github/workflows/deploy-wp.sh"
BUILD_DIR="xcloner-backup-and-restore-build"

export BUILD_ONLY

echo "Building plugin"

bash "${BUILD_SCRIPT_PATH}"

echo "Creating archive"
cd "${BUILD_DIR}" || exit 1
mkdir "${SLUG}"
shopt -s dotglob nullglob
for entry in *; do
    [[ "$entry" == "$SLUG" ]] || mv -- "$entry" "${SLUG}/"
done
zip -r "xcloner-backup-and-restore-${DATE}.zip" "${SLUG}" || exit 1
mv "xcloner-backup-and-restore-${DATE}.zip" ../
cd ..

echo "Removing build directory"
rm -rf "${BUILD_DIR}"
