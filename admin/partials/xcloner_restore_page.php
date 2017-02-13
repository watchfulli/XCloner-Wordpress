<?php

$xcloner_settings 		= new Xcloner_Settings();
$logger					= new Xcloner_Logger();
$xcloner_file_system 	= new Xcloner_File_System();
$xcloner_file_transfer 	= new Xcloner_File_Transfer();

$start = 0 ;

$backup_list = $xcloner_file_system->get_latest_backups();

?>

<h1><?= esc_html(get_admin_page_title()); ?></h1>

<div class="row xcloner-restore">
	<div class="col s12 m10">
		<ul class="collapsible xcloner-restore " data-collapsible="accordion">
			<li data-step="1" class="restore-script-upload-step steps active show">
				<div class="collapsible-header active"><i class="material-icons">settings_remote</i>Restore Script Upload</div>
				<div class="collapsible-body row">
						
						Please upload this script to your new host and provide the url below to the xcloner_restore.php file
						
						<div class="input-field col m9 s12">
							<input value="http://localhost/xcloner/xcloner_restore.php" id="restore_script_url" type="text" class="validate" placeholder="Url to XCloner Restore Script, example http://myddns.com/xcloner/xcloner_restore.php" >
							<label for="restore_script_url"></label>
							<div id="url_validation_status" class="status"></div>
				        </div>
				        <div class="col m3 s12 right-align">
							<button class="btn waves-effect waves-light" type="submit" id="validate_url" name="action">Check
							    <i class="material-icons right">send</i>
							</button>
				        </div>
				</div>
			</li>
			
			<li data-step="2" class="backup-upload-step steps">
				<div class="collapsible-header active"><i class="material-icons">file_upload</i>Local Backup Archive Upload</div>
				<div class="collapsible-body row">
					
					<div class="input-field col s12 m7">
						<select id="backup_file" name="backup_file" class="browser-default">
					      <option value="" disabled selected>Please select a local backup archive to upload to remote host</option>
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
					<div class="col s12 m5 right-align">
						<div class="toggler">
							<button class="btn waves-effect waves-light upload-backup normal" type="submit" id="" name="action">Upload
							    <i class="material-icons right">send</i>
							</button>
							<button class="btn waves-effect waves-light red upload-backup cancel" type="submit" id="" name="action">Cancel
							    <i class="material-icons right">close</i>
							</button>
						</div>
						<button class="btn waves-effect waves-light teal" type="submit" id="skip_upload_backup" name="action">Next
						    <i class="material-icons right">navigate_next</i>
						</button>
					</div>
				</div>
			</li>	
			
			<li data-step="3" class="restore-remote-backup-step steps active">
				<div class="collapsible-header"><i class="material-icons">folder_open</i>Restore Backup On Remote Host</div>
				<div class="collapsible-body row">
												
						<div class=" col s12 m9">
							<div class="input-field row">
								<input type="text" name="remote_restore_path" id="remote_restore_path" class="validate" placeholder="Remote Restore Path">
								<label>Remote Restore Path</label>
							</div>
							
							<div class="input-field row">
								<select id="remote_backup_file" name="remote_backup_file" class="browser-default">
									<option value="" disabled selected>Please select the remote backup file to restore</option>
							    </select>
							    <label></label>
							</div>
							
							<!--<div class="progress">
								<div class="determinate" style="width: 0%"></div>
							</div>
								-->
							 <div class="status"></div>
				        </div>
				       
				        <div class="col s12 m3 right-align">
							<div class="toggler">
								<button class="btn waves-effect waves-light restore_remote_backup normal " type="submit" id="" name="action">Restore
								    <i class="material-icons right">send</i>
								</button>
								<button class="btn waves-effect waves-light red restore_remote_backup cancel" type="submit" id="" name="action">Cancel
								    <i class="material-icons right">close</i>
								</button>
							</div>
				        </div>
				</div>
			</li>
			
		</ul>
	</div>
</div>



