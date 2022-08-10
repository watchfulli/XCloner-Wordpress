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
    } else {
        $type .= "_";
    }
    ob_start(); ?>
<!-- Cleanup by Days -->
<div class="row">
    <div class="col s12 m3 label">
        <label for="xcloner_{type}_cleanup_retention_limit_days">
            <?php echo __("Cleanup by Age", 'xcloner-backup-and-restore') ?>
        </label>
    </div>
    <div class=" col s12 m6">
        <input placeholder="<?php echo __("how many days to keep the backups for", 'xcloner-backup-and-restore') ?>"
            id="xcloner_{type}cleanup_retention_limit_days" type="text"
            name="xcloner_{type}cleanup_retention_limit_days" class="validate"
            value="<?php echo esc_attr(get_option("xcloner_".$type."cleanup_retention_limit_days")) ?>">
    </div>
</div>

<!-- Cleanup by Quantity -->
<div class="row">
    <div class="col s12 m3 label">
        <label
            for="xcloner_{type}_cleanup_retention_limit_archives"><?php echo __("Cleanup by Quantity", 'xcloner-backup-and-restore') ?></label>
    </div>
    <div class=" col s12 m6">
        <input placeholder="<?php echo __("how many backup files to keep", 'xcloner-backup-and-restore') ?>"
            id="xcloner_{type}cleanup_retention_limit_archives" type="number"
            name="xcloner_{type}cleanup_retention_limit_archives" class="validate"
            value="<?php echo esc_attr(get_option("xcloner_".$type."cleanup_retention_limit_archives")) ?>">
    </div>
</div>

<!-- Cleanup by Capacity -->
<div class="row">
    <div class="col s12 m3 label">
        <label for="xcloner_{type}_cleanup_capacity_limit">
            <?php echo __("Cleanup by Capacity(MB)", 'xcloner-backup-and-restore') ?>
        </label>
    </div>
    <div class=" col s12 m6">
        <input placeholder="<?php echo __("delete backup over specified limit", 'xcloner-backup-and-restore') ?>"
            id="xcloner_{type}cleanup_capacity_limit" type="number" name="xcloner_{type}cleanup_capacity_limit"
            class="validate" value="<?php echo esc_attr(get_option("xcloner_".$type."cleanup_capacity_limit")) ?>">
    </div>
</div>

<!-- Keep backups taken on days -->
<div class="row">
    <div class="col s12 m3 label">
        <label for="xcloner_{type}_cleanup_exclude_days">
            <?php echo __("Keep backups taken on days", 'xcloner-backup-and-restore') ?>
        </label>
    </div>
    <div class=" col s12 m6">
        <input placeholder="<?php echo __("days of month, comma separated", 'xcloner-backup-and-restore') ?>"
            id="xcloner_{type}cleanup_exclude_days" type="text" name="xcloner_{type}cleanup_exclude_days"
            class="validate" value="<?php echo esc_attr(get_option("xcloner_".$type."cleanup_exclude_days")) ?>">
    </div>
</div>
<?php
$common_cleanup_html = ob_get_contents();
    ob_end_clean();

    return str_replace("{type}", $type, $common_cleanup_html);
}

?>
<form class="remote-storage-form" method="POST">

    <input type="hidden" id="connection_check" name="connection_check" value="">

    <div class="row remote-storage">
        <div class="col s12 m12 l10">
            <?php include_once(__DIR__ . "/xcloner_header.php")?>
        </div>
        <div class="col s12 m12 l10">
            <ul class="collapsible popout" data-collapsible="accordion">

                <!-- LOCAL STORAGE-->
                <li id="local">
                    <?php include_once(__DIR__ ."/remote_storage/local.php") ?>
                </li>

                <!-- AWS STORAGE-->
                <li id="aws">
                    <?php include_once(__DIR__ ."/remote_storage/aws.php") ?>
                </li>

                <!-- AZURE STORAGE-->
                <li id="azure">
                    <?php include_once(__DIR__ ."/remote_storage/azure.php") ?>
                </li>

                <!-- BACKBLAZE STORAGE-->
                <li id="backblaze">
                    <?php include_once(__DIR__ ."/remote_storage/backblaze.php") ?>
                </li>

                <!-- DROPBOX STORAGE-->
                <li id="dropbox">
                    <?php include_once(__DIR__ ."/remote_storage/dropbox.php") ?>
                </li>

                <!-- FTP STORAGE-->
                <li id="ftp">
                    <?php include_once(__DIR__ ."/remote_storage/ftp.php") ?>
                </li>

                <!-- Google DRIVE STORAGE-->
                <li id="gdrive">
                    <?php include_once(__DIR__ ."/remote_storage/gdrive.php") ?>
                </li>

                <!-- SFTP STORAGE-->
                <li id="sftp">
                    <?php include_once(__DIR__ ."/remote_storage/sftp.php") ?>
                </li>

                <!-- ONEDRIVE STORAGE-->
                <li id="onedrive">
                    <?php include_once(__DIR__ ."/remote_storage/onedrive.php") ?>
                </li>

                <!-- WEBDAV STORAGE-->
                <li id="webdav">
                    <?php include_once(__DIR__ ."/remote_storage/webdav.php") ?>
                </li>

            </ul>
        </div>
    </div>

</form>

<script>
    function checkEndpoint() {
        if (jQuery("#aws_region").val() != "") {
            jQuery('#custom_aws_endpoint').hide();
            jQuery('#custom_aws_endpoint input').attr('disabled', 'disabled')
        } else {
            jQuery('#custom_aws_endpoint').show();
            jQuery('#custom_aws_endpoint input').removeAttr('disabled')
        }
    }

    jQuery(document).ready(function () {

        //var remote_storage = new Xcloner_Remote_Storage();

        var watchful_gdrive_redirect_uri = "<?php echo $remote_storage::GDRIVE_REDIRECT_URL_WATCHFUL?>";
        var default_gdrive_redirect_uri = "<?php echo $remote_storage::GDRIVE_REDIRECT_URL?>";

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

        if (!jQuery("#gdrive_client_id").val()) {
            jQuery("#gdrive_client_secret_wrapper").hide();
        }

        jQuery("#gdrive_client_id").on('keyup', function () {

            if (jQuery("#gdrive_client_id").val()) {
                jQuery("#gdrive_client_secret_wrapper").show();
            } else {
                jQuery("#gdrive_client_secret_wrapper").hide();
            }

        })

        jQuery("#gdrive_authorization_click").on("click", function (e) {

            var href = (jQuery(this).attr("href"))

            var client_id = jQuery("#gdrive_client_id").val() || jQuery("#gdrive_client_id").attr(
                'default-client-id')

            var redirect_uri = default_gdrive_redirect_uri;

            if (client_id === jQuery("#gdrive_client_id").attr('default-client-id')) {
                redirect_uri = watchful_gdrive_redirect_uri
            }

            var new_href = href.
            replace(/(client_id=).*?(&)/, '$1' + client_id + '$2').
            replace(/(redirect_uri=).*?(&)/, '$1' + redirect_uri + '$2');

            console.log(new_href)
            jQuery(this).attr("href", new_href)

        });

        if (location.hash) {
            jQuery(location.hash).addClass("active");
        }

        jQuery('.collapsible').collapsible();

        M.updateTextFields();


    });
</script>
