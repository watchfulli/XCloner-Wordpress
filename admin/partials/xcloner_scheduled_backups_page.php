<?php
$xcloner_scheduler = new Xcloner_Scheduler();
$xcloner_remote_storage = new Xcloner_Remote_Storage();
$available_storages = $xcloner_remote_storage->get_available_storages();
?>
<?php if(!defined("DISABLE_WP_CRON") or !DISABLE_WP_CRON): ?>
	<div id="setting-error-" class="error settings-error notice is-dismissible"> 
		<p><strong>
			<?php echo sprintf(__('We have noticed that DISABLE_WP_CRON is disabled, we recommend enabling that and setting up wp-cron.php to run manually through your hosting account scheduler as explained <a href="%s" target="_blank">here</a>', 'xcloner'), "http://www.inmotionhosting.com/support/website/wordpress/disabling-the-wp-cronphp-in-wordpress") ?>
			</strong>
		</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>
<?php endif?>

<h1><?= esc_html(get_admin_page_title()); ?></h1>

<div class="row">
<table id="scheduled_backups" class="col s12" cellspacing="0" width="100%">
        <thead>
            <tr class="grey lighten-2">
                <th><?php echo __('ID', 'xcloner')?></th>
                <th><?php echo __('Schedule Name', 'xcloner')?></th>
                <th><?php echo __('Recurrence', 'xcloner')?></th>
                <th class="hide-on-med-and-down"><?php echo __('Next Execution', 'xcloner')?></th>
                <th><?php echo __('Remote Storage', 'xcloner')?></th>
                <th class="hide-on-med-and-down"><?php echo __('Last Backup', 'xcloner')?></th>
                <th><?php echo __('Status', 'xcloner')?></th>
                <th class="no-sort"><?php echo __('Action', 'xcloner')?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
				<th><?php echo __('ID', 'xcloner')?></th>
                <th><?php echo __('Schedule Name', 'xcloner')?></th>
                <th><?php echo __('Recurrence', 'xcloner')?></th>
                <th class="hide-on-med-and-down"><?php echo __('Next Execution', 'xcloner')?></th>
                <th><?php echo __('Remote Storage', 'xcloner')?></th>
                <th class="hide-on-med-and-down"><?php echo __('Last Backup', 'xcloner')?></th>
                <th><?php echo __('Status', 'xcloner')?></th>
                <th><?php echo __('Action', 'xcloner')?></th>
            </tr>
        </tfoot>
        <tbody>
        </tbody>    
</table>       
</div>

<div class="row">
	<div class="col s12 m6 offset-m6 teal lighten-1" id="server_time">
		<h2><?php echo __('Current Server Time', 'xcloner')?>: <span class="right"><?php echo date("Y/m/d H:i")?></span></h2>
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
			  <h4><?php echo __('Edit Schedule', 'xcloner') ?> #<span id="schedule_id"></span></h4>
			</div>	  
      
			<div class="col s12 m6 right-align">
				<div class="switch">
					<label>
					<?php echo __('Off', 'xcloner') ?>
					<input type="checkbox" id="status" name="status" value="1">
					<span class="lever"></span>
					<?php echo __('On', 'xcloner') ?>
					</label>
				</div>
			</div>	
	  </div>			  

	  <p>	
		  
		<ul class="nav-tab-wrapper content row"> 
			<li> <a href="#scheduler_settings" class="nav-tab col s12 m6 nav-tab-active"><?php echo __('Scheduler Settings', 'xcloner') ?></a></li>
			<li> <a href="#advanced_scheduler_settings" class="nav-tab col s12 m6"><?php echo __('Advanced', 'xcloner') ?></a></li>
		</ul>
		
		<div class="nav-tab-wrapper-content">
			<div id="scheduler_settings" class="tab-content active">
			
				<div class="row">
					<div class="input-field col s12 l12">
						<input placeholder="" name="schedule_name" id="schedule_name" type="text" required value="">
						<label for="schedule_name"><?php echo __('Schedule Name', 'xcloner') ?></label>
					</div>
				</div>
				
				<div class="row">
					<div class="input-field col s12 l6">
						<input placeholder="" name="schedule_start_date" id="schedule_start_date" type="datetime"  value="">
						<label for="schedule_start_date" class="active"><?php echo __('Schedule Start At', 'xcloner') ?>:</label>
					</div>

					<div class="input-field col s12 l6">
						<select name="schedule_frequency" id="schedule_frequency" class="validate" required>
							<option value="" disabled selected><?php echo __('Schedule Recurrence', 'xcloner') ?></option>
							<option value="single"><?php echo __("Don't Repeat","xcloner")?><option>
							<option value="hourly"><?php echo __("Hourly","xcloner")?></option>
							<option value="daily"><?php echo __("Daily","xcloner")?></option>
							<option value="weekly"><?php echo __("Weekly","xcloner")?></option>
							<option value="monthly"><?php echo __("Monthly","xcloner")?></option>
						</select>
					</div>
				</div>
				
				<?php if(sizeof($available_storages)):?>
				<div class="row">
					<div class="input-field col s12 l6">
						<select name="schedule_storage" id="schedule_storage" class="validate" >
							<option value="" selected><?php echo __('none', 'xcloner') ?></option>
							<?php foreach($available_storages as $storage=>$text):?>
								<option value="<?php echo $storage?>"><?php echo $text?></option>
							<?php endforeach?>
						</select>
						<label><?php echo __('Send To Remote Storage ', 'xcloner') ?></label>
					</div>
				</div>	
				<?php endif?>
				
				<div class="row">
					<div class="input-field col s12 l12">
						<input placeholder="" name="email_notification" id="email_notification" type="text" required value="">
						<label for="email_notification"><?php echo __('Email Notification Address', 'xcloner') ?></label>
					</div>
				</div>
				
			</div>
			
			<div id="advanced_scheduler_settings" class="tab-content">
				<div class="row">
					<div class="input-field col s12 l12">
						<input placeholder="" name="backup_name" id="backup_name" type="text" required value="">
						<label for="backup_name"><?php echo __('Backup Name', 'xcloner') ?></label>
					</div>
				</div>
				
				<div class="row">	
					<div class="input-field col s12 l12">
						<textarea id="table_params" name="table_params" class="materialize-textarea" rows="15"></textarea>
						<label for="table_params" class="active"><?php echo __('Included Database Data', 'xcloner') ?></label>
					</div>
				</div>
				
				<div class="row">	
					<div class="input-field col s12 l12">
						<textarea id="excluded_files" name="excluded_files" class="materialize-textarea" rows="15"></textarea>
						<label for="excluded_files" class="active"><?php echo __('Excluded Files', 'xcloner') ?></label>
					</div>
				</div>
			</div>	
		</div>
		
		<div class="row">
			
			<div class="input-field col s12">
				<button class="right btn waves-effect waves-light" type="submit" name="action"><?php echo __('Save', 'xcloner') ?>
					<i class="material-icons right">send</i>
				</button>
			</div>
		</div>
      </p>
    </div>
    </form>
</div>

