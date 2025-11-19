<?php

$remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();

function common_cleanup_html( $type ): string {
    $typePrefix = ( $type === 'local' ) ? '' : $type . '_';

    return buildCleanupHtml( $typePrefix );
}

function buildCleanupHtml( $typePrefix ): string {
    $cleanupByAgePlaceholder      = __( "how many days to keep the backups for", 'xcloner-backup-and-restore' );
    $cleanupByQuantityPlaceholder = __( "how many backup files to keep", 'xcloner-backup-and-restore' );
    $cleanupByCapacityPlaceholder = __( "delete backup over specified limit", 'xcloner-backup-and-restore' );
    $keepBackupsPlaceholder       = __( "days of month, comma separated", 'xcloner-backup-and-restore' );

    $cleanupByAgeLabel      = __( "Cleanup by Age", 'xcloner-backup-and-restore' );
    $cleanupByQuantityLabel = __( "Cleanup by Quantity", 'xcloner-backup-and-restore' );
    $cleanupByCapacityLabel = __( "Cleanup by Capacity(MB)", 'xcloner-backup-and-restore' );
    $keepBackupsLabel       = __( "Keep backups taken on days", 'xcloner-backup-and-restore' );

    $cleanupByAgeValue      = esc_attr( get_option( "xcloner_{$typePrefix}cleanup_retention_limit_days" ) );
    $cleanupByQuantityValue = esc_attr( get_option( "xcloner_{$typePrefix}cleanup_retention_limit_archives" ) );
    $cleanupByCapacityValue = esc_attr( get_option( "xcloner_{$typePrefix}cleanup_capacity_limit" ) );
    $keepBackupsValue       = esc_attr( get_option( "xcloner_{$typePrefix}cleanup_exclude_days" ) );

    return <<<HTML
    <div class="row">
        <div class="col s12 m3 label">
            <label for="xcloner_{$typePrefix}cleanup_retention_limit_days">
                {$cleanupByAgeLabel}
            </label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="$cleanupByAgePlaceholder"
                id="xcloner_{$typePrefix}cleanup_retention_limit_days" type="text"
                name="xcloner_{$typePrefix}cleanup_retention_limit_days" class="validate"
                value="$cleanupByAgeValue">
        </div>
    </div>
    
    <!-- Cleanup by Quantity -->
    <div class="row">
        <div class="col s12 m3 label">
            <label for="xcloner_{$typePrefix}cleanup_retention_limit_archives">
                {$cleanupByQuantityLabel}
            </label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="$cleanupByQuantityPlaceholder"
                id="xcloner_{$typePrefix}cleanup_retention_limit_archives" type="number"
                name="xcloner_{$typePrefix}cleanup_retention_limit_archives" class="validate"
                value="$cleanupByQuantityValue">
        </div>
    </div>
    
    <!-- Cleanup by Capacity -->
    <div class="row">
        <div class="col s12 m3 label">
            <label for="xcloner_{$typePrefix}cleanup_capacity_limit">
                {$cleanupByCapacityLabel}
            </label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="$cleanupByCapacityPlaceholder"
                id="xcloner_{$typePrefix}cleanup_capacity_limit" type="number" 
                name="xcloner_{$typePrefix}cleanup_capacity_limit"
                class="validate" value="$cleanupByCapacityValue">
        </div>
    </div>
    
    <!-- Keep backups taken on days -->
    <div class="row">
        <div class="col s12 m3 label">
            <label for="xcloner_{$typePrefix}cleanup_exclude_days">
                {$keepBackupsLabel}
            </label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="$keepBackupsPlaceholder"
                id="xcloner_{$typePrefix}cleanup_exclude_days" type="text" 
                name="xcloner_{$typePrefix}cleanup_exclude_days"
                class="validate" value="$keepBackupsValue">
        </div>
    </div>
    HTML;
}

?>
<form class="remote-storage-form" method="POST">

    <?php wp_nonce_field('xcloner_remote_storage_action', 'xcloner_remote_storage_nonce'); ?>

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
