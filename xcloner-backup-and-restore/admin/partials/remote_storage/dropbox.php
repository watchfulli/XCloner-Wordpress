<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="collapsible-header">
    <i class="material-icons">computer</i><?php echo __("Dropbox Storage", 'xcloner-backup-and-restore') ?>
    <div class="switch right">
        <label>
            Off
            <input type="checkbox" name="xcloner_dropbox_enable" class="status" value="1" <?php if (get_option("xcloner_dropbox_enable")) {
                                        echo "checked";
                                    } ?> >
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
                <?php echo sprintf(__('Visit %s and get your "App secret".'), "<a href='https://www.dropbox.com/developers/apps' target='_blank'>https://www.dropbox.com/developers/apps</a>") ?>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label
                for="dropbox_access_token"><?php echo __("Dropbox Access Token", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Dropbox Access Token", 'xcloner-backup-and-restore') ?>"
                id="dropbox_access_token" type="text" name="xcloner_dropbox_access_token" class="validate"
                value="<?php echo esc_attr(get_option("xcloner_dropbox_access_token")) ?>" autocomplete="off">
        </div>
    </div>


    <div class="row">
        <div class="col s12 m3 label">
            <label for="dropbox_app_secret"><?php echo __("Dropbox App Secret", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Dropbox App Secret", 'xcloner-backup-and-restore') ?>"
                id="dropbox_app_secret" type="text" name="xcloner_dropbox_app_secret" class="validate"
                value="<?php echo esc_attr(str_repeat('*', strlen(get_option("xcloner_dropbox_app_secret")))) ?>"
                autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="dropbox_prefix"><?php echo __("Dropbox Prefix", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Dropbox Prefix", 'xcloner-backup-and-restore') ?>" id="dropbox_prefix"
                type="text" name="xcloner_dropbox_prefix" class="validate"
                value="<?php echo esc_attr(get_option("xcloner_dropbox_prefix")) ?>">
        </div>
    </div>

    <?php echo common_cleanup_html('dropbox')?>

    <div class="row">
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                value="dropbox"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">save</i>
            </button>
        </div>
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light orange" type="submit" name="action" id="action" value="dropbox"
                onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">import_export</i>
            </button>
        </div>
    </div>

</div>
