# Release Process

## Starting Release

### Releases Tracking WordPress Version Updates

The git workflow ".version.yml" runs once a day, using a [cron trigger](https://docs.github.com/en/actions/learn-github-actions/events-that-trigger-workflows#schedule). This workflow checks if the current value of "Tested up to" is the less than the latest version of WordPress. If it is not, this workflow will do the following:

- Create a new branch.
- Incriment the "Stable tag" by 1 patch version.
- Set the "Tested up to" value to the latest version.
- Create a pull request title "UPDATE For WordPress $VERSION" where $VERSION is the latest version of WordPress.

The test and analysis pipelines will run on the pull request. If they pass, advance to manual testing of this version. If not, we will need to examine why those pipelines failed before moving forward. Work to fix this should be done in a branch based off of the PR's branch.

### Other Releases

If a release is being done for other reasons, a pull request to update the - branch should be made. The rest of this workflow.

## Manual Testing

To test the pull request for the new version:

- Checkout the repo locally, at the same branch as the pull request.
- Build plugin the same way as in "main.yml".
    - `npm install`
    - `npm run build-prod`
    - `composer install --no-dev --prefer-dist -o`

## Trigger Release To WordPress.org/plugins

When the release is ready:

- Merge the pull request to the master branch
- Apply and push a tag to the latest commit of master branch
    - Tag must be the same as the value of "Stable tag".
    - `git tag 1.0.0 && git push --tags`
- This will trigger the "main.yml" workflow.
    - This should create a new release in [WordPress plugin repo](https://wordpress.org/plugins/xcloner-backup-and-restore/).
