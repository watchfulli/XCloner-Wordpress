<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="collapsible-header">
    <i class="material-icons">computer</i><?php echo __("Local Storage", 'xcloner-backup-and-restore') ?>
    <div class="switch right">
        <label>
            Off
            <input type="checkbox" checked disabled class="disabled readonly" >
            <span class="lever"></span>
            On
        </label>
    </div>
</div>
<div class="collapsible-body">

    <div class="row">
        <div class="col s12 m3 label">
            <label for="aws_key"><?php echo __("Backup Start Location", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Backup Start Location", 'xcloner-backup-and-restore') ?>" id="aws_key"
                type="text" name="xcloner_start_path" class="validate"
                value="<?php echo esc_attr(get_option("xcloner_start_path")) ?>" autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="aws_key"><?php echo __("Backup Storage Location", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Backup Storage Location", 'xcloner-backup-and-restore') ?>" id="aws_key"
                type="text" name="xcloner_store_path" class="validate"
                value="<?php echo esc_attr(get_option("xcloner_store_path")) ?>" autocomplete="off">
        </div>
    </div>

    <?php echo common_cleanup_html('local')?>

    <div class="row">
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                value="local"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">save</i>
            </button>
        </div>
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light orange" type="submit" name="action" id="action" value="local"
                onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">import_export</i>
            </button>
        </div>
    </div>

</div>
