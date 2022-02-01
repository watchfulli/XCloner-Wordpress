# Testing Pull Requests

This is documentation for testing pull requests (PRs) for xcloner.

https://github.com/watchfulli/XCloner-Wordpress/pulls

When testing, you must install the plugin using the process documented in [the local development documentation](./local-dev.md). If you do not run composer update, as instructed after checking out the branch, your testing will not be valid. It is OK to subsitute your own local environment for the one that is included.

## Setting Up To Test

1. Checkout the repo from [Github](https://github.com/watchfulli/XCloner-Wordpress)
1. If you've already cloned it, reset your current branch:
    - `git reset HEAD --hard`
1. Identify which branch the PR the branch is for.
1. Switch to that branch
    - `git fetch origin name-of-branch`
    - `git checkout name-of-branch`
1. Run the installation steps documented in [the local development documentation](./local-dev.md).
1. In your test WordPress site, activate the plugin.
1. Attempt to reproduce the bug, using the steps listed in the original issue that the [bug was reported in](./reporting-bugs.md)
1. If the bug is no longer present, comment on the issue explaining how you tested.
    - Make sure to include your version of PHP and WordPress.
1. If the bug is still present, comment on the issue explaining how you tested and what PHP and JavaScript errors you encountered.
    - Make sure to include your version of PHP and WordPress.
