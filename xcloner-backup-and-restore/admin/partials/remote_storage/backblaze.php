<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="collapsible-header">
    <i class="material-icons">computer</i>
    <?php echo __("Backblaze B2 Storage", 'xcloner-backup-and-restore') ?>
    <div class="switch right">
        <label>
            Off
            <input type="checkbox" name="xcloner_backblaze_enable" class="status" value="1" <?php if (get_option("xcloner_backblaze_enable")) {
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
                <?php echo sprintf(__('Visit %s and get your KeyID and  applicationKey.', 'xcloner-backup-and-restore'), '<a href="https://secure.backblaze.com/b2_buckets.htm" target="_blank">https://secure.backblaze.com/b2_buckets.htm</a>') ?>
            </p>
            <p>
                If you specify <strong>only the bucket name</strong>, you must use the <strong>master key</strong>.<br>
                However, if you specify <strong>both bucket name and bucket id</strong>, you do not need the master key and can use a <strong>single-bucket key</strong>.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label
                for="backblaze_account_id"><?php echo __("Backblaze KeyID", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Backblaze KeyID", 'xcloner-backup-and-restore') ?>"
                id="backblaze_account_id" type="text" name="xcloner_backblaze_account_id" class="validate"
                value="<?php echo esc_attr(get_option("xcloner_backblaze_account_id")) ?>" autocomplete="off">
        </div>
    </div>


    <div class="row">
        <div class="col s12 m3 label">
            <label
                for="backblaze_application_key"><?php echo __("Backblaze applicationKey", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Backblaze applicationKey", 'xcloner-backup-and-restore') ?>"
                id="backblaze_application_key" type="text" name="xcloner_backblaze_application_key" class="validate"
                value="<?php echo esc_attr(str_repeat('*', strlen(get_option("xcloner_backblaze_application_key")))) ?>"
                autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label
                for="backblaze_bucket_name"><?php echo __("Backblaze Bucket Name", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Backblaze Bucket Name", 'xcloner-backup-and-restore') ?>"
                id="backblaze_bucket_name" type="text" name="xcloner_backblaze_bucket_name" class="validate"
                value="<?php echo esc_attr(get_option("xcloner_backblaze_bucket_name")) ?>" autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label
                    for="backblaze_bucket_id"><?php echo __("Backblaze Bucket ID", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Backblaze Bucket ID", 'xcloner-backup-and-restore') ?>"
                   id="backblaze_bucket_id" type="text" name="xcloner_backblaze_bucket_id" class="validate"
                   value="<?php echo esc_attr(get_option("xcloner_backblaze_bucket_id")) ?>" autocomplete="off">
        </div>
    </div>

    <?php echo common_cleanup_html('backblaze')?>

    <div class="row">
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                value="backblaze"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">save</i>
            </button>
        </div>
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"
                value="backblaze"
                onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">import_export</i>
            </button>
        </div>
    </div>

</div>
