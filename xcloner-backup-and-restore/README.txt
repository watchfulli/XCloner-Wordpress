=== Backup, Restore and Migrate WordPress Sites With the XCloner Plugin ===
Contributors: watchful
Donate link: http://www.xcloner.com
Tags: backup, database backup, cloud backup, WordPress backup, WordPress migration
Requires at least: 5.1
Requires PHP: 7.3
Tested up to: 6.6.1
Stable tag: 4.7.4


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

= 4.7.5 =
* Move changelog to a separate file (https://github.com/watchfulli/XCloner-Wordpress/issues/315)

[See changelog for all versions](https://raw.githubusercontent.com/watchfulli/XCloner-Wordpress/master/xcloner-backup-and-restore/CHANGELOG.txt).

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
