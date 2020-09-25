<?php
$remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();

$gdrive_auth_url = "";

if (method_exists($remote_storage, "get_gdrive_auth_url")) {
    $gdrive_auth_url = $remote_storage->get_gdrive_auth_url();
}

$gdrive_construct = $remote_storage->gdrive_construct();
?>

<?php
function common_cleanup_html($type)
{
    if ($type == "local") {
        $type = "";
    }else{
        $type .= "_";
    }
    ob_start(); ?>
<!-- Cleanup by Days -->
<div class="row">
                            <div class="col s12 m3 label">
                                <label for="xcloner_{type}_cleanup_retention_limit_days"><?php echo __("Cleanup by Age", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("how many days to keep the backups for", 'xcloner-backup-and-restore') ?>"
                                       id="xcloner_{type}cleanup_retention_limit_days" type="text" name="xcloner_{type}cleanup_retention_limit_days"
                                       class="validate" value="<?php echo get_option("xcloner_".$type."cleanup_retention_limit_days") ?>">
                            </div>
                        </div>
                        
                        <!-- Cleanup by Quantity -->
                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="xcloner_{type}_cleanup_retention_limit_archives"><?php echo __("Cleanup by Quantity", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("how many backup files to keep", 'xcloner-backup-and-restore') ?>"
                                       id="xcloner_{type}cleanup_retention_limit_archives" type="number" name="xcloner_{type}cleanup_retention_limit_archives"
                                       class="validate"
                                       value="<?php echo get_option("xcloner_".$type."cleanup_retention_limit_archives") ?>">
                            </div>
                        </div>

                        <!-- Cleanup by Capacity -->
                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="xcloner_{type}_cleanup_capacity_limit"><?php echo __("Cleanup by Capacity(MB)", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("delete backup over specified limit", 'xcloner-backup-and-restore') ?>"
                                       id="xcloner_{type}cleanup_capacity_limit" type="number" name="xcloner_{type}cleanup_capacity_limit"
                                       class="validate"
                                       value="<?php echo get_option("xcloner_".$type."cleanup_capacity_limit") ?>">
                            </div>
                        </div>

                        <!-- Keep backups taken on days -->
                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="xcloner_{type}_cleanup_exclude_days"><?php echo __("Keep backups taken on days", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("days of month, comma separated", 'xcloner-backup-and-restore') ?>"
                                       id="xcloner_{type}cleanup_exclude_days" type="text" name="xcloner_{type}cleanup_exclude_days"
                                       class="validate"
                                       value="<?php echo get_option("xcloner_".$type."cleanup_exclude_days") ?>">
                            </div>
                        </div>
<?php
$common_cleanup_html = ob_get_contents();
    ob_end_clean();

    return str_replace("{type}", $type, $common_cleanup_html);
}

?>                        
<h1><?= esc_html(get_admin_page_title()); ?></h1>

<form class="remote-storage-form" method="POST">

    <input type="hidden" id="connection_check" name="connection_check" value="">

    <div class="row remote-storage">
        <div class="col s12 m12 l10">
            <ul class="collapsible popout" data-collapsible="accordion">

            <!-- LOCAL STORAGE-->
            <li id="local">
                    <div class="collapsible-header">
                        <i class="material-icons">computer</i><?php echo __("Local Storage", 'xcloner-backup-and-restore') ?>
                        <div class="switch right">
                            <label>
                                Off
                                <input type="checkbox" checked disabled class="disabled readonly" \>
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
                                <input placeholder="<?php echo __("Backup Start Location", 'xcloner-backup-and-restore') ?>"
                                       id="aws_key" type="text" name="xcloner_start_path" class="validate"
                                       value="<?php echo get_option("xcloner_start_path") ?>" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="aws_key"><?php echo __("Backup Storage Location", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Backup Storage Location", 'xcloner-backup-and-restore') ?>"
                                       id="aws_key" type="text" name="xcloner_store_path" class="validate"
                                       value="<?php echo get_option("xcloner_store_path") ?>" autocomplete="off">
                            </div>
                        </div>

                        <?=common_cleanup_html('local')?>

                        <div class="row">
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                                        value="local"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">save</i>
                                </button>
                            </div>
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light orange" type="submit" name="action"
                                        id="action" value="local"
                                        onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">import_export</i>
                                </button>
                            </div>
                        </div>

                    </div>
                </li>

            
                <!-- FTP STORAGE-->
                <li id="ftp">
                    <div class="collapsible-header">
                        <i class="material-icons">computer</i><?php echo __("FTP Storage", 'xcloner-backup-and-restore') ?>
                        <div class="switch right">
                            <label>
                                Off
                                <input type="checkbox" name="xcloner_ftp_enable" class="status"
                                       value="1" <?php if (get_option("xcloner_ftp_enable")) {
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
                                <label for="ftp_host"><?php echo __("Ftp Hostname", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class="col s12 m6">
                                <input placeholder="<?php echo __("Ftp Hostname", 'xcloner-backup-and-restore') ?>"
                                       id="ftp_host" type="text" name="xcloner_ftp_hostname" class="validate"
                                       value="<?php echo get_option("xcloner_ftp_hostname") ?>">
                            </div>
                            <div class=" col s12 m2">
                                <input placeholder="<?php echo __("Ftp Port", 'xcloner-backup-and-restore') ?>"
                                       id="ftp_port" type="text" name="xcloner_ftp_port" class="validate"
                                       value="<?php echo get_option("xcloner_ftp_port", 21) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="ftp_username"><?php echo __("Ftp Username", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Ftp Username", 'xcloner-backup-and-restore') ?>"
                                       id="ftp_username" type="text" name="xcloner_ftp_username" class="validate"
                                       value="<?php echo get_option("xcloner_ftp_username") ?>" autocomplete="off">
                            </div>
                        </div>


                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="ftp_password"><?php echo __("Ftp Password", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Ftp Password", 'xcloner-backup-and-restore') ?>"
                                       id="ftp_password" type="text" name="xcloner_ftp_password" class="validate"
                                       value="<?php echo str_repeat('*', strlen(get_option("xcloner_ftp_password"))) ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="ftp_root"><?php echo __("Ftp Storage Folder", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Ftp Storage Folder", 'xcloner-backup-and-restore') ?>"
                                       id="ftp_root" type="text" name="xcloner_ftp_path" class="validate"
                                       value="<?php echo get_option("xcloner_ftp_path") ?>">
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
                                <input placeholder="<?php echo __("Ftp Timeout", 'xcloner-backup-and-restore') ?>"
                                       id="ftp_timeout" type="text" name="xcloner_ftp_timeout" class="validate"
                                       value="<?php echo get_option("xcloner_ftp_timeout", 30) ?>">
                            </div>
                        </div>

                        <?=common_cleanup_html('ftp')?>

                        <div class="row">
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                                        value="ftp"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">save</i>
                                </button>
                            </div>
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light orange" type="submit" name="action"
                                        id="action" value="ftp"
                                        onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">import_export</i>
                                </button>
                            </div>
                        </div>

                    </div>
                </li>
                <!-- SFTP STORAGE-->
                <li id="sftp">
                    <div class="collapsible-header">
                        <i class="material-icons">computer</i><?php echo __("SFTP Storage", 'xcloner-backup-and-restore') ?>
                        <div class="switch right">
                            <label>
                                Off
                                <input type="checkbox" name="xcloner_sftp_enable" class="status"
                                       value="1" <?php if (get_option("xcloner_sftp_enable")) {
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
                                <label for="sftp_host"><?php echo __("SFTP Hostname", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class="col s12 m6">
                                <input placeholder="<?php echo __("SFTP Hostname", 'xcloner-backup-and-restore') ?>"
                                       id="sftp_host" type="text" name="xcloner_sftp_hostname" class="validate"
                                       value="<?php echo get_option("xcloner_sftp_hostname") ?>">
                            </div>
                            <div class=" col s12 m2">
                                <input placeholder="<?php echo __("SFTP Port", 'xcloner-backup-and-restore') ?>"
                                       id="sftp_port" type="text" name="xcloner_sftp_port" class="validate"
                                       value="<?php echo get_option("xcloner_sftp_port", 22) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="sftp_username"><?php echo __("SFTP Username", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("SFTP Username", 'xcloner-backup-and-restore') ?>"
                                       id="sftp_username" type="text" name="xcloner_sftp_username" class="validate"
                                       value="<?php echo get_option("xcloner_sftp_username") ?>" autocomplete="off">
                            </div>
                        </div>


                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="sftp_password"><?php echo __("SFTP or Private Key Password", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("SFTP or Private Key Password", 'xcloner-backup-and-restore') ?>"
                                       id="ftp_spassword" type="text" name="xcloner_sftp_password" class="validate"
                                       value="<?php echo str_repeat('*', strlen(get_option("xcloner_sftp_password"))) ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="sftp_private_key"><?php echo __("SFTP Private Key(RSA)", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <textarea rows="5"
                                          placeholder="<?php echo __("Local Server Path or Contents of the SFTP Private Key RSA File", 'xcloner-backup-and-restore') ?>"
                                          id="sftp_private_key" type="text" name="xcloner_sftp_private_key"
                                          class="validate"
                                          value=""><?php echo get_option("xcloner_sftp_private_key") ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="sftp_root"><?php echo __("SFTP Storage Folder", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("SFTP Storage Folder", 'xcloner-backup-and-restore') ?>"
                                       id="sftp_root" type="text" name="xcloner_sftp_path" class="validate"
                                       value="<?php echo get_option("xcloner_sftp_path") ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="sftp_timeout"><?php echo __("SFTP Timeout", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m2">
                                <input placeholder="<?php echo __("SFTP Timeout", 'xcloner-backup-and-restore') ?>"
                                       id="sftp_timeout" type="text" name="xcloner_sftp_timeout" class="validate"
                                       value="<?php echo get_option("xcloner_sftp_timeout", 30) ?>">
                            </div>
                        </div>

                        <?=common_cleanup_html('sftp')?>

                        <div class="row">
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                                        value="sftp"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">save</i>
                                </button>
                            </div>
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light orange" type="submit" name="action"
                                        id="action" value="sftp"
                                        onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">import_export</i>
                                </button>
                            </div>
                        </div>

                    </div>
                </li>

                <!-- AWS STORAGE-->
                <li id="aws">
                    <div class="collapsible-header">
                        <i class="material-icons">computer</i><?php echo __("Amazon S3 Storage", 'xcloner-backup-and-restore') ?>
                        <div class="switch right">
                            <label>
                                Off
                                <input type="checkbox" name="xcloner_aws_enable" class="status"
                                       value="1" <?php if (get_option("xcloner_aws_enable")) {
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
									<?php echo sprintf(__('Visit %s and get your "Key" and "Secret <br />Visit %s to install your own S3 like service.'), "<a href='https://aws.amazon.com/s3/' target='_blank'>https://aws.amazon.com/s3/</a>", "<a href='https://minio.io/' target='_blank'>https://minio.io/</a>") ?>
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="aws_key"><?php echo __("S3 Key", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("S3 Key", 'xcloner-backup-and-restore') ?>"
                                       id="aws_key" type="text" name="xcloner_aws_key" class="validate"
                                       value="<?php echo get_option("xcloner_aws_key") ?>" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="aws_secret"><?php echo __("S3 Secret", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("S3 Secret", 'xcloner-backup-and-restore') ?>"
                                       id="aws_secret" type="text" name="xcloner_aws_secret" class="validate"
                                       value="<?php echo str_repeat('*', strlen(get_option("xcloner_aws_secret"))) ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="aws_region"><?php echo __("S3 Region", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <select placeholder="<?php echo __("example: us-east-1", 'xcloner-backup-and-restore') ?>"
                                        id="aws_region" type="text" name="xcloner_aws_region" class="validate"
                                        value="<?php echo get_option("xcloner_aws_region") ?>" autocomplete="off">
                                    <option readonly
                                            value=""><?php echo __("Please Select AWS S3 Region or Leave Unselected for Custom Endpoint") ?></option>
									<?php
                                    $aws_regions = $remote_storage->get_aws_regions();

                                    foreach ($aws_regions as $key => $region) {
                                        ?>
                                        <option value="<?php echo $key ?>" <?php echo($key == get_option('xcloner_aws_region') ? "selected" : "") ?>><?php echo $region ?>
                                            = <?php echo $key ?></option>
										<?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div id="custom_aws_endpoint">
                            <div class="row">
                                <div class="col s12 m3 label">
                                    <label for="aws_endpoint"><?php echo __("S3 EndPoint", 'xcloner-backup-and-restore') ?></label>
                                </div>
                                <div class=" col s12 m6">
                                    <input placeholder="<?php echo __("S3 EndPoint, leave blank if you want to use the default Amazon AWS Service", 'xcloner-backup-and-restore') ?>"
                                        id="aws_endpoint" type="text" name="xcloner_aws_endpoint" class="validate"
                                        value="<?php echo get_option("xcloner_aws_endpoint") ?>" autocomplete="off">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col s12 m3 label">
                                    <label for="aws_region"><?php echo __("S3 Custom Region", 'xcloner-backup-and-restore') ?></label>
                                </div>
                                <div class=" col s12 m6">
                                    <input placeholder="<?php echo __("S3 Custom Region, ex: af-south-1", 'xcloner-backup-and-restore') ?>"
                                        id="aws_region" type="text" name="xcloner_aws_region" class="validate"
                                        value="<?php echo get_option("xcloner_aws_region") ?>" autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="aws_bucket_name"><?php echo __("S3 Bucket Name", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("S3 Bucket Name", 'xcloner-backup-and-restore') ?>"
                                       id="aws_bucket_name" type="text" name="xcloner_aws_bucket_name" class="validate"
                                       value="<?php echo get_option("xcloner_aws_bucket_name") ?>" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="aws_prefix"><?php echo __("S3 Prefix", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("S3 Prefix, use / ending to define a folder", 'xcloner-backup-and-restore') ?>"
                                       id="aws_prefix" type="text" name="xcloner_aws_prefix" class="validate"
                                       value="<?php echo get_option("xcloner_aws_prefix") ?>" autocomplete="off">
                            </div>
                        </div>

                        <?=common_cleanup_html('aws')?>

                        <div class="row">
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                                        value="aws"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">save</i>
                                </button>
                            </div>
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light orange" type="submit" name="action"
                                        id="action" value="aws"
                                        onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">import_export</i>
                                </button>
                            </div>
                        </div>

                    </div>
                </li>

                <!-- DROPBOX STORAGE-->
                <li id="dropbox">
                    <div class="collapsible-header">
                        <i class="material-icons">computer</i><?php echo __("Dropbox Storage", 'xcloner-backup-and-restore') ?>
                        <div class="switch right">
                            <label>
                                Off
                                <input type="checkbox" name="xcloner_dropbox_enable" class="status"
                                       value="1" <?php if (get_option("xcloner_dropbox_enable")) {
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
									<?php echo sprintf(__('Visit %s and get your "App secret".'), "<a href='https://www.dropbox.com/developers/apps' target='_blank'>https://www.dropbox.com/developers/apps</a>") ?>
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="dropbox_access_token"><?php echo __("Dropbox Access Token", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Dropbox Access Token", 'xcloner-backup-and-restore') ?>"
                                       id="dropbox_access_token" type="text" name="xcloner_dropbox_access_token"
                                       class="validate"
                                       value="<?php echo get_option("xcloner_dropbox_access_token") ?>"
                                       autocomplete="off">
                            </div>
                        </div>


                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="dropbox_app_secret"><?php echo __("Dropbox App Secret", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Dropbox App Secret", 'xcloner-backup-and-restore') ?>"
                                       id="dropbox_app_secret" type="text" name="xcloner_dropbox_app_secret"
                                       class="validate"
                                       value="<?php echo str_repeat('*', strlen(get_option("xcloner_dropbox_app_secret"))) ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="dropbox_prefix"><?php echo __("Dropbox Prefix", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Dropbox Prefix", 'xcloner-backup-and-restore') ?>"
                                       id="dropbox_prefix" type="text" name="xcloner_dropbox_prefix" class="validate"
                                       value="<?php echo get_option("xcloner_dropbox_prefix") ?>">
                            </div>
                        </div>

                        <?=common_cleanup_html('dropbox')?>

                        <div class="row">
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                                        value="dropbox"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">save</i>
                                </button>
                            </div>
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light orange" type="submit" name="action"
                                        id="action" value="dropbox"
                                        onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">import_export</i>
                                </button>
                            </div>
                        </div>

                    </div>
                </li>

                <!-- ONEDRIVE STORAGE-->
                <li id="onedrive">
                    <div class="collapsible-header">
                        <i class="material-icons">computer</i><?php echo __("OneDrive Storage", 'xcloner-backup-and-restore') ?>
                        <div class="switch right">
                            <label>
                                Off
                                <input type="checkbox" name="xcloner_onedrive_enable" class="status"
                                       value="1" <?php if (get_option("xcloner_onedrive_enable")) {
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
                                       value="<?=get_option("xcloner_onedrive_client_id") ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="onedrive_client_secret"><?php echo __("OneDrive Client Secret", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("OneDrive Client Secret", 'xcloner-backup-and-restore') ?>"
                                       id="onedrive_client_secret" type="text" name="xcloner_onedrive_client_secret" class="validate"
                                       value="<?php echo str_repeat('*', strlen(get_option("xcloner_onedrive_client_secret"))) ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                                <div class="col s12 m3 label">
                                    &nbsp;
                                </div>
                                <div class=" col s12 m6">
                                    <a class="btn" target="_blank" id="onedrive_authorization_click"
                                       onclick="jQuery(this).attr('href', jQuery(this).attr('target_href') + '&client_id=' + jQuery('#onedrive_client_id').val());
                                       jQuery('.onedrive-action').click()"
                                       href="#" 
                                       target_href="https://login.microsoftonline.com/common/oauth2/v2.0/authorize?scope=offline_access files.readwrite.all  files.read files.read.all files.readwrite&response_type=code&redirect_uri=<?=get_admin_url('')?>"><?php echo sprintf(__('Authorize OneDrive', 'xcloner-backup-and-restore')) ?></a>
                                    
                                </div>
                        </div>
                        
                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="onedrive_path"><?php echo __("OneDrive Storage Folder", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("OneDrive Storage Folder Path", 'xcloner-backup-and-restore') ?>"
                                       id="onedrive_path" type="text" name="xcloner_onedrive_path" class="validate"
                                       value="<?php echo get_option("xcloner_onedrive_path") ?>">
                            </div>
                        </div>

                        <?=common_cleanup_html('onedrive')?>

                        <div class="row">
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light onedrive-action" type="submit" name="action" id="action"
                                        value="onedrive"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">save</i>
                                </button>
                            </div>
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light orange" type="submit" name="action"
                                        id="action" value="onedrive"
                                        onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">import_export</i>
                                </button>
                            </div>
                        </div>

                    </div>
                </li>

                <!-- AZURE STORAGE-->
                <li id="azure">
                    <div class="collapsible-header">
                        <i class="material-icons">computer</i><?php echo __("Azure Blob Storage", 'xcloner-backup-and-restore') ?>
                        <div class="switch right">
                            <label>
                                Off
                                <input type="checkbox" name="xcloner_azure_enable" class="status"
                                       value="1" <?php if (get_option("xcloner_azure_enable")) {
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
                                       id="azure_account_name" type="text" name="xcloner_azure_account_name"
                                       class="validate" value="<?php echo get_option("xcloner_azure_account_name") ?>"
                                       autocomplete="off">
                            </div>
                        </div>


                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="azure_api_key"><?php echo __("Azure Api Key", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Azure Api Key", 'xcloner-backup-and-restore') ?>"
                                       id="azure_api_key" type="text" name="xcloner_azure_api_key" class="validate"
                                       value="<?php echo str_repeat('*', strlen(get_option("xcloner_azure_api_key"))) ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="azure_container"><?php echo __("Azure Container", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Azure Container", 'xcloner-backup-and-restore') ?>"
                                       id="azure_container" type="text" name="xcloner_azure_container" class="validate"
                                       value="<?php echo get_option("xcloner_azure_container") ?>">
                            </div>
                        </div>

                        <?=common_cleanup_html('azure')?>

                        <div class="row">
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                                        value="azure"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">save</i>
                                </button>
                            </div>
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light orange" type="submit" name="action"
                                        id="action" value="azure"
                                        onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">import_export</i>
                                </button>
                            </div>
                        </div>

                    </div>
                </li>

                <!-- BACKBLAZE STORAGE-->
                <li id="backblaze">
                    <div class="collapsible-header">
                        <i class="material-icons">computer</i><?php echo __("Backblaze B2 Storage", 'xcloner-backup-and-restore') ?>
                        <div class="switch right">
                            <label>
                                Off
                                <input type="checkbox" name="xcloner_backblaze_enable" class="status"
                                       value="1" <?php if (get_option("xcloner_backblaze_enable")) {
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
									<?php echo sprintf(__('Visit %s and get your KeyID and  applicationKey.', 'xcloner-backup-and-restore'), '<a href="https://secure.backblaze.com/b2_buckets.htm" target="_blank">https://secure.backblaze.com/b2_buckets.htm</a>') ?>
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="backblaze_account_id"><?php echo __("Backblaze KeyID", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Backblaze KeyID", 'xcloner-backup-and-restore') ?>"
                                       id="backblaze_account_id" type="text" name="xcloner_backblaze_account_id"
                                       class="validate"
                                       value="<?php echo get_option("xcloner_backblaze_account_id") ?>"
                                       autocomplete="off">
                            </div>
                        </div>


                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="backblaze_application_key"><?php echo __("Backblaze applicationKey", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Backblaze applicationKey", 'xcloner-backup-and-restore') ?>"
                                       id="backblaze_application_key" type="text"
                                       name="xcloner_backblaze_application_key" class="validate"
                                       value="<?php echo str_repeat('*', strlen(get_option("xcloner_backblaze_application_key"))) ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="backblaze_bucket_name"><?php echo __("Backblaze Bucket Name", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("Backblaze Bucket Name", 'xcloner-backup-and-restore') ?>"
                                       id="backblaze_bucket_name" type="text" name="xcloner_backblaze_bucket_name"
                                       class="validate"
                                       value="<?php echo get_option("xcloner_backblaze_bucket_name") ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <?=common_cleanup_html('backblaze')?>

                        <div class="row">
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                                        value="backblaze"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">save</i>
                                </button>
                            </div>
                            <div class="col s6 m4">
                                <button class="btn waves-effect waves-light orange" type="submit" name="action"
                                        id="action" value="backblaze"
                                        onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                                    <i class="material-icons right">import_export</i>
                                </button>
                            </div>
                        </div>

                    </div>
                </li>

                <!-- WEBDAV STORAGE-->
                <li id="webdav">
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
                                <input placeholder="<?php echo __("WebDAV Base Url", 'xcloner-backup-and-restore') ?>"
                                       id="webdav_url" type="text" name="xcloner_webdav_url" class="validate"
                                       value="<?php echo get_option("xcloner_webdav_url") ?>" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="webdav_username"><?php echo __("WebDAV Username", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("WebDAV Username", 'xcloner-backup-and-restore') ?>"
                                       id="webdav_username" type="text" name="xcloner_webdav_username" class="validate"
                                       value="<?php echo get_option("xcloner_webdav_username") ?>" autocomplete="off">
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
                                       value="<?php echo str_repeat('*', strlen(get_option("xcloner_webdav_password"))) ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col s12 m3 label">
                                <label for="webdav_target_folder"><?php echo __("WebDAV Target Folder", 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class=" col s12 m6">
                                <input placeholder="<?php echo __("WebDAV Target Folder", 'xcloner-backup-and-restore') ?>"
                                       id="webdav_target_folder" type="text" name="xcloner_webdav_target_folder"
                                       class="validate"
                                       value="<?php echo get_option("xcloner_webdav_target_folder") ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <?=common_cleanup_html('webdav')?>

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
                </li>

                <!-- Google DRIVE STORAGE-->
                <li id="gdrive">
                    <div class="collapsible-header">
                        <i class="material-icons">computer</i><?php echo __("Google Drive Storage", 'xcloner-backup-and-restore') ?>
						<?php if ($gdrive_construct): ?>
                            <div class="switch right">
                                <label>
                                    Off
                                    <input type="checkbox" name="xcloner_gdrive_enable" class="status"
                                           value="1" <?php if (get_option("xcloner_gdrive_enable")) {
                                        echo "checked";
                                    } ?> \>
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
										<?php echo sprintf(__('Visit %s to create a new application and get your Client ID and Client Secret.', 'xcloner-backup-and-restore'), '<a href="https://console.developers.google.com" target="_blank">https://console.developers.google.com</a>') ?>
                                        <a href="https://youtu.be/kBxf-39F4Nw" target="_blank"
                                           class="btn-floating tooltipped btn-small" data-position="right"
                                           data-delay="50" data-html="true"
                                           data-tooltip="<?php echo sprintf(__('Click here to view a short video explaining how to create the Client ID and Client Secret as well as connecting XCloner with the Google Drive API %s', 'xcloner-backup-and-restore'), "<br />https://youtu.be/kBxf-39F4Nw") ?>"
                                           data-tooltip-id="92c95730-94e9-7b59-bd52-14adc30d5e3e"><i
                                                    class="material-icons">help_outline</i></a>
                                    </p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col s12 m3 label">
                                    <label for="gdrive_client_id"><?php echo __("Client ID", 'xcloner-backup-and-restore') ?></label>
                                </div>
                                <div class=" col s12 m6">
                                    <input placeholder="<?php echo __("Google Client ID", 'xcloner-backup-and-restore') ?>"
                                           id="gdrive_client_id" type="text" name="xcloner_gdrive_client_id"
                                           class="validate"
                                           value="<?php echo get_option("xcloner_gdrive_client_id") ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col s12 m3 label">
                                    <label for="gdrive_client_secret"><?php echo __("Client Secret", 'xcloner-backup-and-restore') ?></label>
                                </div>
                                <div class=" col s12 m6">
                                    <input placeholder="<?php echo __("Google Client Secret", 'xcloner-backup-and-restore') ?>"
                                           id="gdrive_client_secret" type="text" name="xcloner_gdrive_client_secret"
                                           class="validate"
                                           value="<?php echo str_repeat('*', strlen(get_option("xcloner_gdrive_client_secret"))) ?>">
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
                                    <label for="gdrive_target_folder"><?php echo __("Folder ID or Root Path", 'xcloner-backup-and-restore') ?>
                                        <a class="btn-floating tooltipped btn-small" data-position="right"
                                           data-delay="50" data-html="true" \
                                           data-tooltip="<?php echo __('Folder ID can be found by right clicking on the folder name and selecting \'Get shareable link\' menu, format https://drive.google.com/open?id={FOLDER_ID}<br />
									If you supply a folder name, it has to exists in the drive root and start with / , example /backups.xcloner.com/', 'xcloner-backup-and-restore') ?>"
                                           data-tooltip-id="92c95730-94e9-7b59-bd52-14adc30d5e3e"><i
                                                    class="material-icons">help_outline</i></a>
                                    </label>
                                </div>
                                <div class=" col s12 m6">
                                    <input placeholder="<?php echo __("Target Folder ID or Root Path", 'xcloner-backup-and-restore') ?>"
                                           id="gdrive_target_folder" type="text" name="xcloner_gdrive_target_folder"
                                           class="validate"
                                           value="<?php echo get_option("xcloner_gdrive_target_folder") ?>"
                                           autocomplete="off">
                                </div>
                            </div>

                            <?=common_cleanup_html('gdrive')?>

                            <div class="row">
                                <div class="col s12 m3 label">
                                    <label for="gdrive_empty_trash"><?php echo __("Automatically Empty Trash?", 'xcloner-backup-and-restore') ?></label>
                                </div>
                                <div class=" col s12 m6 input-field inline">
                                    <p>
                                    <label for="gdrive_empty_trash_off">
                                        <input name="xcloner_gdrive_empty_trash" type="radio" value="0"
                                            id="gdrive_empty_trash_off" <?php if (!get_option("xcloner_gdrive_empty_trash", 0)) {
                                        echo "checked";
                                    } ?> />
                                        <span><?php echo __("Disabled", 'xcloner-backup-and-restore') ?></span>
                                    </label>
                                    </p>
                                    <p>
                                        <label for="gdrive_empty_trash_on">
                                        <input name="xcloner_gdrive_empty_trash" type="radio" value="1"
                                            id="gdrive_empty_trash_on" <?php if (get_option("xcloner_gdrive_empty_trash", 0)) {
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
                                    <button class="btn waves-effect waves-light orange" type="submit" name="action"
                                            id="action" value="gdrive"
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
                                        <h6><?php echo __("This storage option requires the XCloner-Google-Drive Wordpress Plugin to be installed and activated.") ?></h6>
                                        <h6><?php echo __("PHP 5.5 minimum version is required.") ?></h6>
                                        <br/>
                                        <a class="install-now btn" data-slug="xcloner-google-drive"
                                           href="<?php echo $url; ?>"
                                           aria-label="Install XCloner Google Drive 1.0.0 now"
                                           data-name="XCloner Google Drive 1.0.0">
											<?php echo sprintf(__('Install Now', 'xcloner-backup-and-restore')) ?>
                                        </a>

                                        <a href="<?php echo admin_url("plugin-install.php") ?>?tab=plugin-information&amp;plugin=xcloner-google-drive&amp;TB_iframe=true&amp;width=772&amp;height=499"
                                           class="btn thickbox open-plugin-details-modal"
                                           aria-label="More information about Theme Check 20160523.1"
                                           data-title="Theme Check 20160523.1">
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
                </li>


            </ul>
        </div>
    </div>

</form>

<script>
    function checkEndpoint() {
        if (jQuery("#aws_region").val() != "") {
            jQuery('#custom_aws_endpoint').hide();
            jQuery('#custom_aws_endpoint input').attr('disabled','disabled')
        } else {
            jQuery('#custom_aws_endpoint').show();
            jQuery('#custom_aws_endpoint input').removeAttr('disabled')
        }
    }

    jQuery(document).ready(function () {

        //var remote_storage = new Xcloner_Remote_Storage();

        checkEndpoint()
        jQuery("#aws_region").on("change", function () {
            checkEndpoint();

        })

        jQuery(".remote-storage .status").on("change", function () {
            remote_storage.toggle_status(this);
        })

        jQuery(".remote-storage-form #action").on("click", function () {
            var tag = jQuery(this).val()
            window.location.hash = "#" + tag;
        })

        jQuery("#gdrive_authorization_click").on("click", function (e) {

            var href = (jQuery(this).attr("href"))

            var new_href = href.replace(/(client_id=).*?(&)/, '$1' + jQuery("#gdrive_client_id").val() + '$2');

            jQuery(this).attr("href", new_href)

        });

        if (location.hash) {
            jQuery(location.hash).addClass("active");
        }

        jQuery('.collapsible').collapsible();

        M.updateTextFields();


    });

</script>
