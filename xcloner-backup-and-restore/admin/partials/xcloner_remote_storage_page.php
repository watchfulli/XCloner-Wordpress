<?php
$remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();
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

        if (location.hash) {
            jQuery(location.hash).addClass("active");
        }

        jQuery('.collapsible').collapsible();

        M.updateTextFields();


    });
</script>
