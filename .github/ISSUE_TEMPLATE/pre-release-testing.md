---
name: Pre-release testing
about: Use this checklist template when all issues for a milestone are complete.
title: ''
labels: testing
assignees: jimiero

---

# Settings
- [ ] Basic Settings can be saved
- [ ] Remote Storages settings show `VALID` message when saved.
  - [ ] WebDav
  - [ ] SFTP
  - [ ] Dropbox
  - [ ] Azure Blob
  - [ ] Amazon S3
  - [ ] Backblaze
  - [ ] BOX

# Generate Local Backup
- [ ] Full backup with default settings completes successfully.
- [ ] Partial backup with some tables excluded completes successfully.
- [ ] Partial backup with some files excludes completes successfully.
- [ ] Partial backup with additional databases included completes successfully.
- [ ] Backup options
  - [ ] Backup process completes successfully with encryption enabled
  - [ ] Backup process completes successfully when `transfer to Dropbox` enabled.
    - [ ] Check that backup archive successfuly uploaded in Dropbox account.
  - [ ] Backup process completes successfully when `transferr to Dropbox` enabled and `delete from local storage` selected.
    - [ ] Check that backup archive successfuly deleted from Dropbox account.

# Send backups to remote storage

In these tests, use a very a simple backup that will complete quickly and have a very small size. For example, do not include any databases and exclude all files except `index.php`. 

  - [ ] Partial backup completes successfully and send file to SFTP location.
  - [ ] Partial backup completes successfully and send file to Dropbox location (likely tested above).
  - [ ] Partial backup completes successfully and send file to Azure Blob location.
  - [ ] Partial backup completes successfully and send file to Amazon S3 location.
  - [ ] Partial backup completes successfully and send file to Backblaze location.
  - [ ] Partial backup completes successfully and send file to BOX location.

# Manage Local Backups
- [ ] Make sure backups are listed.
- [ ] Make sure backups can be deleted when the icon is clicked. 
- [ ] Make sure backups can be uploaded to DropBox when the icon is clicked. 
- [ ] Make sure backups can be downloaded when the icon is clicked.
- [ ] Make sure backup contents can be listed when the icon is clicked.
- [ ] Make sure an encrypted backup can be unencrypted when the icon is clicked.

# Manage Remote Backups
- [ ] Dropbox backups are listed.
- [ ] Dropbox backups can be deleted when the icon is clicked.
- [ ] Dropbox backups can be downloaded when the icon is clicked.
- [ ] Dropbox backup contents can be listed when the icon is clicked.
- [ ] Encrypted Dropbox backups can be unencrypted when the icon is clicked.

# Scheduling Backups
- [ ] Create a Scheduled Profile
- [ ] Check the correct recurrence of a scheduled Profile based on the Wordpress cronjob execution

# Restore Backups
- [ ] Restore all files from a local backup. 
- [ ] Restore database from a local backup.
- [ ] Migrate files from a backup to a different domain.
- [ ] Migrate the database from a backup to a different domain.

# Watchful Backups
- [ ] Site connects/validates in Watchful (test `Refresh data` success).
- [ ] XCloner backup works as expected in Watchful when using the `Start Backup` button.

# Other tests
- [ ] Trigger any backup profile from the XCloner CLI.
- [ ] Trigger any backup profile from the WP CLI.
- [ ] Check if enabling/disabling logs in XCloner settings works as expected.
- [ ] Check if XCloner version has been incremented from previous release.
- [ ] Check if release has changelog notes.
