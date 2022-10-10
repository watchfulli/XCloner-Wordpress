<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="collapsible-header">
    <i class="material-icons">computer</i><?php echo __("FTP Storage", 'xcloner-backup-and-restore') ?>
    <div class="switch right">
        <label>
            Off
            <input
                    type="checkbox"
                    name="xcloner_ftp_enable"
                    class="status"
                    value="1"
                <?php if (get_option("xcloner_ftp_enable")) {
                    echo "checked";
                } ?>
            >
            <span class="lever"></span>
            On
        </label>
    </div>
</div>
<div class="collapsible-body">
    <div class="row">
        <div class="col s12 m3 label">
            <label for="ftp_host"><?php echo __("Ftp Hostname", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class="col s12 m6">
            <input
                    id="ftp_host"
                    placeholder="<?php echo __("Ftp Hostname", 'xcloner-backup-and-restore') ?>"
                    type="text" name="xcloner_ftp_hostname" class="validate"
                    value="<?php echo esc_attr(get_option("xcloner_ftp_hostname")) ?>">
        </div>
        <div class=" col s12 m2">
            <input placeholder="<?php echo __("Ftp Port", 'xcloner-backup-and-restore') ?>" id="ftp_port" type="text"
                   name="xcloner_ftp_port" class="validate"
                   value="<?php echo esc_attr(get_option("xcloner_ftp_port", 21)) ?>">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="ftp_username"><?php echo __("Ftp Username", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Ftp Username", 'xcloner-backup-and-restore') ?>" id="ftp_username"
                   type="text" name="xcloner_ftp_username" class="validate"
                   value="<?php echo esc_attr(get_option("xcloner_ftp_username")) ?>" autocomplete="off">
        </div>
    </div>


    <div class="row">
        <div class="col s12 m3 label">
            <label for="ftp_password"><?php echo __("Ftp Password", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Ftp Password", 'xcloner-backup-and-restore') ?>" id="ftp_password"
                   type="text" name="xcloner_ftp_password" class="validate"
                   value="<?php echo esc_attr(str_repeat('*', strlen(get_option("xcloner_ftp_password")))) ?>"
                   autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="ftp_root"><?php echo __("Ftp Storage Folder", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Ftp Storage Folder", 'xcloner-backup-and-restore') ?>" id="ftp_root"
                   type="text" name="xcloner_ftp_path" class="validate"
                   value="<?php echo esc_attr(urldecode(get_option("xcloner_ftp_path") ?: '')) ?>">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="ftp_root"><?php echo __("Ftp Transfer Mode", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6 input-field inline">
            <p>
                <label for="passive">
                    <input name="xcloner_ftp_transfer_mode" type="radio" id="passive"
                           value="1" <?php if (get_option("xcloner_ftp_transfer_mode", 1)) {
                        echo "checked";
                    } ?> />
                    <span><?php echo __("Passive", 'xcloner-backup-and-restore') ?></span>
                </label>
            </p>
            <p>
                <label for="active">
                    <input name="xcloner_ftp_transfer_mode" type="radio" id="active"
                           value="0" <?php if (!get_option("xcloner_ftp_transfer_mode", 1)) {
                        echo "checked";
                    } ?> />
                    <span><?php echo __("Active", 'xcloner-backup-and-restore') ?></span>
                </label>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="ftp_ssl_mode"><?php echo __("Ftp Secure Connection", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6 input-field inline">
            <p>
                <label for="ftp_ssl_mode_inactive">
                    <input name="xcloner_ftp_ssl_mode" type="radio" id="ftp_ssl_mode_inactive"
                           value="0" <?php if (!get_option("xcloner_ftp_ssl_mode")) {
                        echo "checked";
                    } ?> />
                    <span><?php echo __("Disable", 'xcloner-backup-and-restore') ?></span>
                </label></p>
            <p>
                <label for="ftp_ssl_mode_active">
                    <input name="xcloner_ftp_ssl_mode" type="radio" id="ftp_ssl_mode_active"
                           value="1" <?php if (get_option("xcloner_ftp_ssl_mode")) {
                        echo "checked";
                    } ?> />
                    <span><?php echo __("Enable", 'xcloner-backup-and-restore') ?></span>
                </label></p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="ftp_timeout"><?php echo __("Ftp Timeout", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m2">
            <input placeholder="<?php echo __("Ftp Timeout", 'xcloner-backup-and-restore') ?>" id="ftp_timeout"
                   type="text" name="xcloner_ftp_timeout" class="validate"
                   value="<?php echo esc_attr(get_option("xcloner_ftp_timeout", 30)) ?>">
        </div>
    </div>

    <?php echo common_cleanup_html('ftp') ?>

    <div class="row">
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                    value="ftp"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">save</i>
            </button>
        </div>
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light orange" type="submit" name="action" id="action" value="ftp"
                    onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">import_export</i>
            </button>
        </div>
    </div>

</div>
