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
    var storage_selection = '<?=$storage_selection?>';
</script>

<div class="row">
    <div class="col s12 m6 l9">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
    </div>
    <?php if (sizeof($available_storages)): ?>
    <div class="input-field  col s12 m6 l3 remote-storage-selection">
        <select name="storage_selection" id="storage_selection" class="validate" required>

            <?php if ($storage_selection): ?>
                <option value=""
                        selected><?php echo __('Change To Local Storage...', 'xcloner-backup-and-restore') ?></option>
            <?php else: ?>
                <option value=""
                        selected><?php echo __('Change To Remote Storage...', 'xcloner-backup-and-restore') ?></option>
            <?php endif; ?>

            <?php foreach ($available_storages as $storage => $text): ?>
                <option value="<?php echo $storage ?>"<?php if ($storage == $storage_selection) {
    echo "selected";
} ?>><?php echo $text ?></option>
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
                        <input name="select_all" class="" id="select_all" value="1" type="checkbox">
                        <label for="select_all">&nbsp;</label>
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
        
        <a class="waves-effect waves-light btn delete-all"><i
                class="material-icons left">delete</i><?php echo __("Delete", 'xcloner-backup-and-restore') ?></a>
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
                    <a class="waves-effect waves-light btn"><?php echo __("START ENCRYPTION", 'xcloner-backup-and-restore') ?></a>
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
                    <?=__('Provide Alternative Decryption Key:')?>
                    <input type="text"
                           name="decryption_key"
                           id="decryption_key"
                           placeholder="<?=__('Decryption Key', 'xcloner-backup-and-restore')?>" />
                </p>
                <p class="center-align">
                    <a class="waves-effect waves-light btn"><?php echo __("START DECRYPTION", 'xcloner-backup-and-restore') ?></a>
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
                        <label><?php echo sprintf(
    __('Send %s to remote storage', 'xcloner-backup-and-restore'),
    "<span class='backup_name'></span>"
) ?></label>
                    </div>
                    <div class="input-field col s8 m10">
                        <select name="transfer_storage" id="transfer_storage" class="validate" required>
                            <option value=""
                                    selected><?php echo __('please select...', 'xcloner-backup-and-restore') ?></option>
                            <?php foreach ($available_storages as $storage => $text): ?>
                                <option value="<?php echo $storage ?>"><?php echo $text ?></option>
                            <?php endforeach ?>
                        </select>

                    </div>
                    <div class="s4 m2 right">
                        <button type="submit"
                                class="upload-submit btn-floating btn-large waves-effect waves-light teal"><i
                                    class="material-icons">file_upload</i></submit>
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