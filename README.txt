=== XCloner - Backup and Restore===
Contributors: xcloner
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AAPE8PLAE554S
Tags: backup plugin, restore plugin, database backup, duplicate, full site backup, website cloner, wordpress backup, database restore, webdav, azure, ftp, sftp, amazon s3, dropbox, google drive, differential backup
Requires at least: 3.0.1
Tested up to: 4.8
Stable tag: 4.0.5

Backup your site, restore to any web location, send your backups to Dropbox, Amazon S3, Azure, FTP, SFTP, WebDAV, Google Drive with XCloner plugin.

== Description ==

XCloner is a Backup and Restore plugin that is perfectly integrated with Wordpress. It is able to create complete and differentials backups of your site, manually or automatically through the built-in scheduler.

[youtube http://www.youtube.com/watch?v=V9iWpPyG1EE]

XCloner design was specifically created to Generate custom backups of any Wordpress website through custom admin inputs, and to be able to Restore the backup on any other location with the help of the automatic Restore script we provide!

XCloner Backup tool uses Open Source standards like TAR, Mysql and CSV formats so you can rest assured your backups can be restored in a variety of ways, giving you more flexibility and full control.

Project is actively maintained through github https://github.com/ovidiul/XCloner-Wordpress/ , all issues can be reported here https://github.com/ovidiul/XCloner-Wordpress/issues .

<strong>Requirements:</strong>

PHP 5.4+ with mod CURL installed

<strong>Features:</strong>

   * Backup and Restore your Wordpress site easily
   * Create compressed and uncompressed backups using TAR open source format
   * Create automated backups from your Scheduled Backups Section
   * Received email notifications of created backups
   * Generate automatic backups based on cronjobs, it can run daily, weekly, monthly or even hourly
   * Restore your backups locally or to a remote location, XCloner will attempt to extract the backup archive files for you, as well as import the mysql dump and update the Wordpress config details
   * Upload your backups to Remote Storage locations supporting FTP, SFTP, Dropbox, AWS, Azure Blob, Backblaze, WebDAV, Google Drive and many more to come
   * Watch every step of XCloner through it's built in debugger
   * Althrough we have optimized XCloner to run properly on most hosts, we give Developers options to customize it's running speed and avoid backup timeouts, all from the XCloner Config-> System Options
   * Ability to split backups into multiple smaller parts if a certain size limit is reached
   * Generate Differential Backups so your backup will include only files modified after a certain date, giving you the option to decrease the total backup space disk usage
   * Generate automatic backups before a Wordpress automatic update

== Installation ==

1. Upload the plugin directory to wp-content/plugins directory
2. Activate the plugin
3. Access the plugin Dashboard from the Admin Sidebar -> Site Backup Menu

UPGRADE:

You can do it easily from the Wordpress backend.

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
