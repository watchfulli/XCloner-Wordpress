# Changelog

## [4.2.0](https://github.com/watchfulli/XCloner-Wordpress/tree/4.2.0) (2020-06-01)

[Full Changelog](https://github.com/watchfulli/XCloner-Wordpress/compare/4.1.5...4.2.0)

**Implemented enhancements:**

- Improve error when connecting to remote site using restore script from another site  [\#89](https://github.com/watchfulli/XCloner-Wordpress/issues/89)
- Improve error message when accessing restore script directly [\#86](https://github.com/watchfulli/XCloner-Wordpress/issues/86)
- Encrypt database password during restore [\#84](https://github.com/watchfulli/XCloner-Wordpress/issues/84)
- Update default regex exclusions [\#78](https://github.com/watchfulli/XCloner-Wordpress/issues/78)
- Replace youtube links [\#76](https://github.com/watchfulli/XCloner-Wordpress/issues/76)
- Add additional cleanup & quota options for backup storage [\#61](https://github.com/watchfulli/XCloner-Wordpress/issues/61)
- Add standalone CLI for making backups [\#60](https://github.com/watchfulli/XCloner-Wordpress/issues/60)
- Move `send to remote destination` option to the `Backup Options` tab [\#56](https://github.com/watchfulli/XCloner-Wordpress/issues/56)

**Fixed bugs:**

- Select All Backups delete does not work [\#92](https://github.com/watchfulli/XCloner-Wordpress/issues/92)
- JS Error during restoration [\#91](https://github.com/watchfulli/XCloner-Wordpress/issues/91)
- Ajax error when viewing empty storage area [\#79](https://github.com/watchfulli/XCloner-Wordpress/issues/79)
- Javascript error when running a backup, and clicking "Send Backup to remote storage" [\#74](https://github.com/watchfulli/XCloner-Wordpress/issues/74)
- SFTP bug [\#72](https://github.com/watchfulli/XCloner-Wordpress/issues/72)
- Live DB restore replaces WP Options table [\#69](https://github.com/watchfulli/XCloner-Wordpress/issues/69)

**Closed issues:**

- Rename `Cleanup by Date\(days\)` [\#85](https://github.com/watchfulli/XCloner-Wordpress/issues/85)
- Add `Local Storage` to the `Remote Storage` area? [\#81](https://github.com/watchfulli/XCloner-Wordpress/issues/81)
- Bug selecting the time in the `schedule backup` tab [\#80](https://github.com/watchfulli/XCloner-Wordpress/issues/80)
- Update Remote Storage names [\#77](https://github.com/watchfulli/XCloner-Wordpress/issues/77)
- Small JS bug in Manage Backups interface [\#68](https://github.com/watchfulli/XCloner-Wordpress/issues/68)
- New settings option: delete remote file when deleting backup [\#66](https://github.com/watchfulli/XCloner-Wordpress/issues/66)

**Merged pull requests:**

- Milestone4.2.0 [\#93](https://github.com/watchfulli/XCloner-Wordpress/pull/93) ([ovidiul](https://github.com/ovidiul))

## [4.1.5](https://github.com/watchfulli/XCloner-Wordpress/tree/4.1.5) (2020-05-14)

[Full Changelog](https://github.com/watchfulli/XCloner-Wordpress/compare/4.1.4...4.1.5)

**Implemented enhancements:**

- Add incremental extended inserts  [\#39](https://github.com/watchfulli/XCloner-Wordpress/issues/39)

**Fixed bugs:**

- Running a backup and sending to remote store ex, sFTP does not upload the backup file [\#73](https://github.com/watchfulli/XCloner-Wordpress/issues/73)

**Closed issues:**

- Replace dev links in files [\#59](https://github.com/watchfulli/XCloner-Wordpress/issues/59)
- Update developer link in the plugins area of site backend [\#58](https://github.com/watchfulli/XCloner-Wordpress/issues/58)
- Update footer in progress indicator [\#57](https://github.com/watchfulli/XCloner-Wordpress/issues/57)
- Improve language string for `backup before update` tooltip [\#55](https://github.com/watchfulli/XCloner-Wordpress/issues/55)
- Update readme.txt again [\#53](https://github.com/watchfulli/XCloner-Wordpress/issues/53)
- Replace README.txt and xcloner.php files [\#47](https://github.com/watchfulli/XCloner-Wordpress/issues/47)
- Local files not deleted after transfer to remote location [\#46](https://github.com/watchfulli/XCloner-Wordpress/issues/46)
- pull and push might be great way? [\#44](https://github.com/watchfulli/XCloner-Wordpress/issues/44)
- Confusing user experience when restoring a public site to a local site [\#43](https://github.com/watchfulli/XCloner-Wordpress/issues/43)

**Merged pull requests:**

- Milestone4.1.5 [\#75](https://github.com/watchfulli/XCloner-Wordpress/pull/75) ([ovidiul](https://github.com/ovidiul))

## [4.1.4](https://github.com/watchfulli/XCloner-Wordpress/tree/4.1.4) (2020-05-06)

[Full Changelog](https://github.com/watchfulli/XCloner-Wordpress/compare/untagged-0681671b0a71d1a1d73e...4.1.4)

**Implemented enhancements:**

- Remote Storage Security [\#15](https://github.com/watchfulli/XCloner-Wordpress/issues/15)
- Differential from last backup date [\#6](https://github.com/watchfulli/XCloner-Wordpress/issues/6)

**Closed issues:**

- \[Xcloner\_Standalone\] Remove schedule params from configuration [\#50](https://github.com/watchfulli/XCloner-Wordpress/issues/50)
- SFTP connection error [\#45](https://github.com/watchfulli/XCloner-Wordpress/issues/45)
- Feature: please add wp-cli support [\#42](https://github.com/watchfulli/XCloner-Wordpress/issues/42)
- Unable to connect WebDAV with a " in the password [\#41](https://github.com/watchfulli/XCloner-Wordpress/issues/41)
- Issues uploading backups to Dropbox [\#40](https://github.com/watchfulli/XCloner-Wordpress/issues/40)
- Differential backups, manual vs scheduled [\#35](https://github.com/watchfulli/XCloner-Wordpress/issues/35)
- Error Message: Unreadable file encountered: temporary file [\#34](https://github.com/watchfulli/XCloner-Wordpress/issues/34)
- BackblazeB2\Client' not found [\#27](https://github.com/watchfulli/XCloner-Wordpress/issues/27)
- Unable to use single Application Keys [\#26](https://github.com/watchfulli/XCloner-Wordpress/issues/26)
- How about file based backups? [\#25](https://github.com/watchfulli/XCloner-Wordpress/issues/25)
- Backup date from backups on backblaze is not valid [\#24](https://github.com/watchfulli/XCloner-Wordpress/issues/24)
- Does not upload to WebDav on owncloud [\#23](https://github.com/watchfulli/XCloner-Wordpress/issues/23)
-  Header does not match it's checksum for [\#22](https://github.com/watchfulli/XCloner-Wordpress/issues/22)
- Error on upload Backblaze -\> field bucketId cannot be null [\#21](https://github.com/watchfulli/XCloner-Wordpress/issues/21)
- phpMyAdmin Import Errors - Duplicate entry for key PRIMARY [\#17](https://github.com/watchfulli/XCloner-Wordpress/issues/17)
- generated backup file not valid [\#16](https://github.com/watchfulli/XCloner-Wordpress/issues/16)
- failing to upload large file to Backblaze B2 [\#14](https://github.com/watchfulli/XCloner-Wordpress/issues/14)
- numeric off\_t value expected [\#13](https://github.com/watchfulli/XCloner-Wordpress/issues/13)

**Merged pull requests:**

- Milestone4.1.4 [\#62](https://github.com/watchfulli/XCloner-Wordpress/pull/62) ([ovidiul](https://github.com/ovidiul))
- Bugfix: generate inserts with null values instead of empty values [\#37](https://github.com/watchfulli/XCloner-Wordpress/pull/37) ([jkroemer](https://github.com/jkroemer))
- Scrutinizer Auto-Fixes [\#33](https://github.com/watchfulli/XCloner-Wordpress/pull/33) ([scrutinizer-auto-fixer](https://github.com/scrutinizer-auto-fixer))
- Scrutinizer Auto-Fixes [\#32](https://github.com/watchfulli/XCloner-Wordpress/pull/32) ([scrutinizer-auto-fixer](https://github.com/scrutinizer-auto-fixer))
- Scrutinizer Auto-Fixes [\#31](https://github.com/watchfulli/XCloner-Wordpress/pull/31) ([scrutinizer-auto-fixer](https://github.com/scrutinizer-auto-fixer))
- Scrutinizer Auto-Fixes [\#30](https://github.com/watchfulli/XCloner-Wordpress/pull/30) ([scrutinizer-auto-fixer](https://github.com/scrutinizer-auto-fixer))
- Scrutinizer Auto-Fixes [\#28](https://github.com/watchfulli/XCloner-Wordpress/pull/28) ([scrutinizer-auto-fixer](https://github.com/scrutinizer-auto-fixer))

## [untagged-0681671b0a71d1a1d73e](https://github.com/watchfulli/XCloner-Wordpress/tree/untagged-0681671b0a71d1a1d73e) (2018-06-21)

[Full Changelog](https://github.com/watchfulli/XCloner-Wordpress/compare/4.0.6...untagged-0681671b0a71d1a1d73e)

**Closed issues:**

- backup files on WEBDAV remote storage are not recognized as locally existing [\#20](https://github.com/watchfulli/XCloner-Wordpress/issues/20)
- backup files on WEBDAV remote storage doesn't appear in list [\#19](https://github.com/watchfulli/XCloner-Wordpress/issues/19)
- How to do a remote target systems restore [\#18](https://github.com/watchfulli/XCloner-Wordpress/issues/18)
- Gdrive Backup Not Working [\#10](https://github.com/watchfulli/XCloner-Wordpress/issues/10)
- Document whether this supports multisite [\#8](https://github.com/watchfulli/XCloner-Wordpress/issues/8)

## [4.0.6](https://github.com/watchfulli/XCloner-Wordpress/tree/4.0.6) (2017-12-18)

[Full Changelog](https://github.com/watchfulli/XCloner-Wordpress/compare/4.0.1...4.0.6)

**Closed issues:**

- Please correct Backblaze - no camel case [\#12](https://github.com/watchfulli/XCloner-Wordpress/issues/12)
- Azure Blog Storage? [\#11](https://github.com/watchfulli/XCloner-Wordpress/issues/11)
- Xcloner-Wordpress allows upgrade when PHP version is not appropriate [\#9](https://github.com/watchfulli/XCloner-Wordpress/issues/9)

**Merged pull requests:**

- German Translation  [\#5](https://github.com/watchfulli/XCloner-Wordpress/pull/5) ([QuadStingray](https://github.com/QuadStingray))

## [4.0.1](https://github.com/watchfulli/XCloner-Wordpress/tree/4.0.1) (2017-03-20)

[Full Changelog](https://github.com/watchfulli/XCloner-Wordpress/compare/4.0.2...4.0.1)

## [4.0.2](https://github.com/watchfulli/XCloner-Wordpress/tree/4.0.2) (2017-03-20)

[Full Changelog](https://github.com/watchfulli/XCloner-Wordpress/compare/89d1407d361293c1f1a0bf9ac020990a082ac8f0...4.0.2)

**Closed issues:**

- restore script is broken [\#3](https://github.com/watchfulli/XCloner-Wordpress/issues/3)
- Header does not match it's checksum for [\#2](https://github.com/watchfulli/XCloner-Wordpress/issues/2)
- File scanning estimated size seems to be wrong and much lower than the actual backup size [\#1](https://github.com/watchfulli/XCloner-Wordpress/issues/1)



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
