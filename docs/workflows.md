# Github Action Workflows

There are several Github actions:

1. analysis.yml
    - This runs [phpstan](https://phpstan.org/) and [checks backwards compatibility with older versions of php](https://github.com/PHPCompatibility/PHPCompatibilityWP).
    - This runs on all pull requests.
1. test.yml
    - This runs [phpunit](https://phpunit.de/) tests.
    - These tests can run locally. See [WordPress Tests section of local dev docs](./local-dev.md)
    - This runs on all pull requests.
1. main.yml
    - This [releases updates to WordPress.org](https://github.com/10up/action-wordpress-plugin-deploy).
    - This runs when a new tag is added.
1. version.yml
    - This checks if the "Tested up to" value is older than latest version of WordPress.
    - See [release process docs](./release-process.md)
    - This runs once a day.
