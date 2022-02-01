# Reporting Bugs

Whenever you find a bug, [please open a GitHub issue for it](https://github.com/watchfulli/XCloner-Wordpress/issues/new). If you are opening an issue after reading a support thread on WordPress.org, please include a link to that thread. Also, indicate if you have reproduced the bug yourself, or if you are only passing on a report.

## What To Include

Begin by stating what you were doing, what you expected to happend and what happened instead. Please include a list of steps to reproduce the error.

In the issue, please note what versions of the following you are testing with:
- WordPress
- xCloner
- PHP

Please do not write "latest" version, use a version number always. If you are testing a branch from the Github repo, please note which branch, at which commit you have checked out. Also indicate if you installed using the process documented in [the local development documentation](./local-dev.md).


### Error Logging.

Please  always include what PHP errors happened with WP_DEBUG enabled and what JavaScript console errors happened. If they didn't, or you don't know, include that. The [local dev](./local-dev.md) documentation includes notes about setting up debug logging, which is probably neccasary for testing.

For JavaScript console errors, please provide __the text of any errors__. Please do not include a screenshot of the terminal or all of the text in the JavaScript console. Please do not include warnings, or other text in the JavaScript console, unless you belive it is relevant.
