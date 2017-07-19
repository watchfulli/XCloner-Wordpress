<?php 
$xcloner_settings = $this->get_xcloner_container()->get_xcloner_settings();
$xcloner_remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();
$available_storages = $xcloner_remote_storage->get_available_storages();
$xcloner_scheduler = $this->get_xcloner_container()->get_xcloner_scheduler();
$tab = 1;
?>

<script>var xcloner_backup = new Xcloner_Backup();</script>

<h1><?= esc_html(get_admin_page_title()); ?></h1>
         
<ul class="nav-tab-wrapper content row">
	<li><a href="#backup_options" class="nav-tab col s12 m3 l2 nav-tab-active"><?php echo $tab.". ".__('Backup Options','xcloner-backup-and-restore')?></a></li>
	<?php if($xcloner_settings->get_enable_mysql_backup()):?>
		<li><a href="#database_options" class="nav-tab col s12 m3 l2 "><?php echo ++$tab.". ".__('Database Options','xcloner-backup-and-restore')?></a></li>
	<?php endif?>
	<li><a href="#files_options" class="nav-tab col s12 m3 l2 "><?php echo ++$tab.". ".__('Files Options','xcloner-backup-and-restore')?></a></li>
	<li><a href="#generate_backup" class="nav-tab col s12 m3 l2 "><?php echo ++$tab.". ".__('Generate Backup','xcloner-backup-and-restore')?></a></li>
	<li><a href="#schedule_backup" class="nav-tab col s12 m3 l2 "><?php echo ++$tab.". ".__('Schedule Backup','xcloner-backup-and-restore')?></a></li>
</ul>

<form action="" method="POST" id="generate_backup_form">
	<div class="nav-tab-wrapper-content">
		<!-- Backup Options Content Tab-->
		<div id="backup_options" class="tab-content active">
			<div class="row">
		        <div class="input-field inline col s12 m10 l6">
					<i class="material-icons prefix">input</i>
					<input name="backup_name" id="backup_name" type="text" value=<?php echo $xcloner_settings->get_default_backup_name() ?> >
					<label for="backup_name"><?php echo __('Backup Name','xcloner-backup-and-restore')?></label>
				</div>
				<div class="hide-on-small-only m2">
					<a class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-tooltip="<?php echo __('The default backup name, supported tags [time], [hostname], [domain]','xcloner-backup-and-restore')?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
				</div>
		     </div>
		     
		     <div class="row">
		        <div class="input-field inline col s12 m10 l6">
					<i class="material-icons prefix">email</i>
					<input name="email_notification" id="email_notification" type="text" value="<?php echo get_option('admin_email');?>" >
					<label for="email_notification"><?php echo __('Send Email Notification To','xcloner-backup-and-restore')?></label>
				</div>
				<div class="hide-on-small-only m2">
					<a class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-tooltip="<?php echo __('If left blank, no notification will be sent','xcloner-backup-and-restore')?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
				</div>
		     </div>
		     
		     <div class="row">
				<div class="input-field inline col s10 m10 l6">
					<i class="material-icons prefix">access_time</i>
					<input type="datetime-local" id="diff_start_date" class="datepicker_max_today" name="diff_start_date" >
					<label for="diff_start_date"><?php echo __('Backup Only Files Modified/Created After','xcloner-backup-and-restore')?></label>
				</div>
				<div class="hide-on-small-only m2">	
					<a class="btn-floating tooltipped btn-small" data-html="true" data-position="center" data-delay="50" data-tooltip="<?php echo __("This option allows you to create a differential backup that will include only <br> changed files since the set date, leave blank to include all files", "xcloner-backup-and-restore")?>"><i class="material-icons">help_outline</i></a>
				</div>
			</div>
		     
		     <div class="row">
				<div class="input-field col s12 m10 l6">
					<i class="material-icons prefix">input</i>
					<textarea name="backup_comments" id="backup_comments" class="materialize-textarea"></textarea>
					<label for="backup_comments"><?php echo __('Backup Comments','xcloner-backup-and-restore')?></label>
				</div>
				<div class="hide-on-small-only m2">
					<a class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-tooltip="<?php echo __('Some default backup comments that will be stored inside the backup archive','xcloner-backup-and-restore')?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
				</div>
		     </div>
		     
		     <div class="row">
				<div class="input-field col s12 m10 l6 right-align">
					<a class="waves-effect waves-light btn" onclick="next_tab('#database_options');"><i class="material-icons right">skip_next</i>Next</a>
				</div>
			 </div>
		</div>
		
		<?php if($xcloner_settings->get_enable_mysql_backup()):?>
		<div id="database_options" class="tab-content">
			<h2><?php echo __('Select database data to include in the backup', 'xcloner-backup-and-restore')?>:
				<a class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-tooltip="<?php echo __('Enable the \'Backup only WP tables\' setting if you don\'t want to show all other databases and tables not related to this Wordpress install','xcloner-backup-and-restore');?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
			</h2>
			
			<!-- database/tables tree -->
			<div class="row">
				<div class="col s12 l6">
					<div id="jstree_database_container"></div>
				</div>
			</div>
		    
		    <div class="row">
				<div class="input-field col s12 m10 l6 right-align">
					<a class="waves-effect waves-light btn" onclick="next_tab('#files_options');"><i class="material-icons right">skip_next</i>Next</a>
				</div>
			</div>
			
		</div>
		<?php endif ?>
		
		<div id="files_options" class="tab-content">
			<h2><?php echo __('Select from below the files/folders you want to exclude from your Backup Archive','xcloner-backup-and-restore')?>:
				<a class="btn-floating tooltipped btn-small" data-position="bottom" data-delay="50" data-html="true" data-tooltip="<?php echo __('You can navigate below through all your site structure(Backup Start Location) to exclude any file/folder you need by clicking the checkbox near it. <br />If the checkobx is disabled, then it matches a Regex Exclude File option and it can\'t be unchecked','xcloner-backup-and-restore');?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
			</h2>
			
			<!-- Files System Container -->
			<div class="row">
				<div class="col s12 l6">
					<div id="jstree_files_container"></div>
				</div>
			</div>
			
			<div class="row">
				<div class="input-field col s12 m10 l6 right-align">
					<a class="waves-effect waves-light btn" onclick="next_tab('#generate_backup');"><i class="material-icons right">skip_next</i>Next</a>
				</div>
			</div>
			
		</div>
		<div id="generate_backup" class="tab-content">
			<div class="row ">
				 <div class="col s12 l10 center action-buttons">
					<a class="waves-effect waves-light btn-large teal darken-1 start" onclick="xcloner_backup.start_backup()">Start Backup<i class="material-icons left">forward</i></a>
					<a class="waves-effect waves-light btn-large teal darken-1 restart" onclick="xcloner_backup.restart_backup()">Restart Backup<i class="material-icons left">cached</i></a>
					<a class="waves-effect waves-light btn-large red darken-1 cancel" onclick="xcloner_backup.cancel_backup()">Cancel Backup<i class="material-icons left">cancel</i></a>
				</div>
				<div class="col l10 s12">
					<ul class="backup-status collapsible" data-collapsible="accordion">
					    <li class="file-system">
						      <div class="collapsible-header">
									<i class="material-icons">folder</i><?php echo __('Scanning The File System...','xcloner-backup-and-restore')?>
									
									<p class="right"><?php echo sprintf(__('Found %s files (%s)', 'xcloner-backup-and-restore'), '<span class="file-counter">0</span>', '<span  class="file-size-total">0</span>MB')?></p>

									<div>
										<p class="right"><span class="last-logged-file"></span></p>
									</div>	
									
									<div class="progress">
										<div class="indeterminate"></div>
									</div>
								</div>	
						      <div class="collapsible-body status-body"></div>
					    </li>
					    <?php if($xcloner_settings->get_enable_mysql_backup()):?>
					    <li class="database-backup">
						      <div class="collapsible-header">
									<i class="material-icons">storage</i><?php echo __('Generating the Mysql Backup...','xcloner-backup-and-restore')?>
									
									<p class="right"><?php echo sprintf(__('Found %s tables in %s databases (%s)', 'xcloner-backup-and-restore'), '<span class="table-counter">0</span>', '<span class="database-counter">0</span>', '<span data-processed="0" class="total-records">0</span> records','xcloner-backup-and-restore')?></p>
									
									<div>
										<p class="right"><span class="last-logged-table"></span></p>
									</div>	
									
									<div class="progress">
										<div class="determinate" style="width:0%"></div>
									</div>
								</div>	
						      <div class="collapsible-body status-body">
									<div class="row">
										<div class="col l7 s12">
											<ul class="logged-tables"></ul>
										</div>
										<div class="col l5 s12">
											<ul class="logged-databases right"></ul>
										</div>
									</div>
							</div>
					    </li>
					    <?php endif?>
					    <li class="files-backup">
						      <div class="collapsible-header">
									<i class="material-icons">archive</i><?php echo __('Adding Files to Archive...','xcloner-backup-and-restore')?>
									
									<p class="right"><?php echo sprintf(__('Adding %s files (%s)','xcloner-backup-and-restore'), '<span class="file-counter">0</span>', '<span  data-processed="0" class="file-size-total">0</span>MB')?></p>

									<div>
										<p class="right"><span class="last-logged-file"></span></p>
									</div>	
									
									<div class="progress">
										<div class="determinate" style="width:0%"></div>
									</div>
								</div>	
						      <div class="collapsible-body status-body">
									<div class="row">
										<div class="col l3 s12">
											<h2><?php echo __("Backup Parts",'xcloner-backup-and-restore')?>: </h2>
										</div>	
										<div class="col l9 s12">
											<ul class="backup-name"></ul>
										</div>	
									</div>
							  </div>
					    </li>
					    <li class="backup-done">
						      <div class="collapsible-header">
									<i class="material-icons">done</i><?php echo __('Backup Done','xcloner-backup-and-restore')?>
									
									<p class="right">
										 <?php if(sizeof($available_storages)):?>
											<a href="#" class="cloud-upload" title="<?php echo __("Send Backup To Remote Storage",'xcloner-backup-and-restore')?>"><i class="material-icons">cloud_upload</i></a>
										 <?php endif?>
										 <a href="#" class="download" title="<?php echo __("Download Backup",'xcloner-backup-and-restore')?>"><i class="material-icons">file_download</i></a>
										 <a href="#" class="list-backup-content" title="<?php echo __("List Backup Content",'xcloner-backup-and-restore')?>"><i class="material-icons">folder_open</i></a>
									</p>
									
									<div class="progress">
										<div class="determinate" style="width:100%"></div>
									</div>
									
								</div>	
						      <div class="collapsible-body center-align">
									<div class="row">
										<h5><?php echo __("Thank you for using XCloner.",'xcloner-backup-and-restore')?></h5>
										<h6><?php echo sprintf(__("We would love to hear about your experience in the %s.", 'xcloner-backup-and-restore'),'<a href="https://wordpress.org/support/plugin/xcloner-backup-and-restore/reviews/" target="_blank">Wordpress XCloner Reviews Section</a>') ?></h6>
										<a class="twitter-follow-button" href="https://twitter.com/thinkovi" data-show-count="false">Follow @thinkovi</a>
										<script src="//platform.twitter.com/widgets.js" async="" charset="utf-8"></script>
										
										<br />
										<!-- XCLONER SPONSORS AREA-->
										<!-- END XCLONER SPONSORS AREA-->
									</div>
							  </div>
					    </li>
				  </ul>
				 </div> 
				
			</div>
		</div>
		
		<div id="schedule_backup" class="tab-content">
			
			<div class="row">
				<div id="schedule_backup_success" class="col s12 l6 updated settings-error notice is-dismissible"> 
					<p><strong><?php echo __('Schedule Saved', 'xcloner-backup-and-restore')?></strong></p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php echo __('(Dismiss this notice.','xcloner-backup-and-restore')?></span></button>
				</div>
			</div>
			
			<div class="row">
				 <div class="input-field inline col s12 l7">
					  <input type="text" id="schedule_name" class="" name="schedule_name" required>
					  <label for="schedule_name"><?php echo __('Schedule Name', 'xcloner-backup-and-restore') ?></label>
				</div>
			</div>
			
			<div class="row">
				 <div class="input-field inline col s12 m8 l4">
					  <input type="datetime-local" id="datepicker" class="datepicker" name="schedule_start_date" >
					  <label for="datepicker"><?php echo __('Schedule Backup To Start On:','xcloner-backup-and-restore')?></label>
				</div>
				 <div class="input-field inline col s12 m4 l3">
					  <input id="timepicker_ampm_dark" class="timepicker" type="time" name="schedule_start_time">
					  <label for="timepicker_ampm_dark"><?php echo __('At:','xcloner-backup-and-restore')?></label>
				</div>
			</div>
			
			<!--
			<div class="row">
				<div class="input-field inline col s10 m11 l7">
					<select id="backup_type" class="" name="backup_type">
						<option value=""><?php echo __("Full Backup","xcloner-backup-and-restore");?></option>
						<option value="diff"><?php echo __("Differential Backups","xcloner-backup-and-restore");?></option>
						<option value="full_diff"><?php echo __("Full Backup + Differential Backups","xcloner-backup-and-restore");?></option>
					</select>
					<label for="backup_type"><?php echo __('Scheduled Backup Type','xcloner-backup-and-restore')?></label>
				</div>
				<div class="col s2 m1">	
					<a class="btn-floating tooltipped btn-small" data-html="true" data-position="center" data-delay="50" data-tooltip="<ul style='max-width:760px; text-align:left;'>
						<li><?php echo __("Full Backup = it will generate a full backup of all included files each time schedule runs","xcloner-backup-and-restore");?></li>
						<li><?php echo __("Differentials Backups = backups will include only changed files since the schedule started to run","xcloner-backup-and-restore");?></li>
						<li><?php echo __("Full Backup +  Differential Backups = the first time schedule runs, it will create a full backup and all next scheduled backups will include only files created/modified since that last full backup; a full backup is recreated when the number of changed files is bigger than the 'Differetial Backups Max Days' XCloner option.","xcloner-backup-and-restore");?></li>
					</ul>"><i class="material-icons">help_outline</i></a>
				</div>
			</div>	
			-->
			<div class="row">
				<div class="input-field col s12 l7">
					<select name="schedule_frequency" id="schedule_frequency" class="validate" required>
						<option value="" disabled selected><?php echo __('please select', 'xcloner-backup-and-restore') ?></option>
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
					<label><?php echo __('Schedule Frequency', 'xcloner-backup-and-restore') ?></label>
				</div>
			</div>	
			
			<?php if(sizeof($available_storages)):?>
			<div class="row">
				<div class="input-field col s12 m12 l7">
					<select name="schedule_storage" id="schedule_storage" class="validate">
						<option value="" selected><?php echo __('none', 'xcloner-backup-and-restore') ?></option>
						<?php foreach($available_storages as $storage=>$text):?>
							<option value="<?php echo $storage?>"><?php echo $text?></option>
						<?php endforeach?>
						</select>
						<label><?php echo __('Send To Remote Storage', 'xcloner-backup-and-restore') ?></label>
				</div>
			</div>
			<?php endif?>
			<div class="row">
				<div class="col s12 l7">
					<button class="right btn waves-effect waves-light submit_schedule" type="submit" name="action"><?php echo __("Submit" ,'xcloner-backup-and-restore')?>
						<i class="material-icons right">send</i>
					</button>
				</div>
			</div>
		</div>	
	</div>
</form>

<!-- Error Modal Structure -->
<div id="error_modal" class="modal">
	<a title="Online Help" href="https://wordpress.org/support/plugin/xcloner-backup-and-restore" target="_blank"><i class="material-icons medium right">help</i></a>
	<div class="modal-content">
		<h4 class="title_line"><span class="title"></span></h4>
		<!--<h5 class="title_line"><?php echo __('Message')?>: <span class="msg.old"></span></h5>-->
		<h5><?php echo __('Response Code', 'xcloner-backup-and-restore')?>: <span class="status"></span></h5>
		<textarea  class="body" rows="5"></textarea>
	</div>
	<div class="modal-footer">
		<a class=" modal-action modal-close waves-effect waves-green btn-flat  red darken-2"><?php echo __('Close','xcloner-backup-and-restore')?></a>
	</div>
</div>

<!-- List Backup Content Modal-->
<div id="backup_cotent_modal" class="modal">
	<div class="modal-content">
		<h4><?php echo sprintf(__("Listing Backup Content ",'xcloner-backup-and-restore'), "")?></h4>
		<h5 class="backup-name"></h5>
		
		<div class="progress">
			<div class="indeterminate"></div>
		</div>
		<ul class="files-list"></ul>
	</div>	
</div>

<!-- Remote Storage Modal Structure -->
<div id="remote_storage_modal" class="modal">
	<form method="POST" class="remote-storage-form">
	<input type="hidden" name="file" class="backup_name">	  
	<div class="modal-content">
	  <h4><?php echo __("Remote Storage Transfer",'xcloner-backup-and-restore')?></h4>
	  <p>
	  <?php if(sizeof($available_storages)):?>
			<div class="row">
				<div class="col s12 label">
					<label><?php echo __(sprintf('Send %s to remote storage', "<span class='backup_name'></span>"), 'xcloner-backup-and-restore') ?></label>
				</div>
				<div class="input-field col s8 m10">
					<select name="transfer_storage" id="transfer_storage" class="validate" required >
						<option value="" selected><?php echo __('please select...', 'xcloner-backup-and-restore') ?></option>
						<?php foreach($available_storages as $storage=>$text):?>
							<option value="<?php echo $storage?>"><?php echo $text?></option>
						<?php endforeach?>
						</select>
						
				</div>
				<div class="s4 m2 right">
					<button type="submit" class="upload-submit btn-floating btn-large waves-effect waves-light teal"><i class="material-icons">file_upload</i></submit>
				</div>
			</div>
			<div class="row status">
				<?php echo __("Uploading backup to the selected remote storage...",'xcloner-backup-and-restore')?> <span class="status-text"></span>
				<div class="progress">
					<div class="indeterminate"></div>
				</div>
			</div>
		<?php endif?>
		</p>
	</div>
	</form>	
</div>
  
<script>
jQuery(function () { 
	
	jQuery('.col select').material_select();
	jQuery("select[required]").css({display: "block", height: 0, padding: 0, width: 0, position: 'absolute'});
	jQuery(".backup-done .cloud-upload").on("click", function(e){
		var xcloner_manage_backups = new Xcloner_Manage_Backups();
		var hash = jQuery(this).attr('href');
		var id = hash.substr(1)
		
		e.preventDefault();				
		xcloner_manage_backups.cloud_upload(id)
	})
	
	jQuery("#generate_backup_form").on("submit", function(){
	
		xcloner_backup.params = xcloner_backup.get_form_params();
		var data = JSON.stringify(xcloner_backup.params);
		
		xcloner_backup.set_cancel(false);
		
		xcloner_backup.do_ajax(data, "save_schedule")
		return false;
		})
	
	jQuery(".backup-done .download").on("click", function(e){
		var xcloner_manage_backups = new Xcloner_Manage_Backups();
		var hash = jQuery(this).attr('href');
		var id = hash.substr(1)
		
		e.preventDefault();				
		xcloner_manage_backups.download_backup_by_name(id)
	})
	
	jQuery(".backup-done .list-backup-content").on("click", function(e){
		var xcloner_manage_backups = new Xcloner_Manage_Backups();
		var hash = jQuery(this).attr('href');
		var id = hash.substr(1)
		
		e.preventDefault();				
		xcloner_manage_backups.list_backup_content(id)
	})
	
	jQuery('.timepicker').pickatime({
	    default: 'now',
	    min: [7,30],
	    twelvehour: false, // change to 12 hour AM/PM clock from 24 hour
	    donetext: 'OK',
		autoclose: false,
		vibrate: true // vibrate the device when dragging clock hand
	});

	var date_picker = jQuery('.datepicker').pickadate({
		format: 'd mmmm yyyy',
		selectMonths: true, // Creates a dropdown to control month
		selectYears: 15, // Creates a dropdown of 15 years to control year
		min: +0.1,
		onSet: function() {
			//this.close();
		}
	});
	
	var date_picker_allowed = jQuery('.datepicker_max_today').pickadate({
		format: 'd mmmm yyyy',
		selectMonths: true, // Creates a dropdown to control month
		selectYears: 15, // Creates a dropdown of 15 years to control year
		max: +0.1,
		onSet: function() {
			//this.close();
		}
	});
	
	<?php if($xcloner_settings->get_enable_mysql_backup()):?>
	jQuery('#jstree_database_container').jstree({
			'core' : {
				'check_callback' : true,
				'data' : {
					'method': 'POST',
					'dataType': 'json',
					'url' : ajaxurl,
					'data' : function (node) {
								var data = { 
									'action': 'get_database_tables_action',
									'id' : node.id
									}
								return data;
							}
				},		
					
			'error' : function (err) { 
				//alert("We have encountered a communication error with the server, please review the javascript console."); 
				var json = jQuery.parseJSON( err.data )
				show_ajax_error("Error Loading Database Structure ", err.reason, json.xhr);
				},
			 
			'strings' : { 'Loading ...' : 'Loading the database structure...' },			
			'themes' : {
					"variant" : "default"
				},
			},
			'checkbox': {
				  three_state: true
			},
			'plugins' : [
					"checkbox",
					"massload",
					"search",
					//"sort",
					//"state",
					"types",
					"unique",
					"wholerow"
				]
		});
	<?php endif ?>
		
	jQuery('#jstree_files_container').jstree({
			'core' : {
				'check_callback' : true,
				'data' : {
					'method': 'POST',
					'dataType': 'json',
					'url' : ajaxurl,
					'data' : function (node) {
								var data = { 
									'action': 'get_file_system_action',
									'id' : node.id
									}
								return data;
							}
				},		
					
			'error' : function (err) {
				//alert("We have encountered a communication error with the server, please review the javascript console."); 
				var json = jQuery.parseJSON( err.data )
				show_ajax_error("Error Loading Files Structure ", err.reason, json.xhr);
				},
			 
			'strings' : { 'Loading ...' : 'Loading the database structure...' },			
			'themes' : {
					"variant" : "default"
				},
			},
			'checkbox': {
				  three_state: true
			},
			'plugins' : [
					"checkbox",
					"massload",
					"search",
					//"sort",
					//"state",
					"types",
					"unique",
					"wholerow"
				]
		});
});


</script>
