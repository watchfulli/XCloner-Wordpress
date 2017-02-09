<?php

$xcloner_settings 		= new Xcloner_Settings();
$logger					= new Xcloner_Logger();
$xcloner_file_system 	= new Xcloner_File_System();


$xcloner_file_transfer = new Xcloner_File_Transfer();

$xcloner_file_transfer->set_target("http://thinkovi.com/xcloner/xcloner_restore.php");
//$xcloner_file_transfer->set_target("http://localhost/xcloner/xcloner_restore.php");

$start = 0 ;
//while( $start = $xcloner_file_transfer->transfer_file("backup_localhost-2017-02-07_13-29-sql-ac9b0.tgz", $start))
{
	//echo $start."--";
}

$backup_list = $xcloner_file_system->get_latest_backups();

?>

<h1><?= esc_html(get_admin_page_title()); ?></h1>

<div class="row xcloner-restore">
	<div class="col s12 m10">
		<ul class="collapsible xcloner-restore " data-collapsible="accordion">
			<li class="restore-script-upload-step steps active show">
				<div class="collapsible-header active"><i class="material-icons">file_upload</i>Restore Script Upload</div>
				<div class="collapsible-body row">
						
						Please upload this script to your new host and provide the url below to the xcloner_restore.php file
						
						<div class="input-field col s9">
							<input value="http://localhost/xcloner/xcloner_restore.php" id="restore_script_url" type="text" class="validate" placeholder="Url to XCloner Restore Script, example http://myddns.com/xcloner/xcloner_restore.php" >
							<label for="restore_script_url"></label>
							<div id="url_validation_status"></div>
				        </div>
				        <div class="col s3">
							<button class="btn waves-effect waves-light" type="submit" id="validate_url" name="action">Check
							    <i class="material-icons right">send</i>
							</button>
				        </div>
				</div>
			</li>
			
			<li class="backup-upload-step steps">
				<div class="collapsible-header active"><i class="material-icons">file_upload</i>Select Backup Archive To Restore</div>
				<div class="collapsible-body row">
					
					<div class="input-field col s9">
						<select name="backup_name" class="browser-default">
					      <option value="" disabled selected>Please select backup archive to upload</option>
					      <?php if(is_array($backup_list)):?>
							<?php foreach($backup_list as $file):?>
								<option value="<?php echo $file['basename']?>">
								<?php echo $file['basename']?> (<?php echo size_format($file['size'])?>)
								</option>
							<?php endforeach?>
						<?php endif ?>	
					    </select>
						<div id="status"></div>
					</div>
					<div class="col s3">
						<button class="btn waves-effect waves-light" type="submit" id="upload_backup" name="action">Upload
						    <i class="material-icons right">send</i>
						</button>
					</div>
				</div>
			</li>	
		</ul>
	</div>
</div>



