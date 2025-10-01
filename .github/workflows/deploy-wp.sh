#!/bin/bash

if [[ -z "$SVN_USERNAME" ]] && [[ -z "$BUILD_ONLY" ]]; then
	echo "x Missing SVN_USERNAME env variable"
	exit 1
fi

if [[ -z "$SVN_PASSWORD" ]] && [[ -z "$BUILD_ONLY" ]]; then
	echo "x Missing SVN_PASSWORD env variable"
	exit 1
fi

if [[ -z "$VERSION" ]]; then
	VERSION="${GITHUB_REF#refs/tags/}"
	VERSION="${VERSION#v}"
fi

rx='^([0-9]+\.){2}(\*|[0-9]+)(-.*)?$'
if [[ $VERSION =~ $rx ]]; then
  echo "ℹ VERSION is $VERSION"
elif [[ -z "$BUILD_ONLY" ]]; then
  echo "x Unable to validate version: '$VERSION'"
  exit 1
fi

CURRENT_DIR=$(pwd)
SLUG="xcloner-backup-and-restore"

SVN_URL="https://plugins.svn.wordpress.org/${SLUG}/"
SVN_DIR="${CURRENT_DIR}/${SLUG}-svn"
BUILD_DIR="${CURRENT_DIR}/${SLUG}-build"

PHPSCOPER_VERSION="0.15.0"
PHPSCOPER_PHAR="php-scoper.phar"
PHPSCOPER_URL="https://github.com/humbug/php-scoper/releases/download/${PHPSCOPER_VERSION}/php-scoper.phar"

echo "➤ Building Webpack bundle"
export NODE_OPTIONS=--openssl-legacy-provider
npm install || exit 1
npm run build-prod || exit 1

echo "==> Ensuring php-scoper is available"
if [ ! -f "$PHPSCOPER_PHAR" ]; then
  echo "Downloading php-scoper..."
  if command -v curl >/dev/null 2>&1; then
    curl -fsSL "$PHPSCOPER_URL" -o "$PHPSCOPER_PHAR" || { echo "x Download failed"; exit 1; }
  elif command -v wget >/dev/null 2>&1; then
    wget -q -O "$PHPSCOPER_PHAR" "$PHPSCOPER_URL" || { echo "x Download failed"; exit 1; }
  else
    echo "x Neither curl nor wget found";
    exit 1
  fi
  chmod +x "$PHPSCOPER_PHAR"
fi

echo "➤ Installing composer dependencies (excluding dev) for scoping"
composer install --no-dev --prefer-dist -o --no-interaction --working-dir="${SLUG}" --ignore-platform-reqs || exit 1

echo "➤ Running PHP-Scoper to prefix namespaces"
php "${PHPSCOPER_PHAR}" add-prefix --config "${CURRENT_DIR}/scoper.inc.php" --output-dir "${CURRENT_DIR}/build" --force || exit 1

echo "➤ Preparing build directory from PHP-Scoper output"
rm -rf "${BUILD_DIR}"
mkdir -p "${BUILD_DIR}"
rsync -a --delete "${CURRENT_DIR}/build/" "${BUILD_DIR}/"

echo "➤ Copying non-scoped admin directory into scoped build"
if [[ -d "${CURRENT_DIR}/${SLUG}/admin" ]]; then
  mkdir -p "${BUILD_DIR}/admin"
  rsync -a --delete --exclude 'class-xcloner-admin.php' "${CURRENT_DIR}/${SLUG}/admin/" "${BUILD_DIR}/admin/"
  if [[ -f "${CURRENT_DIR}/build/admin/class-xcloner-admin.php" ]]; then
    cp -f "${CURRENT_DIR}/build/admin/class-xcloner-admin.php" "${BUILD_DIR}/admin/class-xcloner-admin.php"
  elif [[ -f "${CURRENT_DIR}/build/${SLUG}/admin/class-xcloner-admin.php" ]]; then
    cp -f "${CURRENT_DIR}/build/${SLUG}/admin/class-xcloner-admin.php" "${BUILD_DIR}/admin/class-xcloner-admin.php"
  else
    echo "Scoped admin class not found in build output!"
    exit 1
  fi
fi

echo "➤ Checking if composer.json/vendor exist in build directory"
if [[ -f "${BUILD_DIR}/composer.json" && -d "${BUILD_DIR}/vendor" ]]; then
  echo "➤ Optimizing autoload in build directory"
  (cd "${BUILD_DIR}" && composer dump-autoload -o --no-interaction) || exit 1
else
  echo "⚠ Skipping autoload optimization (composer.json or vendor dir missing)"
fi

if [[ -n "$BUILD_ONLY" ]]; then
  echo "✓ Plugin built!"
  exit 0
fi

echo "➤ Checking out SVN repository..."
svn checkout "${SVN_URL}/trunk/" "${SVN_DIR}/trunk/"
cd "${CURRENT_DIR}" || exit 1

echo "➤ Coping files to SVN repository..."
rsync -r --exclude-from=".distignore" "${BUILD_DIR}/" "${SVN_DIR}/trunk/"

echo "➤ Adding files to SVN repository..."
cd "${SVN_DIR}/trunk" || exit 1
svn st | grep '^?' | awk '{print $2}' | xargs -r svn add
svn st | grep '^!' | awk '{print $2}' | xargs -r svn rm

echo "➤ Committing files to SVN repository..."
svn commit -m "v${VERSION}" --no-auth-cache --non-interactive  --username "${SVN_USERNAME}" --password "${SVN_PASSWORD}" --config-option=servers:global:http-timeout=300 || exit 1

echo "➤ Coping tag to SVN repository..."
cd "${SVN_DIR}" || exit 1
svn cp "${SVN_URL}/trunk" "${SVN_URL}/tags/${VERSION}" -m "Tagging v${VERSION}" --no-auth-cache --non-interactive  --username "${SVN_USERNAME}" --password "${SVN_PASSWORD}" --config-option=servers:global:http-timeout=300 || exit 1

echo "✓ Plugin deployed!"
