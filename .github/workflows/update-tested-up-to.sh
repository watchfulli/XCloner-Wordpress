#!/bin/bash

if [[ -z "$SVN_USERNAME" ]]; then
	echo "x Missing SVN_USERNAME env variable"
	exit 1
fi

if [[ -z "$SVN_PASSWORD" ]]; then
	echo "x Missing SVN_PASSWORD env variable"
	exit 1
fi

SLUG="xcloner-backup-and-restore"
README_FILE_PATH="./xcloner-backup-and-restore/README.txt"
SVN_URL="https://plugins.svn.wordpress.org/${SLUG}/"
SVN_DIR="./svn-${SLUG}"

echo "➤ Checking out SVN repository..."
svn checkout "$SVN_URL" "$SVN_DIR" --depth immediates

svn update --set-depth infinity "$SVN_DIR/trunk"

echo "➤ Coping README file to SVN repository..."
rsync -rc "$README_FILE_PATH" "${SVN_DIR}/trunk" --delete --delete-excluded

echo "➤ Adding files to SVN repository..."
cd "$SVN_DIR"
svn add . --force > /dev/null

echo "➤ Committing files to SVN repository..."
svn commit -m "Update tested up to" --no-auth-cache --non-interactive  --username "$SVN_USERNAME" --password "$SVN_PASSWORD"

echo "✓ Updated WordPress tested up to version!"
