<?php
$xcloner_scheduler = $this->get_xcloner_container()->get_xcloner_scheduler();

$xcloner_remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();
$available_storages     = $xcloner_remote_storage->get_available_storages();
?>
<?php if (!defined("DISABLE_WP_CRON") || !DISABLE_WP_CRON): ?>
<div id="setting-error-" class="error settings-error notice is-dismissible">
    <p><strong>
            <?php echo sprintf(__('We have noticed that DISABLE_WP_CRON is disabled, we recommend enabling that and setting up wp-cron.php to run manually through your hosting account scheduler as explained <a href="%s" target="_blank">here</a>', 'xcloner-backup-and-restore'), "http://www.inmotionhosting.com/support/website/wordpress/disabling-the-wp-cronphp-in-wordpress") // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </strong>
    </p>
    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
    </button>
</div>
<?php endif ?>

<div class="row">
    <div class="col s12">
        <?php include_once(__DIR__ . "/xcloner_header.php")?>
    </div>
    <table id="scheduled_backups" class="col s12" cellspacing="0" width="100%">
        <thead>
            <tr class="grey lighten-2">
                <th><?php echo esc_html__('ID', 'xcloner-backup-and-restore') ?></th>
                <th><?php echo esc_html__('Profile Name', 'xcloner-backup-and-restore') ?></th>
                <th><?php echo esc_html__('Recurrence', 'xcloner-backup-and-restore') ?></th>
                <th class="hide-on-med-and-down"><?php echo esc_html__('Next Execution', 'xcloner-backup-and-restore') ?></th>
                <th><?php echo esc_html__('Remote Storage', 'xcloner-backup-and-restore') ?></th>
                <th class="hide-on-med-and-down"><?php echo esc_html__('Last Backup', 'xcloner-backup-and-restore') ?></th>
                <th><?php echo esc_html__('Status', 'xcloner-backup-and-restore') ?></th>
                <th class="no-sort"><?php echo esc_html__('Action', 'xcloner-backup-and-restore') ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th><?php echo esc_html__('ID', 'xcloner-backup-and-restore') ?></th>
                <th><?php echo esc_html__('Schedule Name', 'xcloner-backup-and-restore') ?></th>
                <th><?php echo esc_html__('Recurrence', 'xcloner-backup-and-restore') ?></th>
                <th class="hide-on-med-and-down"><?php echo esc_html__('Next Execution', 'xcloner-backup-and-restore') ?></th>
                <th><?php echo esc_html__('Remote Storage', 'xcloner-backup-and-restore') ?></th>
                <th class="hide-on-med-and-down"><?php echo esc_html__('Last Backup', 'xcloner-backup-and-restore') ?></th>
                <th><?php echo esc_html__('Status', 'xcloner-backup-and-restore') ?></th>
                <th><?php echo esc_html__('Action', 'xcloner-backup-and-restore') ?></th>
            </tr>
        </tfoot>
        <tbody>
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col s12 m6 offset-m6 teal lighten-1" id="server_time">
        <h2><?php echo esc_html__('Current Server Time', 'xcloner-backup-and-restore') ?>: <span
                class="right"><?php echo esc_html(current_time('mysql')); ?></span></h2>
    </div>
</div>


<!-- Modal Structure -->
<div id="edit_schedule" class="modal">
    <form method="POST" action="" id="save_schedule">
        <input type="hidden" name="id" id="schedule_id_hidden">
        <input type="hidden" name="action" value="save_schedule">
        <div class="modal-content">

            <div class="row">
                <div class="col s12 m6">
                    <h4><?php echo esc_html__('Edit Schedule', 'xcloner-backup-and-restore') ?> #<span id="schedule_id"></span>
                    </h4>
                </div>

                <div class="col s12 m6 right-align">
                    <div class="switch">
                        <label>
                            <?php echo esc_html__('Off', 'xcloner-backup-and-restore') ?>
                            <input type="checkbox" id="status" name="status" value="1">
                            <span class="lever"></span>
                            <?php echo esc_html__('On', 'xcloner-backup-and-restore') ?>
                        </label>
                    </div>
                </div>
            </div>

            <p>

                <ul class="nav-tab-wrapper content row">
                    <li><a href="#scheduler_settings"
                            class="nav-tab col s12 m6 nav-tab-active"><?php echo esc_html__('Scheduler Settings', 'xcloner-backup-and-restore') ?></a>
                    </li>
                    <li><a href="#advanced_scheduler_settings"
                            class="nav-tab col s12 m6"><?php echo esc_html__('Advanced', 'xcloner-backup-and-restore') ?></a>
                    </li>
                </ul>

                <div class="nav-tab-wrapper-content">
                    <div id="scheduler_settings" class="tab-content active">

                        <div class="row">
                            <div class="input-field col s12 l6">
                                <input placeholder="" name="schedule_name" id="schedule_name" type="text" required
                                    value="">
                                <label
                                    for="schedule_name"><?php echo esc_html__('Schedule Name', 'xcloner-backup-and-restore') ?></label>
                            </div>
                            <div class="input-field col s12 l6">
                                <div class="switch">
                                    <label>
                                        Off
                                        <input type="checkbox" name="backup_encrypt" id="backup_encrypt" value="1">
                                        <span class="lever"></span>
                                        On
                                    </label>
                                </div>
                                <label
                                    style="top:-1.8em"><?php echo esc_html__('Encrypt Backup', 'xcloner-backup-and-restore') ?></label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12 l6">
                                <input placeholder="" name="schedule_start_date" id="schedule_start_date"
                                    type="datetime" value="">
                                <label for="schedule_start_date"
                                    class="active"><?php echo esc_html__('Schedule Start At', 'xcloner-backup-and-restore') ?>
                                    :</label>
                            </div>

                            <div class="input-field col s12 l6">
                                <select name="schedule_frequency" id="schedule_frequency" class="validate" required>
                                    <option value="" disabled selected>
                                        <?php echo esc_html__('Schedule Recurrence', 'xcloner-backup-and-restore') ?></option>
                                    <?php
								$schedules = $xcloner_scheduler->get_available_intervals();

								foreach ($schedules as $key => $schedule) {
									?>
                                    <option value="<?php echo esc_attr($key) ?>"><?php echo esc_html($schedule['display']) ?></option>
                                    <?php
								}
								?>
                                </select>
                            </div>
                        </div>

                        <?php if (sizeof($available_storages)): ?>
                        <div class="row">
                            <div class="input-field col s12 l6">
                                <select name="schedule_storage" id="schedule_storage" class="validate">
                                    <option value="" selected><?php echo esc_html__('none', 'xcloner-backup-and-restore') ?>
                                    </option>
                                    <?php foreach ($available_storages as $storage => $text): ?>
                                    <option value="<?php echo esc_attr($storage) ?>"><?php echo esc_html($text) ?></option>
                                    <?php endforeach ?>
                                </select>
                                <label><?php echo esc_html__('Send To Remote Storage', 'xcloner-backup-and-restore') ?></label>
                            </div>

                            <div class="input-field col s12 l6">
                                <div class="switch">
                                    <label>
                                        Off
                                        <input type="checkbox" name="backup_delete_after_remote_transfer"
                                            id="backup_delete_after_remote_transfer" value="1">
                                        <span class="lever"></span>
                                        On
                                    </label>
                                </div>
                                <label
                                    style="top:-1.8em"><?php echo esc_html__('Delete Local Copy After Transfer', 'xcloner-backup-and-restore') ?></label>
                            </div>
                        </div>


                        <?php endif ?>

                        <div class="row">
                            <div class="input-field col s12 l12">
                                <input placeholder="" name="email_notification" id="email_notification" type="text"
                                    value="">
                                <label
                                    for="email_notification"><?php echo esc_html__('Email Notification Address', 'xcloner-backup-and-restore') ?></label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12 l12">
                                <input placeholder="" name="diff_start_date" id="diff_start_date" type="text"
                                    class="datepicker_max_today" value="">
                                <label
                                    for="diff_start_date"><?php echo esc_html__('Backup Only Files Modified/Created After', 'xcloner-backup-and-restore') ?></label>
                            </div>
                        </div>
                    </div>

                    <div id="advanced_scheduler_settings" class="tab-content">
                        <div class="row">
                            <div class="input-field col s12 l12">
                                <input placeholder="" name="backup_name" id="backup_name" type="text" required value="">
                                <label
                                    for="backup_name"><?php echo esc_html__('Backup Name', 'xcloner-backup-and-restore') ?></label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12 l12">
                                <textarea id="table_params" name="table_params" class="materialize-textarea"
                                    rows="15"></textarea>
                                <label for="table_params"
                                    class="active"><?php echo esc_html__('Included Database Data', 'xcloner-backup-and-restore') ?></label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12 l12">
                                <textarea id="excluded_files" name="excluded_files" class="materialize-textarea"
                                    rows="15"></textarea>
                                <label for="excluded_files"
                                    class="active"><?php echo esc_html__('Excluded Files', 'xcloner-backup-and-restore') ?></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="input-field col s12 ">
                        <button class="right btn waves-effect waves-light" type="submit"
                            name="action"><?php echo esc_html__('Save', 'xcloner-backup-and-restore') ?>
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </div>
            </p>
        </div>
    </form>
</div>