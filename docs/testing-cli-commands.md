# CLI Commands For Testing

## `is-tested-up-to`

Checks if the plugin's tested up to version is greater than or equal to latest version of WordPress.

Example:
`docker-compose run wpcli wp is-tested-up-to`

## `update-version`

Gets latest version of WordPress. Sets the "Tested up to" value in README.txt to latest version of WordPress. Sets stable tag in README.txt and version in xcloner.php to one minor version higher.

Example:
`docker-compose run wpcli wp update-version`

Probably need to run this first:

```bash
sudo chmod 777 xcloner.php
sudo chmod 777 README.txt
```
