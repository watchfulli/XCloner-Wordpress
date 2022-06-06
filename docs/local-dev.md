# Local Development

## Install For Development

- `git clone git@github.com:watchfulli/XCloner-Wordpress.git`
- `npm install`
- Install JavaScript Dependencies:
    - If testing:
        - `npm run build-prod` will build for production.
    -    `npm run build` does not do anything.
    - If doing development:
        - `npm run start` Starts watcher
- Install Composer Dependencies
    - If installing for testing:
        - `docker run --rm -it --volume "$(pwd)":/app prooph/composer:7.3 install --no-dev`
    - If installing for development:
        - `docker run --rm -it --volume "$(pwd)":/app prooph/composer:7.3 install`

## Local Development Environment
A [docker-compose](https://docs.docker.com/samples/wordpress/)-based local development environment is provided.

- Start server
    - `docker-compose up -d`
    - If you get errors about port already being allocated, you can either:
        - Kill all containers and try again: `docker kill $(docker ps -q) && docker-compose up -d`
        - Change the port in docker-compose.yml.
- Access Site
    - [http://localhost:6123](http://localhost:6123)
- Run WP CLI command:
    - `docker-compose run wordpress-cli wp user create admin admin@example.com --role=admin user_pass=pass`

## WordPress Tests

There is a special phpunit container for running WordPress tests, with WordPress and MySQL configured.

- Enter container
    - `docker-compose run phpunit`
- Test
    - `composer test:wordpress`

## Debug Logging

Because most of what xCloner does happens via asynchronous requests, logging PHP errors is important for identifying bugs.

By default, xCloner registers its own error handlers. This will prevent WordPress default error logging with WP_DEBUG_LOG from working as expected.

You can use that log, or you can disable it in xCloner's "Settings" screen.

- @todo find or create documentation about the PHP logging feature in xCloner.
- [Recent post about WordPress debug logging from Delicious Brains](https://deliciousbrains.com/why-use-wp-debug-log-wordpress-development/)
- [Post about WordPress debug logging I wrote for Elegant Themes awhile ago](https://www.elegantthemes.com/blog/tips-tricks/using-the-wordpress-debug-log)
