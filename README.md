# XCloner Wordpress Plugin - Backup and Restore

[![Author](http://img.shields.io/badge/author-@thinkovi-blue.svg?style=flat-square)](https://twitter.com/thinkovi)
[![Software License](https://img.shields.io/badge/license-GPL-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://scrutinizer-ci.com/g/ovidiul/XCloner-Wordpress/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ovidiul/XCloner-Wordpress/build-status/master)

Backup your Wordpress site, restore to any web location, send your backups to Dropbox, Amazon S3, Azure, FTP, SFTP and many others with XCloner backup plugin.

XCloner is a Backup and Restore plugin that is perfectly integrated with Wordpress.

XCloner design was specifically created to Generate custom backups of any LAMP website through custom admin inputs, and to be able to Restore the clone on any other location with the help of the automatic Restore script we provide!

XCloner Backup tool uses Open Source standards like TAR, Mysql and CSV formats so you can rest assured your backups can be restored in a variety of ways, giving you more flexibility and full control.

## Features

   * Backup and Restore your Wordpress site easily
   * Create compressed and uncompressed backups using TAR open source format
   * Create automated backups from your Scheduled Backups Section
   * Received email notifications of created backups
   * Generate automatic backups based on cronjobs, it can run daily, weekly, monthly or even hourly
   * Restore your backups on any other location, XCloner will attempt to extract the backup archive files for you, as well as import the mysql dump and update the Wordpress config details
   * Upload your backups to Remote Storage locations supporting FTP, SFTP, Dropbox, AWS, Azure Blob and many more to come
   * Watch every step of XCloner through it's built in debugger
   * Althrough we have optimized XCloner to run properly on most hosts, we give Developers options to customize it's running speed and avoid backup timeouts, all from the XCloner Config-> System Options
   * Ability to split backups into multiple smaller parts if a certain size limit is reached

## Installation 

1. Upload the plugin directory to wp-content/plugins directory
2. Activate the plugin
3. Access the plugin Dashboard from the Admin Sidebar -> Site Backup Menu

## UPGRADE

You can do it easily from the Wordpress backend.

## Frequently Asked Questions

<b>Where does XCloner keep it's database backups?</b>

XCloner stores them in separate mysql dump files, inside a folder called xcloner-XXXXX inside the backup archive root path, where XXXXX is a hash number that is identical with the last 5 characters of the backup name,
so if the backup name is backup_localhost-2017-02-16_15-36-sql-1c6c6.tgz , the mysql backup file will be stored in xcloner-1c6c6/ folder.

<b>How do I restore my backup?</b> 

XCloner provide an easy to use restore script available in the Site Backup -> Restore Backups menu, the process is being described there as well.

If the XCloner Restore option fails, you can manually restore your backup as follows:

1. extract the backup archive files to your new location
2. locate the xcloner-XXXXX folder inside your backup root folder, and look for the mysql backup in database-sql and import it through phpmyadmin
3. update your wp-config.php file to reflect the new mysql details

<b>How do I know which files were include in the backup?</b>

The XCloner Manager Backups Panel provides an easy utility to view each backup content files list. It also stores a copy of the archived backup files inside the xcloner-XXXXX/backup_files.csv file in an easy to read CSV format.

<b>Do you have a log for the created backup?</b>

Yes, if XCloner Logger option is enabled, it will store a log file inside the xcloner-XXXXX folder inside the backup archive, file is named xcloner-xxxxx.log
 
