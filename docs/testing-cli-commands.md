# CLI Commands For Testing


## `latest-wordpress`

Gets latest version of WordPress via API. Other commands will use same API lookup if --version param not passed.

Examples:
- `docker-compose run wpcli wp latest-wordpress`
- `docker-compose run wpcli wp latest-wordpress`
## `is-tested-up-to`

Checks if the plugin's tested up to version is greater than or equal to latest version of WordPress.

Examples:
- `docker-compose run wpcli wp is-tested-up-to`
- `docker-compose run wpcli wp is-tested-up-to --version=4.2`

## `update-version`

Gets latest version of WordPress. Sets the "Tested up to" value in README.txt to latest version of WordPress. Sets stable tag in README.txt and version in xcloner.php to one minor version higher.

Examples:
- `docker-compose run wpcli wp update-version`
- `docker-compose run wpcli wp --version=4.2`

Probably need to run this first:

```bash
sudo chmod 777 xcloner.php
sudo chmod 777 README.txt
```
