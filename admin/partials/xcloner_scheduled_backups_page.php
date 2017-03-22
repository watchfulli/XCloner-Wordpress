<?php
$xcloner_scheduler = $this->get_xcloner_container()->get_xcloner_scheduler();

$xcloner_remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();
$available_storages = $xcloner_remote_storage->get_available_storages();
?>
<?php if(!defined("DISABLE_WP_CRON") || !DISABLE_WP_CRON): ?>
	<div id="setting-error-" class="error settings-error notice is-dismissible"> 
		<p><strong>
			<?php echo sprintf(__('We have noticed that DISABLE_WP_CRON is disabled, we recommend enabling that and setting up wp-cron.php to run manually through your hosting account scheduler as explained <a href="%s" target="_blank">here</a>', 'xcloner-backup-and-restore'), "http://www.inmotionhosting.com/support/website/wordpress/disabling-the-wp-cronphp-in-wordpress") ?>
			</strong>
		</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>
<?php endif?>

<h1><?= esc_html(get_admin_page_title()); ?></h1>

<div class="row">
<table id="scheduled_backups" class="col s12" cellspacing="0" width="100%">
        <thead>
            <tr class="grey lighten-2">
                <th><?php echo __('ID', 'xcloner-backup-and-restore')?></th>
                <th><?php echo __('Schedule Name', 'xcloner-backup-and-restore')?></th>
                <th><?php echo __('Recurrence', 'xcloner-backup-and-restore')?></th>
                <th class="hide-on-med-and-down"><?php echo __('Next Execution', 'xcloner-backup-and-restore')?></th>
                <th><?php echo __('Remote Storage', 'xcloner-backup-and-restore')?></th>
                <th class="hide-on-med-and-down"><?php echo __('Last Backup', 'xcloner-backup-and-restore')?></th>
                <th><?php echo __('Status', 'xcloner-backup-and-restore')?></th>
                <th class="no-sort"><?php echo __('Action', 'xcloner-backup-and-restore')?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
				<th><?php echo __('ID', 'xcloner-backup-and-restore')?></th>
                <th><?php echo __('Schedule Name', 'xcloner-backup-and-restore')?></th>
                <th><?php echo __('Recurrence', 'xcloner-backup-and-restore')?></th>
                <th class="hide-on-med-and-down"><?php echo __('Next Execution', 'xcloner-backup-and-restore')?></th>
                <th><?php echo __('Remote Storage', 'xcloner-backup-and-restore')?></th>
                <th class="hide-on-med-and-down"><?php echo __('Last Backup', 'xcloner-backup-and-restore')?></th>
                <th><?php echo __('Status', 'xcloner-backup-and-restore')?></th>
                <th><?php echo __('Action', 'xcloner-backup-and-restore')?></th>
            </tr>
        </tfoot>
        <tbody>
        </tbody>    
</table>       
</div>

<div class="row">
	<div class="col s12 m6 offset-m6 teal lighten-1" id="server_time">
		<h2><?php echo __('Current Server Time', 'xcloner-backup-and-restore')?>: <span class="right"><?php echo current_time('mysql');?></span></h2>
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
			  <h4><?php echo __('Edit Schedule', 'xcloner-backup-and-restore') ?> #<span id="schedule_id"></span></h4>
			</div>	  
      
			<div class="col s12 m6 right-align">
				<div class="switch">
					<label>
					<?php echo __('Off', 'xcloner-backup-and-restore') ?>
					<input type="checkbox" id="status" name="status" value="1">
					<span class="lever"></span>
					<?php echo __('On', 'xcloner-backup-and-restore') ?>
					</label>
				</div>
			</div>	
	  </div>			  

	  <p>	
		  
		<ul class="nav-tab-wrapper content row"> 
			<li> <a href="#scheduler_settings" class="nav-tab col s12 m6 nav-tab-active"><?php echo __('Scheduler Settings', 'xcloner-backup-and-restore') ?></a></li>
			<li> <a href="#advanced_scheduler_settings" class="nav-tab col s12 m6"><?php echo __('Advanced', 'xcloner-backup-and-restore') ?></a></li>
		</ul>
		
		<div class="nav-tab-wrapper-content">
			<div id="scheduler_settings" class="tab-content active">
			
				<div class="row">
					<div class="input-field col s12">
						<input placeholder="" name="schedule_name" id="schedule_name" type="text" required value="">
						<label for="schedule_name"><?php echo __('Schedule Name', 'xcloner-backup-and-restore') ?></label>
					</div>
					<!--<div class="input-field inline col s12 l6">
						<select id="backup_type" class="" name="backup_type" id="backup_type">
							<option value=""><?php echo __("Full Backup","xcloner-backup-and-restore");?></option>
							<option value="diff"><?php echo __("Differential Backups","xcloner-backup-and-restore");?></option>
							<option value="full_diff"><?php echo __("Full Backup + Differential Backups","xcloner-backup-and-restore");?></option>
						</select>
						<label for="backup_type"><?php echo __('Scheduled Backup Type','xcloner-backup-and-restore')?></label>
					</div>-->
				</div>
				
				<div class="row">
					<div class="input-field col s12 l6">
						<input placeholder="" name="schedule_start_date" id="schedule_start_date" type="datetime"  value="">
						<label for="schedule_start_date" class="active"><?php echo __('Schedule Start At', 'xcloner-backup-and-restore') ?>:</label>
					</div>

					<div class="input-field col s12 l6">
						<select name="schedule_frequency" id="schedule_frequency" class="validate" required>
							<option value="" disabled selected><?php echo __('Schedule Recurrence', 'xcloner-backup-and-restore') ?></option>
							<?php
							$schedules = $xcloner_scheduler->get_available_intervals();
							
							foreach($schedules as $key=>$schedule)
							{
							?>
								<option value="<?php echo $key?>"><?php echo $schedule['display']?></option>
							<?php
							}
							?>
						</select>
					</div>
				</div>
				
				<?php if(sizeof($available_storages)):?>
				<div class="row">
					<div class="input-field col s12 l12">
						<select name="schedule_storage" id="schedule_storage" class="validate" >
							<option value="" selected><?php echo __('none', 'xcloner-backup-and-restore') ?></option>
							<?php foreach($available_storages as $storage=>$text):?>
								<option value="<?php echo $storage?>"><?php echo $text?></option>
							<?php endforeach?>
						</select>
						<label><?php echo __('Send To Remote Storage ', 'xcloner-backup-and-restore') ?></label>
					</div>
				</div>	
				<?php endif?>
				
				<div class="row">
					<div class="input-field col s12 l12">
						<input placeholder="" name="email_notification" id="email_notification" type="text" value="">
						<label for="email_notification"><?php echo __('Email Notification Address', 'xcloner-backup-and-restore') ?></label>
					</div>
				</div>
				
				<div class="row">
					<div class="input-field col s12 l12">
						<input placeholder="" name="diff_start_date" id="diff_start_date" type="text" class="datepicker_max_today" value="">
						<label for="diff_start_date"><?php echo __('Backup Only Files Modified/Created After', 'xcloner-backup-and-restore') ?></label>
					</div>
				</div>
			</div>
			
			<div id="advanced_scheduler_settings" class="tab-content">
				<div class="row">
					<div class="input-field col s12 l12">
						<input placeholder="" name="backup_name" id="backup_name" type="text" required value="">
						<label for="backup_name"><?php echo __('Backup Name', 'xcloner-backup-and-restore') ?></label>
					</div>
				</div>
				
				<div class="row">	
					<div class="input-field col s12 l12">
						<textarea id="table_params" name="table_params" class="materialize-textarea" rows="15"></textarea>
						<label for="table_params" class="active"><?php echo __('Included Database Data', 'xcloner-backup-and-restore') ?></label>
					</div>
				</div>
				
				<div class="row">	
					<div class="input-field col s12 l12">
						<textarea id="excluded_files" name="excluded_files" class="materialize-textarea" rows="15"></textarea>
						<label for="excluded_files" class="active"><?php echo __('Excluded Files', 'xcloner-backup-and-restore') ?></label>
					</div>
				</div>
			</div>	
		</div>
		
		<div class="row">
			
			<div class="input-field col s12 ">
				<button class="right btn waves-effect waves-light" type="submit" name="action"><?php echo __('Save', 'xcloner-backup-and-restore') ?>
					<i class="material-icons right">send</i>
				</button>
			</div>
		</div>
      </p>
    </div>
    </form>
</div>

