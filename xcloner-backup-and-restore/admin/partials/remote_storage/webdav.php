<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="collapsible-header">
                        <i class="material-icons">computer</i><?php echo __("WebDAV Storage", 'xcloner-backup-and-restore') ?>
                        <div class="switch right">
                            <label>
                                Off
                                <input type="checkbox" name="xcloner_webdav_enable" class="status"
                                       value="1" <?php if (get_option("xcloner_webdav_enable")) {
                                        echo "checked";
                                    } ?> \>
                                <span class="lever"></span>
                                On
                            </label>
                        </div>
                    </div>
                    <div class="collapsible-body">

                        <div class="row">
                            <div class="col s12 m3 label">
                                &nbsp;
                            </div>
                            <div class=" col s12 m6">
                                <p>
									<?php //echo sprintf(__('Visit %s and get your Account Id and  Application Key.','xcloner-backup-and-restore'), '<a href="https://secure.backblaze.com/b2_buckets.htm" target="_blank">https://secure.backblaze.com/b2_buckets.htm</a>')?>
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="webdav_url"><?php echo __("WebDAV Base Url", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("WebDAV Service Url like https://webdav.yandex.com", 'xcloner-backup-and-restore') ?>"
                                       id="webdav_url" type="text" name="xcloner_webdav_url" class="validate"
                                       value="<?php echo esc_attr(get_option("xcloner_webdav_url")) ?>" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="webdav_username"><?php echo __("WebDAV Username", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("WebDAV Username", 'xcloner-backup-and-restore') ?>"
                                       id="webdav_username" type="text" name="xcloner_webdav_username" class="validate"
                                       value="<?php echo esc_attr(get_option("xcloner_webdav_username")) ?>" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="webdav_password"><?php echo __("WebDAV Password", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("WebDAV Password", 'xcloner-backup-and-restore') ?>"
                                       id="webdav_password" type="text" name="xcloner_webdav_password"
                                       class="validate"
                                       value="<?php echo esc_attr(str_repeat('*', strlen(get_option("xcloner_webdav_password")))) ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="webdav_target_folder"><?php echo __("WebDAV Target Path", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("WebDAV Target Path", 'xcloner-backup-and-restore') ?>"
                                       id="webdav_target_folder" type="text" name="xcloner_webdav_target_folder"
                                       class="validate"
                                       value="<?php echo esc_attr(get_option("xcloner_webdav_target_folder")) ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <?php echo common_cleanup_html('webdav')?>

                        <div class="row">
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                                        value="webdav"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">save</i>
                                </button>
                            </div>
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light orange" type="submit" name="action"
                                        id="action" value="webdav"
                                        onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">import_export</i>
                                </button>
                            </div>
                        </div>

                    </div>
