<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="collapsible-header">
    <i class="material-icons">computer</i><?php echo __("Google Drive Storage", 'xcloner-backup-and-restore') ?>
    <?php if ($gdrive_construct): ?>
    <div class="switch right">
        <label>
            Off
            <input type="checkbox" name="xcloner_gdrive_enable" class="status" value="1" <?php if (get_option("xcloner_gdrive_enable")) {
                                        echo "checked";
                                    } ?> >
            <span class="lever"></span>
            On
        </label>
    </div>
    <?php endif ?>
</div>
<div class="collapsible-body">

    <?php if ($gdrive_construct) : ?>

    <div class="row">
        <div class="col s12 m3 label">
            &nbsp;
        </div>
        <div class=" col s12 m9">
            <p>
                <?php echo sprintf(__('If you would like to create your custom app, please visit %s to create a new application and get your Client ID and Client Secret. Otherwise, you can use the default XCloner.com Google Drive app by leaving them blank.', 'xcloner-backup-and-restore'), '<a href="https://console.developers.google.com" target="_blank">https://console.developers.google.com</a>') ?>
                <a href="https://youtu.be/kBxf-39F4Nw" target="_blank" class="btn-floating tooltipped btn-small"
                    data-position="right" data-delay="50" data-html="true"
                    data-tooltip="<?php echo sprintf(__('Click here to view a short video explaining how to create the Client ID and Client Secret as well as connecting XCloner with the Google Drive API %s', 'xcloner-backup-and-restore'), "<br />https://youtu.be/kBxf-39F4Nw") ?>"
                    data-tooltip-id="92c95730-94e9-7b59-bd52-14adc30d5e3e"><i
                        class="material-icons">help_outline</i></a>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            &nbsp;
        </div>
        <div class=" col s12 m6">
            <a class="btn" target="_blank" id="gdrive_authorization_click"
                onclick="jQuery('#authentification_code').show()"
                href="<?php echo $gdrive_auth_url ?>"><?php echo sprintf(__('Authorize Google Drive', 'xcloner-backup-and-restore')) ?></a>
            <input type="text" name="authentification_code" id="authentification_code"
                placeholder="<?php echo __("Paste Authorization Code Here", "xcloner-backup-and-restore") ?>">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="gdrive_client_id"><?php echo __("Client ID", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input
                placeholder="<?php echo __("Leave blank unless you wish to set up Google Drive integration manually", 'xcloner-backup-and-restore') ?>"
                id="gdrive_client_id" type="text" name="xcloner_gdrive_client_id" class="validate"
                value="<?php echo get_option("xcloner_gdrive_client_id") ?>"
                default-client-id=<?=$remote_storage::GDRIVE_AUTH_WATCHFUL?>>
        </div>
    </div>

    <div class="row" id="gdrive_client_secret_wrapper">
        <div class="col s12 m3 label">
            <label for="gdrive_client_secret"><?php echo __("Client Secret", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Google Client Secret", 'xcloner-backup-and-restore') ?>"
                id="gdrive_client_secret" type="text" name="xcloner_gdrive_client_secret" class="validate"
                value="<?php echo str_repeat('*', strlen(get_option("xcloner_gdrive_client_secret"))) ?>">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="gdrive_target_folder"><?php echo __("Folder ID or Root Path", 'xcloner-backup-and-restore') ?>
                <a class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-html="true" \
                    data-tooltip="<?php echo __('Folder ID can be found by right clicking on the folder name and selecting \'Get shareable link\' menu, format https://drive.google.com/open?id={FOLDER_ID}<br />
									If you supply a folder name, it has to exists in the drive root and start with / , example /backups.xcloner.com/', 'xcloner-backup-and-restore') ?>"
                    data-tooltip-id="92c95730-94e9-7b59-bd52-14adc30d5e3e"><i
                        class="material-icons">help_outline</i></a>
            </label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("Target Folder ID or Root Path", 'xcloner-backup-and-restore') ?>"
                id="gdrive_target_folder" type="text" name="xcloner_gdrive_target_folder" class="validate"
                value="<?php echo get_option("xcloner_gdrive_target_folder") ?>" autocomplete="off">
        </div>
    </div>

    <?=common_cleanup_html('gdrive')?>

    <div class="row">
        <div class="col s12 m3 label">
            <label
                for="gdrive_empty_trash"><?php echo __("Automatically Empty Trash?", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6 input-field inline">
            <p>
                <label for="gdrive_empty_trash_off">
                    <input name="xcloner_gdrive_empty_trash" type="radio" value="0" id="gdrive_empty_trash_off" <?php if (!get_option("xcloner_gdrive_empty_trash", 0)) {
                                        echo "checked";
                                    } ?> />
                    <span><?php echo __("Disabled", 'xcloner-backup-and-restore') ?></span>
                </label>
            </p>
            <p>
                <label for="gdrive_empty_trash_on">
                    <input name="xcloner_gdrive_empty_trash" type="radio" value="1" id="gdrive_empty_trash_on" <?php if (get_option("xcloner_gdrive_empty_trash", 0)) {
                                        echo "checked";
                                    } ?> />
                    <span><?php echo __("Enabled", 'xcloner-backup-and-restore') ?></span>
                </label>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                value="gdrive"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">save</i>
            </button>
        </div>
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light orange" type="submit" name="action" id="action" value="gdrive"
                onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">import_export</i>
            </button>
        </div>
    </div>
    <?php else: ?>

    <div class="row">
        <div class=" col s12">
            <div class="center">
                <?php
                                        $url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=xcloner-google-drive'), 'install-plugin_xcloner-google-drive');
                                        ?>
                <h6><?php echo __("This storage option requires the XCloner-Google-Drive Wordpress Plugin to be installed and activated.") ?>
                </h6>
                <h6><?php echo __("PHP 5.5 minimum version is required.") ?></h6>
                <br />
                <a class="install-now btn" data-slug="xcloner-google-drive" href="<?php echo $url; ?>"
                    aria-label="Install XCloner Google Drive 1.0.0 now" data-name="XCloner Google Drive 1.0.0">
                    <?php echo sprintf(__('Install Now', 'xcloner-backup-and-restore')) ?>
                </a>

                <a href="<?php echo admin_url("plugin-install.php") ?>?tab=plugin-information&amp;plugin=xcloner-google-drive&amp;TB_iframe=true&amp;width=772&amp;height=499"
                    class="btn thickbox open-plugin-details-modal"
                    aria-label="More information about Theme Check 20160523.1" data-title="Theme Check 20160523.1">
                    <!--
											<a class="btn" href="https://github.com/ovidiul/XCloner-Google-Drive/archive/master.zip">
											-->
                    <?php echo sprintf(__('More Details', 'xcloner-backup-and-restore')) ?>
                </a>
            </div>
        </div>
    </div>

    <?php endif; ?>

</div>