=== Backup, Restore and Migrate WordPress Sites With the XCloner Plugin ===
Contributors: watchful,ovidiul
Donate link: http://www.xcloner.com
Tags: backup, database backup, cloud backup, WordPress backup, WordPress migration
Requires at least: 3.0.1
Requires PHP: 7.1
Tested up to: 5.5
Stable tag: 4.2.161

XCloner is a backup plugin that allows you to safely back up and restore your WordPress sites. You can send site backups to SFTP, Dropbox, Amazon, Google Drive, Backblaze and other locations. 

== Description ==

[XCloner](https://www.xcloner.com) is a backup plugin that allows you to safely back up and restore your WordPress sites. You can send your site backups to SFTP, Dropbox, Amazon, Google Drive, Backblaze and other locations. You can create backups manually or automatically with XCloner’s built-in scheduler.

XCloner enables you to automatically generate backups with the built-in cron script. These cron jobs can run daily, weekly, monthly or even hourly. 

XCloner allows you to generate custom backups of any WordPress site, and then restore the backup on any other location with the help of the automatic restore script we provide!

XCloner has many useful safety features. For example, XCloner will generate a core, plugins, themes or languages files backup before the automatic update of WordPress core, plugins, themes or languages files.
= Remote Storage for WordPress Backups =

XCloner allows you to send your backups to remote storage locations supporting FTP, SFTP, DropBox, Amazon S3, Google Drive, WebDAV, Backblaze, Azure and many more to come

You can generate “Differential Backups” so your backup will include only files modified after a certain date. This can decrease the space needed to store your backups.

XCloner also has safety features to make sure your backups are successful. One example: you have the ability to split backups into multiple smaller parts if a certain size limit is reached. Another example: XCloner can also store a local copy of the backup that it will then delete when the backup has been sent to the remote location.

= Secure, GDPR Compliant WordPress Backups =
XCloner is the best backup choice for people who care about security and privacy.

XCloner uses open source standards like TAR, MySQL and CSV formats so you can be sure that your backups can be restored in a variety of ways, giving you more flexibility and full control.

XCloner has a built-in security layer to protect your backups. You can create encrypted backup archives with AES-128-CBC algorithm. This encryption helps to ensure that your data is still GDPR compliant even if the backup fails.

= Restore Backups Anywhere =

You can restore backups on any location compatible with your website by using the XCloner restore feature. Your site clone can be restored on a totally different server, with new server and MySQL details.

XCloner will attempt to extract the backup archive files for you, as well as import the MySQL dump and update your WordPress configuration details.

The restore script is located inside the XCloner archive, in the /restore/ directory. XCloner can restore your original file and directory permissions. XCloner can also automatically update the new host settings to the configuration file.

XCloner has a variety of restoration options including: All Files, Only Plugins Files, Only Theme Files, Only Uploads Files, and Only Database Backup.

= XCloner works best with the Watchful dashboard =
[Watchful](https://watchful.net) is a web developers toolbox for remotely managing and monitoring multiple WordPress websites. Simply add all your production and staging sites into the Watchful Dashboard and use our tools to monitor uptime and site backups, plus updates to WordPress and core and plugins, and more. XCloner integrates smoothly with Watchful. You’ll be amazed at how much time and money you save managing your WordPress sites with [Watchful](https://wordpress.org/plugins/watchful/).

== Installation ==

1. In the WordPress backend, select Plugins > Add New.
2. In the search bar enter `xcloner`.
3. When the XCLoner listing is shown, click the 'Install` button. 
4. Following installation, click the `Activate` button.

UPGRADE:

XCloner can be updated from the plugins list in the WordPress backend.

== Frequently Asked Questions ==

= Where does XCloner keep it's Database Backups? =

XCloner stores them in separate mysql dump files, inside a folder called xcloner-XXXXX inside the backup archive root path, where XXXXX is a hash number that is identical with the last 5 characters of the backup name,
so if the backup name is backup_localhost-2017-02-16_15-36-sql-1c6c6.tgz , the mysql backup file will be stored in xcloner-1c6c6/ folder.

= How do I Restore my Backup? =

XCloner provide an easy to use restore script available in the Site Backup -> Restore Backups menu, the process is being described there as well.

If the XCloner Restore option fails, you can manually restore your backup as follows:

1. extract the backup archive files to your new location
2. locate the xcloner-XXXXX folder inside your backup root folder, and look for the mysql backup in database-sql and import it through phpmyadmin
3. update your wp-config.php file to reflect the new mysql details

= How do I know which Files were include in the Backup? =

The XCloner Manager Backups Panel provides an easy utility to view each backup content files list. It also stores a copy of the archived backup files inside the xcloner-XXXXX/backup_files.csv file in an easy to read CSV format.

= Do you have a Log for the created Backup? =

Yes, if XCloner Logger option is enabled, it will store a log file inside the xcloner-XXXXX folder inside the backup archive, file is named xcloner-xxxxx.log

= What are Differentials Backups? =

Differential Backups contain files modified after a certian period of time. So each time backup runs, modified files after that period of time are added to a new Backup archive.
Compared to Incremental Backups, which contain only modified files from the previous run, they use more space but are more reliable for files restore. 
They will use considerably less space than a full backup however.

= Why Differential Backups and will you support Incremental Backups? =

The main difference comes from how reliable a backup set it. For instance, if something happens to one backup archive from the Incremental Backup set, then it is possible you will lose 
the files changes in that period of time, however if the same case happens to a Differential Backup, then the files can easily be recovered from any of the other Differential Backups. The 
storage difference between Incremental Backups and Differential Backups is not significant and considering the reliability of the Differential Set so we have decided, for now, to not implement 
further Incremental Backups.

= What would a good Backup Procedure be with Differential Backups? =

As a general rule, I would recommend setting a weekly full site backup schedule and then a daily schedule for a differential backup. You can also include a daily backup of the database in the same Differential Backup. 
Of course, schedules can be adjusted accordingly to how often you update your site, the size of it and the storage space available.

== Screenshots ==

1. XCloner Dashboard
2. General Backup Settings
3. List Backup Content
4. Cleanup Options for Local Storage
5. Remote Storage Panel supporting ftp, sftp, dropbox, amazon s3, azure blob and many more to come
6. Manage Scheduled Backups Panel
7. Edit Scheduled Backup
8. Generate Backup ->Files Options tab
9. Restore Backup Panel
10. Generate Backup Process
11. Generate Backup Screen

== Changelog ==

= 4.2.16 =
* Backblaze remote storage UI text changes
* Improved Google Drive integration
* http(s) url email backup fix
* design changes 

= 4.2.15 =

* frontend popup display conflict fix
* design changes
* remote storage direct download option addon
* replace modal from generate backup screen with a new follow up tab
* S3 uplaod fix
* WP-CLI --quiet flag addon, --encrypt/--decrypt addon
* CSRF nonces implementation API

= 4.2.14 = 

* CSRF restore vulnerability fix

= 4.2.13 =

* conflict fix bootstrap modal display
* WP 5.5 compatibility check and update 

= 4.2.12 =

* Google Drive authorization fix

= 4.2.11 = 

* OneDrive file upload size limit fix

= 4.2.10 =

* Add `Local Storage` to the `Remote Storage` area
* Cleanup uploaded archive after restore 
* Add WP-CLI support
* Add Microsoft OneDrive support to remote storage 
* BUG: Load webdav vendor lib only if webdav is activated
* Add support for Backblaze application keys
* BUG: Google Drive upload issue

= 4.2.9 =

* scheduler fix call init
* google drive support fix
* PHP 7.1 minimum requirement
* PHP 7.x bug fix
* PHP 7.x minimum support activation

= 4.2.1 =

** Implemented enhancements: **
* Rename `Scheduled Backups` menu to `Schedules & Profiles`

** Fixed bugs: **
* Fixed `redeclare deactivate\_plugin` bug in CLI

= 4.2.0 =

** Implemented enhancements: **

* Improve error when connecting to remote site using restore script from another site  
* Improve error message when accessing restore script directly 
* Encrypt database password during restore 
* Update default regex exclusions 
* Replace youtube links 
* Add additional cleanup & quota options for backup storage 
* Add standalone CLI for making backups 
* Move `send to remote destination` option to the `Backup Options` tab 

** Fixed bugs: **

* Select All Backups delete does not work 
* JS Error during restoration 
* Ajax error when viewing empty storage area 
* Javascript error when running a backup, and clicking "Send Backup to remote storage"
* SFTP bug 
* Live DB restore replaces WP Options table 

= 4.1.5 =
* SFTP upoad fix

= 4.1.4 =
* thinkovi references replace
* plugin pre auto update text changelog
* author name and uri change
* standalone library addon support

= 4.1.3 =
* database include tables fix

= 4.1.2 = 
* improved default backup storage path security
* improved remote storage security

= 4.1.2 = 
* vendor lib updates
* flysystem azure storage Upgrade

= 4.1.1 = 
* log tmp directories fix, tracking only ERROR reports from php
* security improvement backup log name
* database restore resume fix
* memory limit fix

= 4.1.0 =
* added AES-128-CBC backup encryption and decryption option
* manage backup fixes
* scheduled backup screen fixes and addon backup encryption option
* automated backups encryption option addon
* generate backups encrypt option addon

= 4.0.9 =
* remote storage password encryption addon for database
* vendor cleanup packages
* database silent fail fix
* copyright changes
* jstree fix database display
* microtime float div fix
* manage backups data order fix

= 4.0.8 = 
* updated vendor library dependencies, AWS, phpseclib
* TAR compression fix
* 7.2 compatibility checks and fixes

= 4.0.7 =
* added log fixes for Wordpress cron
* remove storage fixes

= 4.0.6 =
* S3 prefix addon for defining folders
* S3 custom endpoint addon to support minio.io
* code fixes

= 4.0.5 =
* Dropbox API update to V2
* Code fixes and text changes

= 4.0.4 =
* remote storage view fix
* added automatic backups option before WP automatic update
* deactivate exception handling fix
* restore pages improvements
* old XCloner backup format compatibility fixes

= 4.0.3 =
* added differential backups with the option to only backup files modified after a certain date
* added localhost restore option with direct access to the restore restore
* added schedule name fixes
* added restore filter All Files, Only Plugins Files, Only Theme Files, Only Uploads Files, Only Database Backup
* added remote backup list archive option on restore page
* tmp directory cleanup on deactivate
* sftp text fixes

= 4.0.2 =
* added WebDAV remote storage support
* added Google Drive Suppor through XCloner-Google-Drive plugin
* added depedency injection code refactoring
* added TAR PAX support on restore
* improving code quality scrutinizer
* fixing phpversion requirement
* adding Backblaze remote storage support
* added Remote Storage Manage Backups dropdown selection
* fixed windows opendir error
* added total archived files to notifications email
* timezone scheduler fix
* added default error sending to admin when no notification email is set


= 4.0.1 =
* Code rewritten from ground up to make use of latest code standards
* Added support for Dropbox, Amazon S3, Azure Blob and SFTP storage
* Added a new restore script
* Added an improved backup and system logger 
* New Setting Panel
* New Manage Backups Panel with the options to Delete, Transfer to Remote Storage, Download and List Backup archive contents
* Added mail notifications for scheduled backups 
* Added a new Cron Scheduler to make use of Wordpress System Cron option
* Improved user input sanitization
* Improved recursive file scanning and archiving
* Improved Mysql Backup dump
* Added Multiple Cleanup options both for local storage and remote
* Added Improved Backup Compressing option

= 3.1.5 =
* Config variables save sanitization addon

= 3.1.4 =
* DropPHP DropBox library update, upload fixes for files larger than 150MB

= 3.1.3 = 
* XSS fix

= 3.1.2 =
* vulnerability fix

= 3.1.1 = 
* added CSRF protection

= 3.1.0 =
* added Wordpress login-less integration
* plugin settings are now saved to database
* security audit and hardening

= 3.0.8 =
* added russian language support

= 3.0.7 =
* added sftp support for backup transfer, thanks Todd Bluhm - dynamicts.com

= 3.0.6 =
* added php 5.4 compatibility

= 3.0.4 =
* LFI vulnerability fix

= 3.0.3 =
* added amazon ssl option box
* moved the compress option to the System tab, don't use it unless you know what you are doing!

= 3.0.1 =
* several important security and bug fixes

= 3.0 =
* incremental database backup
* incremental file system scan
* backup size limit and option to split it into additional archives, default 2GB
* exclude files larger than a certain size option
* incremental files restore
* JQuery Start interface

= 2.2.1 =
* Added JSON AJAX interface to the Generate Backup process
* Added incremental filesystem scan
* several bug fixes
* php >=5.2.0 version check

= 2.1.2 =
* Added Amazon S3 cron storage support

= 2.1 =
* Initial release

== Upgrade Notice ==

= 3.0.3 =
Please check changelog!

== Installation ==

1. In the WordPress backend, select Plugins > Add New.
2. In the search bar enter `xcloner`.
3. When the XCLoner listing is shown, click the 'Install` button. 
4. Following installation, click the `Activate` button.

UPGRADE:

XLCloner can be updated from the plugins list in the WordPress backend.

== Frequently Asked Questions ==

= Where does XCloner keep it's Database Backups? =

XCloner stores them in separate mysql dump files, inside a folder called xcloner-XXXXX inside the backup archive root path, where XXXXX is a hash number that is identical with the last 5 characters of the backup name,
so if the backup name is backup_localhost-2017-02-16_15-36-sql-1c6c6.tgz , the mysql backup file will be stored in xcloner-1c6c6/ folder.

= How do I Restore my Backup? =

XCloner provide an easy to use restore script available in the Site Backup -> Restore Backups menu, the process is being described there as well.

If the XCloner Restore option fails, you can manually restore your backup as follows:

1. extract the backup archive files to your new location
2. locate the xcloner-XXXXX folder inside your backup root folder, and look for the mysql backup in database-sql and import it through phpmyadmin
3. update your wp-config.php file to reflect the new mysql details

= How do I know which Files were include in the Backup? =

The XCloner Manager Backups Panel provides an easy utility to view each backup content files list. It also stores a copy of the archived backup files inside the xcloner-XXXXX/backup_files.csv file in an easy to read CSV format.

= Do you have a Log for the created Backup? =

Yes, if XCloner Logger option is enabled, it will store a log file inside the xcloner-XXXXX folder inside the backup archive, file is named xcloner-xxxxx.log

= What are Differentials Backups? =

Differential Backups contain files modified after a certian period of time. So each time backup runs, modified files after that period of time are added to a new Backup archive.
Compared to Incremental Backups, which contain only modified files from the previous run, they use more space but are more reliable for files restore. 
They will use considerably less space than a full backup however.

= Why Differential Backups and will you support Incremental Backups? =

The main difference comes from how reliable a backup set it. For instance, if something happens to one backup archive from the Incremental Backup set, then it is possible you will lose 
the files changes in that period of time, however if the same case happens to a Differential Backup, then the files can easily be recovered from any of the other Differential Backups. The 
storage difference between Incremental Backups and Differential Backups is not significant and considering the reliability of the Differential Set so we have decided, for now, to not implement 
further Incremental Backups.

= What would a good Backup Procedure be with Differential Backups? =

As a general rule, I would recommend setting a weekly full site backup schedule and then a daily schedule for a differential backup. You can also include a daily backup of the database in the same Differential Backup. 
Of course, schedules can be adjusted accordingly to how often you update your site, the size of it and the storage space available.

== Screenshots ==

1. XCloner Dashboard
2. General Backup Settings
3. List Backup Content
4. Cleanup Options for Local Storage
5. Remote Storage Panel supporting ftp, sftp, dropbox, amazon s3, azure blob and many more to come
6. Manage Scheduled Backups Panel
7. Edit Scheduled Backup
8. Generate Backup ->Files Options tab
9. Restore Backup Panel
10. Generate Backup Process
11. Generate Backup Screen

