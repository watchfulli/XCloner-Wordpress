<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
/** @var \Watchfulli\XClonerCore\Xcloner_Remote_Storage $remote_storage */
$remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();

$gdrive_auth_url = "";

if (method_exists($remote_storage, "gdrive_get_auth_url")) {
    $gdrive_auth_url = $remote_storage->gdrive_get_auth_url();
}

$gdrive_construct = $remote_storage->gdrive_construct();
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
                <?php echo sprintf(__('Click the Google Sign-in button below to complete the 1-time integration.'));  ?>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            &nbsp;
        </div>
        <div class=" col s12 m6">
            <a class="" target="_blank" id="gdrive_authorization_click"
                onclick="jQuery('#authentification_code').show()"
                href="<?php echo esc_url($gdrive_auth_url) ?>">
                <img src="<?php echo plugin_dir_url(__DIR__)?>/../../assets/btn_google_signin_dark_pressed_web.png"
                alt="<?php echo sprintf(__('Authorize Google Drive', 'xcloner-backup-and-restore')) ?>"/>
                </a>
            <input type="text" name="authentification_code" id="authentification_code"
                placeholder="<?php echo __("Paste Authorization Code Here", "xcloner-backup-and-restore") ?>">
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
                value="<?php echo esc_attr(get_option("xcloner_gdrive_target_folder")) ?>" autocomplete="off">
        </div>
    </div>

    <?php echo common_cleanup_html('gdrive')?>

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
                <a class="install-now btn" data-slug="xcloner-google-drive" href="<?php echo esc_url($url); ?>"
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
