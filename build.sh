SCRIPT_DIR="$( dirname -- "$0"; )"
SRC_DIR="${SCRIPT_DIR}/xcloner-backup-and-restore"
BUILD_DIR="${SCRIPT_DIR}/xcloner-backup-and-restore-build"

DATE=$(date +"%Y%m%d%H%M")

echo "Building Webpack bundle"

npm i
npm run build-prod

echo "Moving source files to build directory"

rsync --exclude-from=".distignore" -av --delete "${SRC_DIR}/" "${BUILD_DIR}/"

echo "Install composer dependencies without dev dependencies"
cd "${BUILD_DIR}"
/bin/php7.3 /usr/local/bin/composer install --no-dev --prefer-dist -o
cd ..

echo "Removing unnecessary files from vendor directory"
rsync --exclude-from=".distignore" -av --delete "${BUILD_DIR}/" "${BUILD_DIR}/xcloner-backup-and-restore/"

echo "Creating archive"
cd "${BUILD_DIR}"
zip -r "xcloner-backup-and-restore-${DATE}.zip" "xcloner-backup-and-restore"
mv "xcloner-backup-and-restore-${DATE}.zip" ../
cd ..

echo "Removing build directory"
rm -rf "${BUILD_DIR}"
