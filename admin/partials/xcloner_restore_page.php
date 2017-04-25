<?php

$xcloner_settings 		= $this->get_xcloner_container()->get_xcloner_settings();
$logger					= $this->get_xcloner_container()->get_xcloner_logger();
$xcloner_file_system 	= $this->get_xcloner_container()->get_xcloner_filesystem();
$xcloner_file_transfer 	= $this->get_xcloner_container()->get_xcloner_file_transfer();

$start = 0 ;

$backup_list = $xcloner_file_system->get_latest_backups();

?>

<h1><?= esc_html(get_admin_page_title()); ?></h1>

<script>	
	var xcloner_auth_key = '<?php echo md5(AUTH_KEY)?>';
</script>

<div class="row xcloner-restore">
	<div class="col s12">
		<ul class="collapsible xcloner-restore " data-collapsible="accordion">
			<li data-step="1" class="restore-script-upload-step steps active show">
				<div class="collapsible-header active"><i class="material-icons">settings_remote</i><?php echo __("Restore Script Upload",'xcloner-backup-and-restore')?></div>
				<div class="collapsible-body row">
						
						<ul class="text-steps">
							<li><?php echo __("If you want to do a <strong>Local Target System Restore</strong>, leave Url field below empty and click 'Check Connection', you can skip the next steps.",'xcloner-backup-and-restore')?> 
							</li>	
							<li><?php echo __("If you want to do a <strong>Remote Target System Restore</strong>, please download the restore script from",'xcloner-backup-and-restore')?> <a href='#' onclick="window.location=ajaxurl+'?action=download_restore_script&phar=true'"><strong><?php echo __("here",'xcloner-backup-and-restore')?></strong></a>
							</li>	
							<li>
							<?php echo __("Extract the restore script archive files on your new host",'xcloner-backup-and-restore')?>
							</li>
							<li>
							<?php echo __("Provide url below to the <u>xcloner_restore.php</u> restore script, like http://my_restore_site.com/xcloner_restore.php",'xcloner-backup-and-restore')?>
							</li>
							<li>
							<?php echo __("If your server is not web accessible, like a localhost computer, you can use a DynDNS service or install a blank copy of Wordpress with XCloner in the same environment and start the restore from there.",'xcloner-backup-and-restore')?>
							</li>
							<?php if(is_ssl()):?>
							<li class="warning">
								<?php echo __("We have detected your connection to the site as being secure, so your restore script address must start with https://.")?>
							</li>
							<?php endif ?>
							
						</ul>	
						
						<div class="input-field col l9 s12">
							<input value="<?php echo (is_ssl())?"https://":""?>" id="restore_script_url" type="text" class="validate" placeholder="Url to XCloner Restore Script, example http://myddns.com/xcloner/xcloner_restore.php" >
							<label for="restore_script_url"></label>
							<div id="url_validation_status" class="status"></div>
				        </div>
				        <div class="col l3 s12 right-align">
							<button class="btn waves-effect waves-light" type="submit" id="validate_url" name="action"><?php echo __("Check Connection",'xcloner-backup-and-restore')?>
							    <i class="material-icons right">send</i>
							</button>
				        </div>
				</div>
			</li>
			
			<li data-step="2" class="backup-upload-step steps">
				<div class="collapsible-header active"><i class="material-icons">file_upload</i><?php echo __("Upload Local Backup Archive To Target Host",'xcloner-backup-and-restore')?>
				</div>
				<div class="collapsible-body row">
					<p><?php echo __("You can skip this step if you want to transfer the archive in some other way, make sure you upload it in the same directory as the restore script from the previous step.",'xcloner-backup-and-restore')?></p>
					<div class="input-field col s12 l7">
						<select id="backup_file" name="backup_file" class="browser-default">
					      <option value="" disabled selected><?php echo __("Please select a local backup archive to upload to target host",'xcloner-backup-and-restore')?></option>
					      <?php if(is_array($backup_list)):?>
							<?php foreach($backup_list as $file):?>
								<option value="<?php echo $file['basename']?>">
								<?php echo $file['basename']?> (<?php echo size_format($file['size'])?>)
								</option>
							<?php endforeach?>
						<?php endif ?>	
					    </select>
					    
					    <label> </label>
						<div class="progress">
						      <div class="determinate" style="width: 0%"></div>
						</div>
						<div class="status"></div>
					</div>
					<div class="col s12 l5 right-align">
						<div class="toggler">
							<button class="btn waves-effect waves-light upload-backup normal" type="submit" id="" name="action"><?php echo __("Upload",'xcloner-backup-and-restore')?>
							    <i class="material-icons left">navigate_before</i>
							</button>
							<button class="btn waves-effect waves-light red upload-backup cancel" type="submit" id="" name="action"><?php echo __("Cancel",'xcloner-backup-and-restore')?>
							    <i class="material-icons right">close</i>
							</button>
						</div>
						<button class="btn waves-effect waves-light grey" type="submit" title="<?php echo __("Skip Next",'xcloner-backup-and-restore')?>" id="skip_upload_backup" name="action"><?php echo __("Skip Next",'xcloner-backup-and-restore')?>
						    <i class="material-icons right">navigate_next</i>
						</button>
					</div>
				</div>
			</li>	
			
			<li data-step="3" class="restore-remote-backup-step steps active">
				<div class="collapsible-header"><i class="material-icons">folder_open</i><?php echo __("Restore Files Backup Available On Target Location",'xcloner-backup-and-restore')?>
						<i class="material-icons right" title="Refresh Target Backup Files List" id="refresh_remote_backup_file">cached</i>

						<div class="switch right">
							<label>
							<?php echo __('Verbose Output', 'xcloner-backup-and-restore')?>
							<input type="checkbox" id="toggle_file_restore_display" name="toggle_file_restore_display"  class="" checked value="1">
							<span class="lever"></span>
							
							</label>
						</div>
				</div>
				<div class="collapsible-body row">
						
						<div class=" col s12 l7">
							<div class="input-field row">
								<div class="col s12">
									<a class="btn-floating tooltipped btn-small right" data-html="true" data-position="left" data-delay="50" 
										data-tooltip="<?php echo __("This is the directory where you would like to restore the backup archive files.<br /> 
										Please use this with caution when restoring to a live site.",'xcloner-backup-and-restore')?>"><i class="material-icons">help_outline</i>
									</a>	
									<h5><?php echo __("Restore Target Path:",'xcloner-backup-and-restore')?></h5>
									<input type="text" name="remote_restore_path" id="remote_restore_path" class="validate" placeholder="Restore Target Path">
									<label></label>
								</div>
				
							</div>
							
							<div class="input-field row">
								<div class="col s12">
									<a href="#backup_localhost-2017-04-03_10-58-sql-diff2017-03-22_00-00-5b6c4.tgz" 
									class="list-backup-content btn-floating tooltipped btn-small right" data-tooltip="<?php echo __('Click To List The Selected Backup Content', 'xcloner-backup-and-restore') ?>">
										<i class="material-icons">folder_open</i>
									</a>
									<h5><?php echo __("Restore Backup Archive:",'xcloner-backup-and-restore')?></h5>
									<select id="remote_backup_file" name="remote_backup_file" class="browser-default">
										<option value="" disabled selected><?php echo __("Please select the target backup file to restore",'xcloner-backup-and-restore')?></option>
								    </select>
								    <label></label>
							    </div>
							
								<div class="col s12">
									<input class="with-gap" name="filter_files" type="radio" id="filter_files_all" checked value="" disabled />
									<label for="filter_files_all" class="tooltipped" data-position="right" data-tooltip="<?php echo __("Restore all backup files. Available only when doing a Remote Target System Restore", 'xcloner-backup-and-restore')?>"><?php echo __("Restore All Files","xcloner-backup-and-restore")?></label>
									
									<input class="with-gap" name="filter_files" type="radio" id="filter_files_wp_content"  value="/^wp-content\/(.*)/" />
									<label for="filter_files_wp_content" class="tooltipped" data-tooltip="<?php echo __('Restore the files only of the wp-content/ folder', 'xcloner-backup-and-restore')?>">
										<?php echo __("Only wp-content","xcloner-backup-and-restore")?>
									</label>
									
									<input class="with-gap" name="filter_files" type="radio" id="filter_files_plugins"  value="/^wp-content\/plugins(.*)/" />
									<label for="filter_files_plugins" class="tooltipped" data-tooltip="<?php echo __('Restore the files only of the wp-content/plugins/ folder', 'xcloner-backup-and-restore')?>">
										<?php echo __("Only Plugins","xcloner-backup-and-restore")?>
									</label>

									<input class="with-gap" name="filter_files" type="radio" id="filter_files_uploads"  value="/^wp-content\/uploads(.*)/" />
									<label for="filter_files_uploads" class="tooltipped" data-tooltip="<?php echo __('Restore the files only of the wp-content/uploads/ folder only', 'xcloner-backup-and-restore')?>">
										<?php echo __("Only Uploads","xcloner-backup-and-restore")?>
									</label>
									
									<input class="with-gap" name="filter_files" type="radio" id="filter_files_themes"  value="/^wp-content\/themes(.*)/" />
									<label for="filter_files_themes" class="tooltipped" data-tooltip="<?php echo __('Restore the files only of the wp-content/themes/ folder', 'xcloner-backup-and-restore')?>">
										<?php echo __("Only Themes","xcloner-backup-and-restore")?>
									</label>
									
									<input class="with-gap" name="filter_files" type="radio" id="filter_files_database"  value="/^xcloner-(.*)\/(.*)\.sql/"/>
									<label for="filter_files_database" class="tooltipped" data-tooltip="<?php echo __('Restore the database-sql.sql mysql backup from the xcloner-xxxxx/ folder', 'xcloner-backup-and-restore')?>">
										<?php echo __("Only Database Backup","xcloner-backup-and-restore")?>
									</label>
								</div>
							</div>
							
							<div class="progress">
								<div class="indeterminate" style="width: 0%"></div>
							</div>
								
							 <div class="status"></div>
								<ul class="files-list"></ul>
							 </div>
				       
				        <div class="col s12 l5 right-align">
							<div class="toggler">
								<button class="btn waves-effect waves-light restore_remote_backup normal " type="submit" id="" name="action"><?php echo __("Restore",'xcloner-backup-and-restore')?>
								    <i class="material-icons left">navigate_before</i>
								</button>
								<button class="btn waves-effect waves-light red restore_remote_backup cancel" type="submit" id="" name="action"><?php echo __("Cancel",'xcloner-backup-and-restore')?>
								    <i class="material-icons right">close</i>
								</button>
							</div>
							<button class="btn waves-effect waves-light grey" type="submit" title="<?php echo __("Skip Next",'xcloner-backup-and-restore')?>" id="skip_remote_backup_step" name="action"><?php echo __("Skip Next",'xcloner-backup-and-restore')?>
								<i class="material-icons right">navigate_next</i>
							</button>
				        </div>
				</div>
			</li>
			
			<li data-step="4" class="restore-remote-database-step steps active">
				<div class="collapsible-header"><i class="material-icons">list</i><?php echo __("Restore Target Database - Search and Replace",'xcloner-backup-and-restore')?>
					<i class="material-icons right" title="Refresh Database Backup Files List" id="refresh_database_file">cached</i>
				</div>
				<div class="collapsible-body row">
						
						<div id="remote-restore-options">
						<div class="col s12">
							<a class="btn-floating tooltipped btn-small right" data-position="left" data-delay="50" data-html="true" data-tooltip="<?php echo __('Please provide below the mysql connection details for the target host database.<br />For live sites we recommend using a new separate database.','xcloner-backup-and-restore')?>" data-tooltip-id="92c95730-94e9-7b59-bd52-14adc30d5e3e"><i class="material-icons">help_outline</i></a>	
							<h5><?php echo __('Target Mysql Details','xcloner-backup-and-restore')?></h5>
						</div>						
						<div class=" col s12">
							<div class="input-field col s12 m6">
								<input type="text" name="remote_mysql_host" id="remote_mysql_host" class="validate" placeholder="Target Mysql Hostname">
								<label><?php echo __("Target Mysql Hostname",'xcloner-backup-and-restore')?></label>
							</div>
							
							<div class="input-field  col s12 m6">
								<input type="text" name="remote_mysql_db" id="remote_mysql_db" class="validate" placeholder="Target Mysql Database">
								<label><?php echo __("Target Mysql Database",'xcloner-backup-and-restore')?></label>
							</div>
							
							<div class="input-field  col s12 m6">
								<input type="text" name="remote_mysql_user" id="remote_mysql_user" class="validate" placeholder="Target Mysql Username">
								<label><?php echo __("Target Mysql Username",'xcloner-backup-and-restore')?></label>
							</div>
							
							
							<div class="input-field  col s12 m6">
								<input type="text" name="remote_mysql_pass" id="remote_mysql_pass" class="validate" placeholder="Target Mysql Password">
								<label><?php echo __("Target Mysql Password",'xcloner-backup-and-restore')?></label>
							</div>
							
						</div>	
						<div class="col s12">
						<a class="btn-floating tooltipped btn-small right" data-position="left" data-delay="50" data-html="true" data-tooltip="<?php echo __('I will attempt to replace all mysql backup records matching the provided Source Url with the provided Target Url. <br />Leave blank the Target Url if you would like to skip this option. <br />As a bonus, I will also replace all matching serialized data and fix it\'s parsing.','xcloner-backup-and-restore')?>" data-tooltip-id="92c95730-94e9-7b59-bd52-14adc30d5e3e"><i class="material-icons">help_outline</i></a>	
							<h5><?php echo __('Target Mysql Search and Replace','xcloner-backup-and-restore')?></h5>
						</div>
						<div class="col s12">  
							<div class="input-field col s12 m6 ">
									<input type="text" name="wp_home_url" id="wp_home_url" class="validate" placeholder="WP Home Url" value="<?php echo home_url();?>">
									<label><?php echo __("Source Home Url",'xcloner-backup-and-restore')?></label>
							</div>	
							
							<div class="input-field col s12 m6 ">
									<input type="text" name="remote_restore_url" id="remote_restore_url" class="validate" placeholder="Restore Target Url">
									<label><?php echo __("With Target Home Url",'xcloner-backup-and-restore')?></label>
							</div>
						
						<?php if( site_url() != home_url()) : ?>
							<div class="input-field col s12 m6 ">
									<input type="text" name="wp_site_url" id="wp_site_url" class="validate" placeholder="WP Site Url" value="<?php echo site_url();?>">
									<label><?php echo __("Source Site Url",'xcloner-backup-and-restore')?></label>
							</div>	
							
							<div class="input-field col s12 m6 ">
									<input type="text" name="remote_restore_site_url" id="remote_restore_site_url" class="validate" placeholder="Restore Target Url">
									<label><?php echo __("With Target Site Url",'xcloner-backup-and-restore')?></label>
							</div>
						
						<?php endif;?>
						</div>
						
						</div>
						
						<div class=" col s12 l7">
							<div class="input-field row">
								<select id="remote_database_file" name="remote_database_file" class="browser-default">
									<option value="" disabled selected><?php echo __("Please select the target database backup file to restore",'xcloner-backup-and-restore')?></option>
							    </select>
							    
							    <label></label>
							</div>
							
							<div class="progress">
								<div class="determinate" style="width: 0%"></div>
							</div>

							 <div class="status"></div>
							 <div class="query-box">
								 <h6><?php echo __('Use the field below to fix your mysql query and Retry again the Restore, or replace with # to Skip next', 'xcloner-backup-and-restore')?></h6>
								<textarea class="query-list" cols="5"></textarea>
							 </div>
				        </div>
				      
				        <div class="col s12 l5 right-align">
							<div class="toggler">
								<button class="btn waves-effect waves-light restore_remote_mysqldump normal " type="submit" id="" name="action"><?php echo __("Restore",'xcloner-backup-and-restore')?>
								    <i class="material-icons left">navigate_before</i>
								</button>
								<button class="btn waves-effect waves-light red restore_remote_mysqldump cancel" type="submit" id="" name="action"><?php echo __("Cancel",'xcloner-backup-and-restore')?>
								    <i class="material-icons right">close</i>
								</button>
							</div>
							
							<button class="btn waves-effect waves-light grey" type="submit" title="<?php echo __("Skip Next",'xcloner-backup-and-restore')?>" id="skip_restore_remote_database_step" name="action"><?php echo __("Skip Next",'xcloner-backup-and-restore')?>
								<i class="material-icons right">navigate_next</i>
							</button>
							
				        </div>
				 	
				</div>
			</li>
			
			<li data-step="5" class="restore-finish-step steps active">
				<div class="collapsible-header"><i class="material-icons">folder_open</i><?php echo __("Finishing up...",'xcloner-backup-and-restore')?>
				</div>
				<div class="collapsible-body row">
						
						<div class="row">
							<div class="col s4">
								<label><?php echo __("Update wp-config.php mysql details and update the Target Site Url",'xcloner-backup-and-restore')?></label>
							</div>
							
							<div class="col s8">
								<div class="switch">
									<label>
									Off
									<input type="checkbox" id="update_remote_site_url" name="update_remote_site_url" checked value="1">
									<span class="lever"></span>
									On
									</label>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col s4">
								<label><?php echo __("Delete Restored Backup Temporary Folder",'xcloner-backup-and-restore')?></label>
							</div>
							<div class="col s8">
								<div class="switch">
									<label>
									Off
									<input type="checkbox" id="delete_backup_temporary_folder" name="delete_backup_temporary_folder" checked value="1">
									<span class="lever"></span>
									On
									</label>
								</div>
							</div>
						</div>	
						
						<div class="row">
							<div class="col s4">
								<label><?php echo __("Delete Restore Script",'xcloner-backup-and-restore')?></label>
							</div>
							<div class="col s8">
								<div class="switch">
									<label>
									Off
									<input type="checkbox" id="delete_restore_script" name="delete_restore_script" checked value="1">
									<span class="lever"></span>
									On
									</label>
								</div>
							</div>
						</div>
												
						<div class=" row col s12">
							 <div class="status"></div>
				        </div>
				        
						<div class=" row col s12 center-align" id="xcloner_restore_finish">
							<h5><?php echo __("Thank you for using XCloner.",'xcloner-backup-and-restore')?></h5>
							<h6><?php echo sprintf(__("We would love to hear about your experience in the %s.", 'xcloner-backup-and-restore'),'<a href="https://wordpress.org/support/plugin/xcloner-backup-and-restore" target="_blank">Wordpress XCloner forums</a>') ?></h6>
							<a class="twitter-follow-button" href="https://twitter.com/thinkovi" data-show-count="false">Follow @thinkovi</a>
							<script src="//platform.twitter.com/widgets.js" async="" charset="utf-8"></script>
				        </div>
				        
				        <div class="col s12 center-align">
							<div class="row">
								<div class="col s6 right-align">
								<button class="btn waves-effect waves-light teal" type="submit" id="restore_finish" name="action"><?php echo __("Finish",'xcloner-backup-and-restore')?>
									<i class="material-icons right">navigate_next</i>
								</button>
								</div>
									
								<div id="open_target_site" class="col s6 left-align">
									<a disabled="disabled" href="#" class="btn waves-effect waves-light teal" type="button" target="_blank"><?php echo __("Open Target Site",'xcloner-backup-and-restore')?>
										<i class="material-icons right">navigate_next</i>
									</a>
								</div>
							</div>
				        </div>
				</div>
			</li>
			
		</ul>
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
