<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="collapsible-header">
    <i class="material-icons">computer</i><?php echo __("Azure Blob Storage", 'xcloner-backup-and-restore') ?>
    <div class="switch right">
        <label>
            Off
            <input type="checkbox" name="xcloner_azure_enable" class="status" value="1" <?php if (get_option("xcloner_azure_enable")) {
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
                <?php echo sprintf(__('Visit %s and get your "Api Key".', 'xcloner-backup-and-restore'), '<a href="https://azure.microsoft.com/en-us/services/storage/blobs/" target="_blank">https://azure.microsoft.com/en-us/services/storage/blobs/</a>') ?>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="azure_account_name"><?php echo __("Azure Account Name", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Azure Account Name", 'xcloner-backup-and-restore') ?>"
                id="azure_account_name" type="text" name="xcloner_azure_account_name" class="validate"
                value="<?php echo esc_attr(get_option("xcloner_azure_account_name")) ?>" autocomplete="off">
        </div>
    </div>


    <div class="row">
        <div class="col s12 m3 label">
            <label for="azure_api_key"><?php echo __("Azure Api Key", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Azure Api Key", 'xcloner-backup-and-restore') ?>" id="azure_api_key"
                type="text" name="xcloner_azure_api_key" class="validate"
                value="<?php echo esc_attr(str_repeat('*', strlen(get_option("xcloner_azure_api_key")))) ?>" autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="azure_container"><?php echo __("Azure Container", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Azure Container", 'xcloner-backup-and-restore') ?>" id="azure_container"
                type="text" name="xcloner_azure_container" class="validate"
                value="<?php echo esc_attr(get_option("xcloner_azure_container")) ?>">
        </div>
    </div>

    <?php echo common_cleanup_html('azure')?>

    <div class="row">
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                value="azure"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">save</i>
            </button>
        </div>
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light orange" type="submit" name="action" id="action" value="azure"
                onclick="jQuery('#connection_check').val('1')">
                <?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">import_export</i>
            </button>
        </div>
    </div>

</div>
