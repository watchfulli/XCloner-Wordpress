<?php

$xcloner_file_system = $this->get_xcloner_container()->get_xcloner_filesystem();
$xcloner_sanitization = $this->get_xcloner_container()->get_xcloner_sanitization();
$xcloner_remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();
$xcloner_encryption = $this->get_xcloner_container()->get_xcloner_encryption();
$storage_selection = "";

if (isset($_GET['storage_selection']) and $_GET['storage_selection']) {
    $storage_selection = $xcloner_sanitization->sanitize_input_as_string($_GET['storage_selection']);
}

//$backup_list = $xcloner_file_system->get_backup_archives_list($storage_selection);

$available_storages = $xcloner_remote_storage->get_available_storages();

?>

<script>
    var storage_selection = '<?php echo $storage_selection?>';
</script>

<div class="row">
    <div class="col s12">
        <?php include_once(__DIR__ . "/xcloner_header.php")?>
    </div>

    <?php if (sizeof($available_storages)): ?>
    <div class="input-field  col s12 l4 left remote-storage-selection">
        <select name="storage_selection" id="storage_selection" class="validate" required>

            <?php if ($storage_selection): ?>
            <option value="" selected>
                <?php echo __('Change To Local Storage...', 'xcloner-backup-and-restore') ?>
            </option>
            <?php else: ?>
            <option value="" selected>
                <?php echo __('Change To Remote Storage...', 'xcloner-backup-and-restore') ?>
            </option>
            <?php endif; ?>

            <?php foreach ($available_storages as $storage => $text): ?>
            <option value="<?php echo esc_attr($storage) ?>" <?php if ($storage == $storage_selection) {
                        echo "selected";
                    } ?>>
                <?php echo esc_html($text) ?>
            </option>
            <?php endforeach ?>
        </select>
        <?php endif ?>
    </div>

    <div class="col s12">
        <table id="manage_backups" style="width:100%">
            <thead>
                <tr class="grey lighten-2">
                    <th class="no-sort">
                        <p>
                            <label for="select_all">
                                <input name="select_all" class="" id="select_all" value="1" type="checkbox">
                                <span>&nbsp;</span>
                            </label>

                        </p>
                    </th>
                    <th data-field="id"><?php echo __("Backup Name", 'xcloner-backup-and-restore') ?></th>
                    <th data-field="name"><?php echo __("Created Time", 'xcloner-backup-and-restore') ?></th>
                    <th data-field="name"><?php echo __("Size", 'xcloner-backup-and-restore') ?></th>
                    <th class="no-sort" data-field="price"><?php echo __("Action", 'xcloner-backup-and-restore') ?></th>

                </tr>
            </thead>

            <tbody>

            </tbody>
        </table>

        <a class="waves-effect waves-light btn delete-all">
            <i class="material-icons left">delete</i>
            <?php echo __("Delete", 'xcloner-backup-and-restore') ?>
        </a>
    </div>


    <!-- List Backup Content Modal-->
    <div id="backup_cotent_modal" class="modal">
        <div class="modal-content">
            <h4><?php echo sprintf(__("Listing Backup Content ", 'xcloner-backup-and-restore'), "") ?></h4>
            <h5 class="backup-name"></h5>

            <div class="progress">
                <div class="indeterminate"></div>
            </div>
            <ul class="files-list"></ul>
        </div>
    </div>

    <!-- Backup Encryption Modal-->
    <div id="backup_encryption_modal" class="modal">
        <div class="modal-content">
            <h4><?php echo sprintf(__("Backup Content Encryption", 'xcloner-backup-and-restore'), "") ?></h4>
            <h5 class="backup-name"></h5>

            <div class="progress">
                <div class="determinate"></div>
            </div>
            <div class="notice">
                <p>
                    <?php echo __("This option will encrypt your backup archive with your current XCloner Encryption Key.", 'xcloner-backup-and-restore') ?>
                </p>
                <p class="center-align">
                    <a
                        class="waves-effect waves-light btn"><?php echo __("START ENCRYPTION", 'xcloner-backup-and-restore') ?></a>
                </p>
            </div>
            <ul class="files-list">
            </ul>
        </div>
    </div>

    <!-- Backup Decryption Modal-->
    <div id="backup_decryption_modal" class="modal">
        <div class="modal-content">
            <h4><?php echo sprintf(__("Backup Content Decryption", 'xcloner-backup-and-restore'), "") ?></h4>
            <h5 class="backup-name"></h5>

            <div class="progress">
                <div class="determinate"></div>
            </div>
            <div class="notice">
                <p>
                    <?php echo __("This option will decrypt your backup archive with your current XCloner Encryption Key or the key provided below, requires PHP openssl library installed.", 'xcloner-backup-and-restore') ?>
                </p>
                <p>
                    <?php echo __('Provide Alternative Decryption Key:')?>
                    <input type="text" name="decryption_key" id="decryption_key"
                        placeholder="<?php echo __('Decryption Key', 'xcloner-backup-and-restore')?>" />
                </p>
                <p class="center-align">
                    <a
                        class="waves-effect waves-light btn"><?php echo __("START DECRYPTION", 'xcloner-backup-and-restore') ?></a>
                </p>
            </div>
            <ul class="files-list">

            </ul>
        </div>
    </div>

    <!-- Local Transfer Modal-->
    <div id="local_storage_upload_modal" class="modal">
        <div class="modal-content">
            <h4>
                <?php echo sprintf(__("Transfer Remote Backup To Local Storage", 'xcloner-backup-and-restore'), "") ?>
            </h4>
            <h5 class="backup-name"></h5>

            <div class="row status">
                <div class="progress">
                    <div class="indeterminate"></div>
                </div>
                <?php echo __("Uploading backup to the local storage filesystem...", 'xcloner-backup-and-restore') ?>
                <span class="status-text"></span>
            </div>
        </div>
    </div>

    <!-- Remote Storage Modal Structure -->
    <div id="remote_storage_modal" class="modal">
        <form method="POST" class="remote-storage-form">
            <input type="hidden" name="file" class="backup_name">
            <div class="modal-content">
                <h4><?php echo __("Remote Storage Transfer", 'xcloner-backup-and-restore') ?></h4>
                <p>
                    <?php if (sizeof($available_storages)): ?>
                    <div class="row">
                        <div class="col s12 label">
                            <label>
                                <?php echo sprintf(__('Send %s to remote storage', 'xcloner-backup-and-restore'),"<span class='backup_name'></span>") ?>
                            </label>
                        </div>
                        <div class="input-field col s8 m10">
                            <select name="transfer_storage" id="transfer_storage" class="validate" required>
                                <option value="" selected>
                                    <?php echo __('please select...', 'xcloner-backup-and-restore') ?>
                                </option>
                                <?php foreach ($available_storages as $storage => $text): ?>
                                <option value="<?php echo esc_attr($storage) ?>">
                                    <?php echo esc_html($text) ?>
                                </option>
                                <?php endforeach ?>
                            </select>

                        </div>
                        <div class="s4 m2 right">
                            <button type="submit"
                                class="upload-submit btn-floating btn-large waves-effect waves-light teal"><i
                                        class="material-icons">file_upload</i>
                            </button>
                        </div>
                    </div>
                    <div class="row status">
                        <?php echo __("Uploading backup to the selected remote storage...", 'xcloner-backup-and-restore') ?>
                        <span class="status-text"></span>
                        <div class="progress">
                            <div class="indeterminate"></div>
                        </div>
                    </div>
                    <?php endif ?>
                </p>
            </div>
        </form>
    </div>

    <script>
        jQuery(document).ready(function () {

            xcloner_manage_backups.storage_selection = getUrlParam("storage_selection");

            dataTable = jQuery("#manage_backups").DataTable({
                responsive: true,
                bFilter: true,
                order: [
                    [2, "desc"]
                ],
                buttons: ["selectAll", "selectNone"],
                language: {
                    emptyTable: "No backups available",
                    buttons: {
                        selectAll: "Select all items",
                        selectNone: "Select none",
                    },
                },
                columnDefs: [{
                    targets: "no-sort",
                    orderable: false
                }],
                columns: [{
                        width: "1%"
                    },
                    {
                        width: "25%"
                    },
                    {
                        width: "5%"
                    },
                    {
                        width: "5%"
                    },
                    {
                        width: "9%"
                    },
                ],
                oLanguage: {
                    sSearch: "",
                    sSearchPlaceholder: "Search Backups",
                },
                ajax: {
                    url: XCLONER_AJAXURL +
                        "&action=get_manage_backups_list&storage_selection=" +
                        xcloner_manage_backups.storage_selection,
                },
                fnDrawCallback: function (oSettings) {
                    jQuery("a.expand-multipart").on("click", function () {
                        jQuery(this).parent().find("ul.multipart").toggle();
                        jQuery(this).parent().find("a.expand-multipart.remove").toggle();
                        jQuery(this).parent().find("a.expand-multipart.add").toggle();
                    });

                    jQuery(this)
                        .off("click", ".delete")
                        .on("click", ".delete", function (e) {
                            var hash = jQuery(this).attr("href");
                            var id = hash.substr(1);
                            var data = "";

                            if (show_delete_alert) {
                                if (confirm("Are you sure you want to delete it?")) {
                                    xcloner_manage_backups.delete_backup_by_name(id, this,
                                        dataTable);
                                }
                            } else {
                                xcloner_manage_backups.delete_backup_by_name(id, this,
                                    dataTable);
                            }

                            e.preventDefault();
                        });

                    jQuery(this)
                        .off("click", ".download")
                        .on("click", ".download", function (e) {
                            var hash = jQuery(this).attr("href");
                            var id = hash.substr(1);
                            xcloner_manage_backups.download_backup_by_name(id);
                            e.preventDefault();
                        });

                    jQuery(this)
                        .off("click", ".cloud-upload")
                        .on("click", ".cloud-upload", function (e) {
                            var hash = jQuery(this).attr("href");
                            var id = hash.substr(1);
                            xcloner_manage_backups.cloud_upload(id);
                            e.preventDefault();
                        });

                    jQuery(this)
                        .off("click", ".copy-remote-to-local")
                        .on("click", ".copy-remote-to-local", function (e) {
                            var hash = jQuery(this).attr("href");
                            var id = hash.substr(1);
                            xcloner_manage_backups.copy_remote_to_local(id);
                            e.preventDefault();
                        });

                    jQuery(this)
                        .off("click", ".list-backup-content")
                        .on("click", ".list-backup-content", function (e) {
                            var hash = jQuery(this).attr("href");
                            var id = hash.substr(1);
                            xcloner_manage_backups.list_backup_content(id);
                            e.preventDefault();
                        });

                    jQuery(this)
                        .off("click", ".backup-encryption")
                        .on("click", ".backup-encryption", function (e) {
                            var hash = jQuery(this).attr("href");
                            var id = hash.substr(1);
                            xcloner_manage_backups.backup_encryption(id);
                            e.preventDefault();
                        });

                    jQuery(this)
                        .off("click", ".backup-decryption")
                        .on("click", ".backup-decryption", function (e) {
                            var hash = jQuery(this).attr("href");
                            var id = hash.substr(1);
                            xcloner_manage_backups.backup_decryption(id);
                            e.preventDefault();
                        });
                },
            });

            jQuery("#select_all").click(function () {
                jQuery("input:checkbox").prop("checked", this.checked);
            });

            jQuery(".delete-all").click(function () {
                if (confirm("Are you sure you want to delete selected items?")) {
                    show_delete_alert = 0;
                    jQuery("input:checkbox").each(function () {
                        if (jQuery(this).is(":checked")) {
                            jQuery(this)
                                .parent()
                                .parent()
                                .parent()
                                .parent()
                                .find(".delete")
                                .trigger("click");
                        }
                    });
                    show_delete_alert = 1;
                }
            });

            //jQuery("#remote_storage_modal").modal();
            //jQuery("#local_storage_upload_modal").modal();

            jQuery("#storage_selection").on("change", function () {
                window.location =
                    window.location.href.split("&storage_selection")[0] +
                    "&storage_selection=" +
                    jQuery(this).val();
            });

            jQuery(".modal").on("hide", function () {
                alert("ok");
            });

            var show_delete_alert = 1;
        });
    </script>
