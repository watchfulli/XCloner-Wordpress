<?php

/** @var \Watchfulli\XClonerCore\Xcloner_Settings $xcloner_settings */
$xcloner_settings = $this->get_xcloner_container()->get_xcloner_settings();
/** @var \Watchfulli\XClonerCore\Xcloner_Logger $logger */
$logger = $this->get_xcloner_container()->get_xcloner_logger();
/** @var \Watchfulli\XClonerCore\Xcloner_Filesystem $xcloner_file_system */
$xcloner_file_system = $this->get_xcloner_container()->get_xcloner_filesystem();
/** @var \Watchfulli\XClonerCore\Xcloner_File_Transfer $xcloner_file_transfer */
$xcloner_file_transfer = $this->get_xcloner_container()->get_xcloner_file_transfer();

$start = 0;

$backup_list = $xcloner_file_system->get_latest_backups();
?>

<script>
  let xcloner_auth_key = '<?php echo md5(AUTH_KEY)?>';
</script>

<div class="row xcloner-restore">

    <div class="col s12">
        <?php include_once(__DIR__ . "/xcloner_header.php") ?>
    </div>

    <div class="col s12">
        <ul class="collapsible xcloner-restore collapsible-accordion" data-collapsible="accordion">
            <li data-step="3" class="restore-remote-backup-step steps active show">
                <div class="collapsible-header">
                    <i class="material-icons">folder_open</i>
                    <?php echo __("Restore Files Backup Available On Target Location", 'xcloner-backup-and-restore') ?>
                    <i class="material-icons right" title="Refresh Target Backup Files List" id="refresh_remote_backup_file">cached</i>

                    <div class="switch right">
                        <label>
                            <?php echo __('Verbose Output', 'xcloner-backup-and-restore') ?>
                            <input type="checkbox" id="toggle_file_restore_display" name="toggle_file_restore_display" class="" checked value="1">
                            <span class="lever"></span>
                        </label>
                    </div>
                </div>

                <div class="collapsible-body row">
                    <div class=" col s12 l7">
                        <div class="input-field row">
                            <div class="col s12">
                                <a href="#"
                                   class="list-backup-content btn-floating tooltipped btn-small right"
                                   data-tooltip="<?php echo __('Click To List The Selected Backup Content', 'xcloner-backup-and-restore') ?>">
                                    <i class="material-icons">folder_open</i>
                                </a>
                                <h5><?php echo __("Restore Backup Archive:", 'xcloner-backup-and-restore') ?></h5>
                                <select id="remote_backup_file" name="remote_backup_file" class="browser-default">
                                    <option value="" disabled selected>
                                        <?php echo __("Please select the target backup file to restore", 'xcloner-backup-and-restore') ?>
                                    </option>
                                    <?php foreach ($backup_list as $backup) : ?>
                                        <option value="<?php echo esc_attr($backup['file_name']) ?>">
                                            <?php echo esc_html($backup['file_name'] . ' (' . $backup['size'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label></label>
                            </div>

                            <div class="col s12">

                                <label for="filter_files_all" class="tooltipped" data-position="right"
                                       data-tooltip="<?php echo __("Restore all backup files", 'xcloner-backup-and-restore') ?>">
                                    <input class="with-gap" name="filter_files" type="radio" id="filter_files_all" checked value=""/>
                                    <span>
                                        <?php echo __("Restore All Files", "xcloner-backup-and-restore") ?>
                                    </span>
                                </label>


                                <label for="filter_files_wp_content" class="tooltipped"
                                       data-tooltip="<?php echo __('Restore the files only of the wp-content/ folder', 'xcloner-backup-and-restore') ?>">
                                    <input class="with-gap" name="filter_files" type="radio"
                                           id="filter_files_wp_content" value="/^wp-content\/(.*)/"/>
                                    <span>
                                        <?php echo __("Only wp-content", "xcloner-backup-and-restore") ?>
                                    </span>
                                </label>


                                <label for="filter_files_plugins" class="tooltipped"
                                       data-tooltip="<?php echo __('Restore the files only of the wp-content/plugins/ folder', 'xcloner-backup-and-restore') ?>">
                                    <input class="with-gap" name="filter_files" type="radio" id="filter_files_plugins"
                                           value="/^wp-content\/plugins(.*)/"/>
                                    <span>
                                        <?php echo __("Only Plugins", "xcloner-backup-and-restore") ?>
                                    </span>
                                </label>


                                <label for="filter_files_uploads" class="tooltipped"
                                       data-tooltip="<?php echo __('Restore the files only of the wp-content/uploads/ folder only', 'xcloner-backup-and-restore') ?>">
                                    <input class="with-gap" name="filter_files" type="radio" id="filter_files_uploads"
                                           value="/^wp-content\/uploads(.*)/"/>
                                    <span>
                                        <?php echo __("Only Uploads", "xcloner-backup-and-restore") ?>
                                    </span>
                                </label>


                                <label for="filter_files_themes" class="tooltipped"
                                       data-tooltip="<?php echo __('Restore the files only of the wp-content/themes/ folder', 'xcloner-backup-and-restore') ?>">
                                    <input class="with-gap" name="filter_files" type="radio" id="filter_files_themes"
                                           value="/^wp-content\/themes(.*)/"/>
                                    <span>
                                        <?php echo __("Only Themes", "xcloner-backup-and-restore") ?>
                                    </span>
                                </label>


                                <label for="filter_files_database" class="tooltipped"
                                       data-tooltip="<?php echo __('Restore the database-sql.sql mysql backup from the xcloner-xxxxx/ folder', 'xcloner-backup-and-restore') ?>">
                                    <input class="with-gap" name="filter_files" type="radio" id="filter_files_database"
                                           value="/^xcloner-(.*)\/(.*)\.sql/"/>
                                    <span>
                                        <?php echo __("Only Database Backup", "xcloner-backup-and-restore") ?>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="progress">
                            <div class="indeterminate" style="width: 0"></div>
                        </div>

                        <div class="status"></div>
                        <ul class="files-list"></ul>
                    </div>

                    <div class="col s12 l5 right-align">
                        <div class="toggler">
                            <button class="btn waves-effect waves-light restore_remote_backup normal " type="submit"
                                    id="" name="action"><?php echo __("Restore", 'xcloner-backup-and-restore') ?>
                                <i class="material-icons left">download</i>
                            </button>
                            <button class="btn waves-effect waves-light red restore_remote_backup cancel" type="submit"
                                    id="" name="action"><?php echo __("Cancel", 'xcloner-backup-and-restore') ?>
                                <i class="material-icons right">close</i>
                            </button>
                        </div>
                        <button class="btn waves-effect waves-light grey" type="submit"
                                title="<?php echo __("Skip Next", 'xcloner-backup-and-restore') ?>"
                                id="skip_remote_backup_step"
                                name="action"><?php echo __("Skip Next", 'xcloner-backup-and-restore') ?>
                            <i class="material-icons right">navigate_next</i>
                        </button>
                    </div>
                </div>
            </li>

            <li data-step="4" class="restore-remote-database-step steps">
                <div class="collapsible-header">
                    <i class="material-icons">list</i><?php echo __("Restore Database", 'xcloner-backup-and-restore') ?>
                    <i class="material-icons right" title="Refresh Database Backup Files List" id="refresh_database_file">cached</i>
                </div>
                <div class="collapsible-body row">

                    <div class="col s12 l7">
                        <div class="input-field row">
                            <div class="col s12">
                                <select id="remote_database_file" name="remote_database_file" class="browser-default">
                                    <option value="" disabled selected>
                                        <?php echo __("Please select the target database backup file to restore", 'xcloner-backup-and-restore') ?>
                                    </option>
                                </select>

                                <label></label>
                            </div>
                        </div>

                        <div class="progress">
                            <div class="determinate" style="width: 0%"></div>
                        </div>

                        <div class="status"></div>
                        <div class="query-box">
                            <h6><?php echo __('Use the field below to fix your mysql query and Retry again the Restore, or replace with # to Skip next', 'xcloner-backup-and-restore') ?>
                            </h6>
                            <textarea class="query-list" cols="5"></textarea>
                        </div>
                    </div>

                    <div class="col s12 l5 right-align">
                        <div class="toggler">
                            <button class="btn waves-effect waves-light restore_remote_mysqldump normal " type="submit"
                                    id="" name="action"><?php echo __("Restore", 'xcloner-backup-and-restore') ?>
                                <i class="material-icons left">download</i>
                            </button>
                            <button class="btn waves-effect waves-light red restore_remote_mysqldump cancel"
                                    type="submit" id=""
                                    name="action"><?php echo __("Cancel", 'xcloner-backup-and-restore') ?>
                                <i class="material-icons right">close</i>
                            </button>
                        </div>

                        <button class="btn waves-effect waves-light grey" type="submit"
                                title="<?php echo __("Skip Next", 'xcloner-backup-and-restore') ?>"
                                id="skip_restore_remote_database_step"
                                name="action"><?php echo __("Skip Next", 'xcloner-backup-and-restore') ?>
                            <i class="material-icons right">navigate_next</i>
                        </button>

                    </div>

                </div>
            </li>

            <li data-step="5" class="restore-finish-step steps ">
                <div class="collapsible-header"><i
                            class="material-icons">folder_open</i><?php echo __("Finishing up...", 'xcloner-backup-and-restore') ?>
                </div>
                <div class="collapsible-body row" style="padding-left:40px;">

                    <div class="row">
                        <div class="col s4">
                            <span><?php echo __("Update wp-config.php mysql details and update the Target Site Url", 'xcloner-backup-and-restore') ?></span>
                        </div>

                        <div class="col s8">
                            <div class="switch">
                                <label>
                                    Off
                                    <input type="checkbox" id="update_remote_site_url" name="update_remote_site_url"
                                           checked value="1">
                                    <span class="lever"></span>
                                    On
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s4">
                            <span><?php echo __("Delete Restored Backup Temporary Folder", 'xcloner-backup-and-restore') ?></span>
                        </div>
                        <div class="col s8">
                            <div class="switch">
                                <label>
                                    Off
                                    <input type="checkbox" id="delete_backup_temporary_folder"
                                           name="delete_backup_temporary_folder" checked value="1">
                                    <span class="lever"></span>
                                    On
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s4">
                            <span><?php echo __("Delete Backup Archive", 'xcloner-backup-and-restore') ?></span>
                        </div>
                        <div class="col s8">
                            <div class="switch">
                                <label>
                                    Off
                                    <input type="checkbox" id="delete_backup_archive" name="delete_backup_archive"
                                           value="1">
                                    <span class="lever"></span>
                                    On
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s4">
                            <span><?php echo __("Delete Restore Script", 'xcloner-backup-and-restore') ?></span>
                        </div>
                        <div class="col s8">
                            <div class="switch">
                                <label>
                                    Off
                                    <input type="checkbox" id="delete_restore_script" name="delete_restore_script"
                                           checked value="1">
                                    <span class="lever"></span>
                                    On
                                </label>
                            </div>
                        </div>
                    </div>


                    <div class=" row col s12">
                        <div class="status"></div>
                    </div>



                    <div class="col s12 center-align">
                        <button class="btn waves-effect waves-light teal" type="submit" id="restore_finish"
                                name="action"><?php echo __("Finish", 'xcloner-backup-and-restore') ?>
                            <i class="material-icons right">navigate_next</i>
                        </button>
                    </div>

                    <div class="col s12 center-align" id="xcloner_restore_finish">
                        <h5><?php echo __("Thank you for using XCloner.", 'xcloner-backup-and-restore') ?></h5>
                        <div class="row">
                            <div class="col s6 right-align">
                                <a href="https://wordpress.org/support/plugin/xcloner-backup-and-restore/reviews/#new-post" class="btn waves-effect waves-light teal" type="button" target="_blank">
                                    <?php echo __("Leave a review", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">navigate_next</i>
                                </a>
                            </div>
                            <div class="col s6 left-align">
                                <a href="https://wordpress.org/support/plugin/xcloner-backup-and-restore/%60" class="btn waves-effect waves-light teal" type="button" target="_blank">
                                    <?php echo __("Get support", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">navigate_next</i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

        </ul>
    </div>
</div>


<!-- List Backup Content Modal-->
<div id="backup_cotent_modal" class="modal">
    <div class="modal-content">
        <h4><?php echo sprintf(__("Listing Backup Content ", 'xcloner-backup-and-restore'), "") ?></h4>
        <h5 class="backup-name"></h5>

        <div class="progress">
            <div class="indeterminate"></div>
        </div>
        <ul class="files-list"></ul>
    </div>
</div>
