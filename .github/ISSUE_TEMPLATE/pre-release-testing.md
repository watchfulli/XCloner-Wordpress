---
name: Pre-release testing
about: Use this checklist template when all issues for a milestone are complete.
title: ''
labels: testing
assignees: jimiero

---

- [ ] Basic Settings can be saved
- [ ] Remote Storages settings can be saved and can be validated against valid credentials (spot-test with existing cloud credentials from LastPass)
- [ ] Generate Backup - full backup with default settings
- [ ] Generate Backup - partial backup with some tables excluded
- [ ] Generate Backup - partial backup with some files excludes
- [ ] Generate Backup - partial backup with additional databases included
- [ ] Generate Backup - full backup with encryption enabled
- [ ] Generate Backup - full backup transferred to Dropbox
- [ ] Generate Backup - full backup transferred to Dropbox and deleted from local
- [ ] Manage Local Backups - Make sure backups are listed.
- [ ] Manage Local Backups - Make sure backups can be deleted
- [ ] Manage Local Backups - Make sure backups can be uploaded to DropBox
- [ ] Manage Local Backups - Make sure backups can be downloaded. 
- [ ] Manage Local Backups - Make sure backup contents can be listed.
- [ ] Manage Local Backups - Make sure an encrypted backup can be unencrypted.
- [ ] Manage Dropbox Backups - Make sure backups are listed.
- [ ] Manage Dropbox Backups - Make sure backups can be deleted
- [ ] Manage Dropbox Backups - Make sure backups can be uploaded to DropBox
- [ ] Manage Dropbox Backups - Make sure backups can be downloaded. 
- [ ] Manage Dropbox Backups - Make sure backup contents can be listed.
- [ ] Manage Dropbox Backups - Make sure an encrypted backup can be unencrypted.
- [ ] Create a Scheduled Profile
- [ ] Check the correct recurrence of a scheduled Profile based on the Wordpress cronjob execution
- [ ] Trigger any backup profile from the XCloner CLI 
- [ ] Restore full backup locally
- [ ] Restore full backup to a remote location
- [ ] Enable/Disable logs - check if enabling/disabling logs in XCloner works
