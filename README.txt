=== XCloner - Backup and Restore===
Contributors: xcloner
Donate link: http://www.xcloner.com/
Tags: backup plugin, restore plugin, database backup, full site backup, xcloner, website cloner, wordpress backup, database restore, blog transfer
Requires at least: 3.0.1
Tested up to: 4.7
Stable tag: 4.0.1

Backup your site, restore to any web location, send your backups to Dropbox, Amazon S3, Azure, FTP, SFTP and many others with XCloner backup plugin.

== Description ==

XCloner is a Backup and Restore plugin that is perfectly integrated with Wordpress.

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
   * Restore your backups on any other location, XCloner will attempt to extract the backup archive files for you, as well as import the mysql dump and update the Wordpress config details
   * Upload your backups to Remote Storage locations supporting FTP, SFTP, Dropbox, AWS, Azure Blog and many more to come
   * Watch every step of XCloner through it's built in debugger
   * Althrough we have optimized XCloner to run properly on most hosts, we give Developers options to customize it's running speed and avoid backup timeouts, all from the XCloner Config-> System Options
   * Ability to split backups into multiple smaller parts if a certain size limit is reached

== Installation ==

1. Upload the plugin directory to wp-content/plugins directory
2. Activate the plugin
3. Access the plugin Dashboard from the Admin Sidebar -> Site Backup Menu

UPGRADE:

You can do it easily from the Wordpress backend.

== Frequently Asked Questions ==

= Where does XCloner keep it's database backups? =

XCloner stores them in separate mysql dump files, inside a folder called xcloner-XXXXX inside the backup archive root path, where XXXXX is a hash number that is identical with the last 5 characters of the backup name,
so if the backup name is backup_localhost-2017-02-16_15-36-sql-1c6c6.tgz , the mysql backup file will be stored in xcloner-1c6c6/ folder.

= How do I restore my backup? =

XCloner provide an easy to use restore script available in the Site Backup -> Restore Backups menu, the process is being described there as well.

If the XCloner Restore option fails, you can manually restore your backup as follows:

1. extract the backup archive files to your new location
2. locate the xcloner-XXXXX folder inside your backup root folder, and look for the mysql backup in database-sql and import it through phpmyadmin
3. update your wp-config.php file to reflect the new mysql details

= How do I know which files were include in the backup? =

The XCloner Manager Backups Panel provides an easy utility to view each backup content files list. It also stores a copy of the archived backup files inside the xcloner-XXXXX/backup_files.csv file in an easy to read CSV format.

= Do you have a log for the created backup? =

Yes, if XCloner Logger option is enabled, it will store a log file inside the xcloner-XXXXX folder inside the backup archive, file is named xcloner-xxxxx.log

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
