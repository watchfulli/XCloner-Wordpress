<?php

$xcloner_settings 		= new Xcloner_Settings();
$logger					= new Xcloner_Logger();
$xcloner_file_system 	= new Xcloner_File_System();
$xcloner_file_transfer 	= new Xcloner_File_Transfer();

$start = 0 ;

$backup_list = $xcloner_file_system->get_latest_backups();

?>

<h1><?= esc_html(get_admin_page_title()); ?></h1>

<script>	
	var xcloner_auth_key = '<?php echo md5(AUTH_KEY)?>';
</script>

<div class="row xcloner-restore">
	<div class="col s12 m10">
		<ul class="collapsible xcloner-restore " data-collapsible="accordion">
			<li data-step="1" class="restore-script-upload-step steps active show">
				<div class="collapsible-header active"><i class="material-icons">settings_remote</i><?php echo __("Restore Script Upload","xcloner")?></div>
				<div class="collapsible-body row">
						
						<ul class="text-steps">
							<li><?php echo __("Please download the restore script from","xcloner")?> <a href='#' onclick="window.location=ajaxurl+'?action=download_restore_script&phar=true'"><strong><?php echo __("here","xcloner")?></strong></a>
							</li>	
							<li>
							<?php echo __("Extract the files on your new host","xcloner")?>
							</li>
							<li>
							<?php echo __("Provide url below to the <u>xcloner_restore.php</u> restore script","xcloner")?>
							</li>
							<li>
							<?php echo __("If your server is not web accessible, like a localhost computer, you can use a DynDNS service or install a blank copy of Wordpress with XCloner in the same environment and start the restore from there.","xcloner")?>
							</li>
							
						</ul>	
						
						<div class="input-field col m9 s12">
							<input value="http://localhost/xcloner/xcloner_restore.php" id="restore_script_url" type="text" class="validate" placeholder="Url to XCloner Restore Script, example http://myddns.com/xcloner/xcloner_restore.php" >
							<label for="restore_script_url"></label>
							<div id="url_validation_status" class="status"></div>
				        </div>
				        <div class="col m3 s12 right-align">
							<button class="btn waves-effect waves-light" type="submit" id="validate_url" name="action"><?php echo __("Check","xcloner")?>
							    <i class="material-icons right">send</i>
							</button>
				        </div>
				</div>
			</li>
			
			<li data-step="2" class="backup-upload-step steps">
				<div class="collapsible-header active"><i class="material-icons">file_upload</i><?php echo __("Upload Local Backup Archive To Remote Host","xcloner")?>
				</div>
				<div class="collapsible-body row">
					<p><?php echo __("You can skip this step if you want to transfer the archive in some other way, make sure you upload it in the same directory as the restore script from the previous step.","xcloner")?></p>
					<div class="input-field col s12 m9">
						<select id="backup_file" name="backup_file" class="browser-default">
					      <option value="" disabled selected><?php echo __("Please select a local backup archive to upload to remote host","xcloner")?></option>
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
					<div class="col s12 m3 right-align">
						<div class="toggler">
							<button class="btn waves-effect waves-light upload-backup normal" type="submit" id="" name="action"><?php echo __("Upload","xcloner")?>
							    <i class="material-icons right">send</i>
							</button>
							<button class="btn waves-effect waves-light red upload-backup cancel" type="submit" id="" name="action"><?php echo __("Cancel","xcloner")?>
							    <i class="material-icons right">close</i>
							</button>
						</div>
						<button class="btn waves-effect waves-light grey" type="submit" id="skip_upload_backup" name="action"><?php echo __("Next","xcloner")?>
						    <i class="material-icons right">navigate_next</i>
						</button>
					</div>
				</div>
			</li>	
			
			<li data-step="3" class="restore-remote-backup-step steps active">
				<div class="collapsible-header"><i class="material-icons">folder_open</i><?php echo __("Restore Files Backup Available On Remote Host","xcloner")?>
						<i class="material-icons right" title="Refresh Remote Backup Files List" id="refresh_remote_backup_file">cached</i>

						<div class="switch right">
							<label>
							<?php echo __('Verbose Output', "xcloner")?>
							<input type="checkbox" id="toggle_file_restore_display" name="toggle_file_restore_display" checked value="1">
							<span class="lever"></span>
							
							</label>
						</div>
				</div>
				<div class="collapsible-body row">
						
						<div class=" col s12 ">
							<div class="input-field row">
								<input type="text" name="remote_restore_url" id="remote_restore_url" class="validate" placeholder="Restore Target Url">
								<label><?php echo __("Remote Restore Target Url","xcloner")?></label>
							</div>
						</div>							
						<div class=" col s12 m9">
							<div class="input-field row">
								<input type="text" name="remote_restore_path" id="remote_restore_path" class="validate" placeholder="Restore Target Path">
								<label><?php echo __("Remote Restore Target Path","xcloner")?></label>
							</div>
							
							<div class="input-field row">
								<select id="remote_backup_file" name="remote_backup_file" class="browser-default">
									<option value="" disabled selected><?php echo __("Please select the remote backup file to restore","xcloner")?></option>
							    </select>
							    <label></label>
							</div>
							
							<div class="progress">
								<div class="indeterminate" style="width: 0%"></div>
							</div>
								
							 <div class="status"></div>
							 <div class="files-list"></div>
				        </div>
				       
				        <div class="col s12 m3 right-align">
							<div class="toggler">
								<button class="btn waves-effect waves-light restore_remote_backup normal " type="submit" id="" name="action"><?php echo __("Restore","xcloner")?>
								    <i class="material-icons right">send</i>
								</button>
								<button class="btn waves-effect waves-light red restore_remote_backup cancel" type="submit" id="" name="action"><?php echo __("Cancel","xcloner")?>
								    <i class="material-icons right">close</i>
								</button>
							</div>
							<button class="btn waves-effect waves-light grey" type="submit" id="skip_remote_backup_step" name="action"><?php echo __("Next","xcloner")?>
								<i class="material-icons right">navigate_next</i>
							</button>
				        </div>
				</div>
			</li>
			
			<li data-step="4" class="restore-remote-database-step steps active">
				<div class="collapsible-header"><i class="material-icons">list</i><?php echo __("Restore Remote Database Backup","xcloner")?>
					<i class="material-icons right" title="Refresh Database Backup Files List" id="refresh_database_file">cached</i>
				</div>
				<div class="collapsible-body row">
												
						<div class=" col s12">
							
							<div class="input-field col s12 m6">
								<input type="text" name="remote_mysql_host" id="remote_mysql_host" class="validate" placeholder="Remote Mysql Hostname" value="localhost">
								<label><?php echo __("Remote Mysql Hostname","xcloner")?></label>
							</div>
							
							<div class="input-field  col s12 m6">
								<input type="text" name="remote_mysql_db" id="remote_mysql_db" class="validate" placeholder="Remote Mysql Database">
								<label><?php echo __("Remote Mysql Database","xcloner")?></label>
							</div>
							
							<div class="input-field  col s12 m6">
								<input type="text" name="remote_mysql_user" id="remote_mysql_user" class="validate" placeholder="Remote Mysql Username">
								<label><?php echo __("Remote Mysql Username","xcloner")?></label>
							</div>
							
							
							<div class="input-field  col s12 m6">
								<input type="text" name="remote_mysql_pass" id="remote_mysql_pass" class="validate" placeholder="Remote Mysql Password">
								<label><?php echo __("Remote Mysql Password","xcloner")?></label>
							</div>
							
						</div>	
						
						<div class=" col s12 m9">
							<div class="input-field row">
								<select id="remote_database_file" name="remote_database_file" class="browser-default">
									<option value="" disabled selected><?php echo __("Please select the remote database backup file to restore","xcloner")?></option>
							    </select>
							    
							    <label></label>
							</div>
							
							<div class="progress">
								<div class="determinate" style="width: 0%"></div>
							</div>

							 <div class="status"></div>
							 <div class="query-box">
								 <h6>Use the field below to fix your mysql query and Retry again the Restore</h6>
								<textarea class="query-list" cols="5"></textarea>
							 </div>
				        </div>
				       
				        <div class="col s12 m3 right-align">
							<div class="toggler">
								<button class="btn waves-effect waves-light restore_remote_mysqldump normal " type="submit" id="" name="action"><?php echo __("Restore","xcloner")?>
								    <i class="material-icons right">send</i>
								</button>
								<button class="btn waves-effect waves-light red restore_remote_mysqldump cancel" type="submit" id="" name="action"><?php echo __("Cancel","xcloner")?>
								    <i class="material-icons right">close</i>
								</button>
							</div>
							
							<button class="btn waves-effect waves-light grey" type="submit" id="skip_restore_remote_database_step" name="action"><?php echo __("Next","xcloner")?>
								<i class="material-icons right">navigate_next</i>
							</button>
							
				        </div>
				 	
				</div>
			</li>
			
			<li data-step="5" class="restore-finish-step steps active">
				<div class="collapsible-header"><i class="material-icons">folder_open</i><?php echo __("Finishing up...","xcloner")?>
				</div>
				<div class="collapsible-body row">
						
						<div class="row">
							<div class="col s4">
								<label><?php echo __("Update wp-config.php mysql details and update the restored site Url","xcloner")?></label>
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
								<label><?php echo __("Delete Restored Backup Temporary Folder","xcloner")?></label>
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
								<label><?php echo __("Delete Restore Script","xcloner")?></label>
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
				        
				        <div class="col s12 center-align">

							<button class="btn waves-effect waves-light teal" type="submit" id="restore_finish" name="action"><?php echo __("Finish","xcloner")?>
								<i class="material-icons right">navigate_next</i>
							</button>
				        </div>
				</div>
			</li>
			
		</ul>
	</div>
</div>



