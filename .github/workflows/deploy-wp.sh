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

echo "➤ Building Webpack bundle"
export NODE_OPTIONS=--openssl-legacy-provider
npm install || exit 1
npm run build-prod || exit 1

echo "➤ Copying files to build directory"
rsync -av --delete "${CURRENT_DIR}/${SLUG}/" "${BUILD_DIR}/"

echo "➤ Install composer dependencies without dev dependencies"
cd "${BUILD_DIR}" || exit 1
composer install --no-dev --prefer-dist -o --no-interaction || exit 1
cd "${CURRENT_DIR}" || exit 1

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
