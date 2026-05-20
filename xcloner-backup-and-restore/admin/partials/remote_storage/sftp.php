<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="collapsible-header">
    <i class="material-icons">computer</i><?php echo esc_html__("SFTP Storage", 'xcloner-backup-and-restore') ?>
    <div class="switch right">
        <label>
            Off
            <input type="checkbox"
                   name="xcloner_sftp_enable"
                   class="status"
                   value="1"
                <?php if (get_option("xcloner_sftp_enable")) { echo "checked"; } ?>
            >
            <span class="lever"></span>
            On
        </label>
    </div>
</div>
<div class="collapsible-body">
    <div class="row">
        <div class="col s12 m3 label">
            <label for="sftp_host"><?php echo esc_html__("SFTP Hostname", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class="col s12 m6">
            <input placeholder="<?php echo esc_html__("SFTP Hostname", 'xcloner-backup-and-restore') ?>"
                   id="sftp_host" type="text" name="xcloner_sftp_hostname" class="validate"
                   value="<?php echo esc_attr(get_option("xcloner_sftp_hostname")) ?>">
        </div>
        <div class=" col s12 m2">
            <input placeholder="<?php echo esc_html__("SFTP Port", 'xcloner-backup-and-restore') ?>"
                   id="sftp_port" type="text" name="xcloner_sftp_port" class="validate"
                   value="<?php echo esc_attr(get_option("xcloner_sftp_port", 22)) ?>">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="sftp_username"><?php echo esc_html__("SFTP Username", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo esc_html__("SFTP Username", 'xcloner-backup-and-restore') ?>"
                   id="sftp_username" type="text" name="xcloner_sftp_username" class="validate"
                   value="<?php echo esc_attr(get_option("xcloner_sftp_username")) ?>" autocomplete="off">
        </div>
    </div>


    <div class="row">
        <div class="col s12 m3 label">
            <label for="sftp_password"><?php echo esc_html__("SFTP or Private Key Password", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo esc_html__("SFTP or Private Key Password", 'xcloner-backup-and-restore') ?>"
                   id="ftp_spassword" type="text" name="xcloner_sftp_password" class="validate"
                   value="<?php echo esc_attr(str_repeat('*', strlen(get_option("xcloner_sftp_password")))) ?>"
                   autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="sftp_private_key"><?php echo esc_html__("SFTP Private Key(RSA)", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
                                <textarea rows="5"
                                          placeholder="<?php echo esc_html__("Local Server Path or Contents of the SFTP Private Key RSA File", 'xcloner-backup-and-restore') ?>"
                                          id="sftp_private_key" type="text" name="xcloner_sftp_private_key"
                                          class="validate"
                                          value=""><?php echo esc_attr(get_option("xcloner_sftp_private_key")) ?></textarea>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="sftp_root"><?php echo esc_html__("SFTP Storage Folder", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo esc_html__("SFTP Storage Folder", 'xcloner-backup-and-restore') ?>"
                   id="sftp_root" type="text" name="xcloner_sftp_path" class="validate"
                   value="<?php echo esc_attr(urldecode(get_option("xcloner_sftp_path") ?: '')) ?>">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="sftp_timeout"><?php echo esc_html__("SFTP Timeout", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m2">
            <input placeholder="<?php echo esc_html__("SFTP Timeout", 'xcloner-backup-and-restore') ?>"
                   id="sftp_timeout" type="text" name="xcloner_sftp_timeout" class="validate"
                   value="<?php echo esc_attr(get_option("xcloner_sftp_timeout", 30)) ?>">
        </div>
    </div>

    <?php echo common_cleanup_html('sftp') // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <div class="row">
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                    value="sftp"><?php echo esc_html__("Save Settings", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">save</i>
            </button>
        </div>
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light orange" type="submit" name="action"
                    id="action" value="sftp"
                    onclick="jQuery('#connection_check').val('1')"><?php echo esc_html__("Verify", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">import_export</i>
            </button>
        </div>
    </div>

</div>
