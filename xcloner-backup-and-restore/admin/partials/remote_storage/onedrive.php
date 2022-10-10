<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="collapsible-header">
    <i class="material-icons">computer</i><?php echo __("OneDrive Storage", 'xcloner-backup-and-restore') ?>
    <div class="switch right">
        <label>
            Off
            <input type="checkbox" name="xcloner_onedrive_enable" class="status" value="1" <?php if (get_option("xcloner_onedrive_enable")) {
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
                <?php echo sprintf(__('Visit <a href="%s" target="_blank">Microsoft Azure App Registrations</a> and get your Client ID and Client Secret. More details on setting up the code flow authentication can be found <a href="%s">here</a>. 
                                    Make sure to also add the %s to the Authentication->Redirect URIs area', 'xcloner-backup-and-restore'), 'https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade', 'https://docs.microsoft.com/en-us/onedrive/developer/rest-api/getting-started/graph-oauth?view=odsp-graph-online#code-flow', get_admin_url()) ?>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="onedrive_client_id"><?php echo __("OneDrive Client ID", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("OneDrive Client ID", 'xcloner-backup-and-restore') ?>"
                id="onedrive_client_id" type="text" name="xcloner_onedrive_client_id" class="validate"
                value="<?php echo esc_attr(get_option("xcloner_onedrive_client_id")) ?>" autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label
                for="onedrive_client_secret"><?php echo __("OneDrive Client Secret", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("OneDrive Client Secret", 'xcloner-backup-and-restore') ?>"
                id="onedrive_client_secret" type="text" name="xcloner_onedrive_client_secret" class="validate"
                value="<?php echo esc_attr(str_repeat('*', strlen(get_option("xcloner_onedrive_client_secret")))) ?>"
                autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            &nbsp;
        </div>
        <div class=" col s12 m6">
            <a class="btn" target="_blank" id="onedrive_authorization_click" onclick="jQuery(this).attr('href', jQuery(this).attr('target_href') + '&client_id=' + jQuery('#onedrive_client_id').val());
                                       jQuery('.onedrive-action').click()" href="#"
                target_href="https://login.microsoftonline.com/common/oauth2/v2.0/authorize?scope=offline_access files.readwrite.all  files.read files.read.all files.readwrite&response_type=code&redirect_uri=<?php echo get_admin_url('')?>"><?php echo sprintf(__('Authorize OneDrive', 'xcloner-backup-and-restore')) ?></a>

        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="onedrive_path"><?php echo __("OneDrive Storage Folder", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("OneDrive Storage Folder Path", 'xcloner-backup-and-restore') ?>"
                id="onedrive_path" type="text" name="xcloner_onedrive_path" class="validate"
                value="<?php echo esc_attr(urldecode(get_option("xcloner_onedrive_path") ?: '')) ?>">
        </div>
    </div>

    <?php echo common_cleanup_html('onedrive')?>

    <div class="row">
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light onedrive-action" type="submit" name="action" id="action"
                value="onedrive"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">save</i>
            </button>
        </div>
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light orange" type="submit" name="action" id="action" value="onedrive"
                onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">import_export</i>
            </button>
        </div>
    </div>

</div>
