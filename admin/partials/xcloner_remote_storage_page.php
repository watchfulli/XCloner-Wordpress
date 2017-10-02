<?php 
$remote_storage = $this->get_xcloner_container()->get_xcloner_remote_storage();

$gdrive_auth_url = "";

if(method_exists($remote_storage, "get_gdrive_auth_url"))
	$gdrive_auth_url = $remote_storage->get_gdrive_auth_url();

$gdrive_construct = $remote_storage->gdrive_construct();
?>
<h1><?= esc_html(get_admin_page_title()); ?></h1>

<form class="remote-storage-form" method="POST">

<input type="hidden" id="connection_check" name="connection_check" value="">

<div class="row remote-storage">
	<div class="col s12 m12 l10">
		<ul class="collapsible popout" data-collapsible="accordion">
			<!-- FTP STORAGE-->
			<li id="ftp">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("FTP Storage",'xcloner-backup-and-restore')?>
					<div class="switch right">
						<label>
						Off
						<input type="checkbox" name="xcloner_ftp_enable" class="status" value="1" <?php if(get_option("xcloner_ftp_enable")) echo "checked"?> \>
						<span class="lever"></span>
						On
						</label>
					</div>
				</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_host"><?php echo __("Ftp Hostname",'xcloner-backup-and-restore')?></label>
						</div>
						<div class="col s12 m6">
							<input placeholder="<?php echo __("Ftp Hostname",'xcloner-backup-and-restore')?>" id="ftp_host" type="text" name="xcloner_ftp_hostname" class="validate" value="<?php echo get_option("xcloner_ftp_hostname")?>">
				        </div>
				        <div class=" col s12 m2">
							<input placeholder="<?php echo __("Ftp Port",'xcloner-backup-and-restore')?>" id="ftp_port" type="text" name="xcloner_ftp_port" class="validate" value="<?php echo get_option("xcloner_ftp_port", 21)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_username"><?php echo __("Ftp Username",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Ftp Username",'xcloner-backup-and-restore')?>" id="ftp_username" type="text" name="xcloner_ftp_username" class="validate" value="<?php echo get_option("xcloner_ftp_username")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_password"><?php echo __("Ftp Password",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Ftp Password",'xcloner-backup-and-restore')?>" id="ftp_password" type="password" name="xcloner_ftp_password" class="validate" value="<?php echo get_option("xcloner_ftp_password")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_root"><?php echo __("Ftp Storage Folder",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Ftp Storage Folder",'xcloner-backup-and-restore')?>" id="ftp_root" type="text" name="xcloner_ftp_path" class="validate" value="<?php echo get_option("xcloner_ftp_path")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_root"><?php echo __("Ftp Transfer Mode",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6 input-field inline">
							<input name="xcloner_ftp_transfer_mode" type="radio" id="passive" value="1" <?php if(get_option("xcloner_ftp_transfer_mode", 1)) echo "checked"?> />
							<label for="passive"><?php echo __("Passive",'xcloner-backup-and-restore')?></label>

							<input name="xcloner_ftp_transfer_mode" type="radio" id="active" value="0" <?php if(!get_option("xcloner_ftp_transfer_mode", 1)) echo "checked"?> />
							<label for="active"><?php echo __("Active",'xcloner-backup-and-restore')?></label>
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_ssl_mode"><?php echo __("Ftp Secure Connection",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6 input-field inline">
							<input name="xcloner_ftp_ssl_mode" type="radio" id="ftp_ssl_mode_inactive" value="0" <?php if(!get_option("xcloner_ftp_ssl_mode")) echo "checked"?> />
							<label for="ftp_ssl_mode_inactive"><?php echo __("Disable",'xcloner-backup-and-restore')?></label>

							<input name="xcloner_ftp_ssl_mode" type="radio" id="ftp_ssl_mode_active" value="1" <?php if(get_option("xcloner_ftp_ssl_mode")) echo "checked"?> />
							<label for="ftp_ssl_mode_active"><?php echo __("Enable",'xcloner-backup-and-restore')?></label>
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_timeout"><?php echo __("Ftp Timeout",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m2">
							<input placeholder="<?php echo __("Ftp Timeout",'xcloner-backup-and-restore')?>" id="ftp_timeout" type="text" name="xcloner_ftp_timeout" class="validate" value="<?php echo get_option("xcloner_ftp_timeout", 30)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_cleanup_days"><?php echo __("Ftp Cleanup (days)",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("how many days to keep the backups for",'xcloner-backup-and-restore')?>" id="ftp_cleanup_days" type="text" name="xcloner_ftp_cleanup_days" class="validate" value="<?php echo get_option("xcloner_ftp_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="ftp"><?php echo __("Save Settings",'xcloner-backup-and-restore')?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="ftp" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify",'xcloner-backup-and-restore')?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			<!-- SFTP STORAGE-->
			<li id="sftp">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("SFTP Storage",'xcloner-backup-and-restore')?>
					<div class="switch right">
						<label>
						Off
						<input type="checkbox" name="xcloner_sftp_enable" class="status" value="1" <?php if(get_option("xcloner_sftp_enable")) echo "checked"?> \>
						<span class="lever"></span>
						On
						</label>
					</div>
				</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_host"><?php echo __("SFTP Hostname",'xcloner-backup-and-restore')?></label>
						</div>
						<div class="col s12 m6">
							<input placeholder="<?php echo __("SFTP Hostname",'xcloner-backup-and-restore')?>" id="sftp_host" type="text" name="xcloner_sftp_hostname" class="validate" value="<?php echo get_option("xcloner_sftp_hostname")?>">
				        </div>
				        <div class=" col s12 m2">
							<input placeholder="<?php echo __("SFTP Port",'xcloner-backup-and-restore')?>" id="sftp_port" type="text" name="xcloner_sftp_port" class="validate" value="<?php echo get_option("xcloner_sftp_port", 22)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_username"><?php echo __("SFTP Username",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("SFTP Username",'xcloner-backup-and-restore')?>" id="sftp_username" type="text" name="xcloner_sftp_username" class="validate" value="<?php echo get_option("xcloner_sftp_username")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_password"><?php echo __("SFTP or Private Key Password",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("SFTP or Private Key Password",'xcloner-backup-and-restore')?>" id="ftp_spassword" type="password" name="xcloner_sftp_password" class="validate" value="<?php echo get_option("xcloner_sftp_password")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_private_key"><?php echo __("SFTP Private Key(RSA)",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<textarea rows="5" placeholder="<?php echo __("Local Server Path or Contents of the SFTP Private Key RSA File",'xcloner-backup-and-restore')?>" id="sftp_private_key" type="text" name="xcloner_sftp_private_key" class="validate" value=""><?php echo get_option("xcloner_sftp_private_key")?></textarea>
						</div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_root"><?php echo __("SFTP Storage Folder",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("SFTP Storage Folder",'xcloner-backup-and-restore')?>" id="sftp_root" type="text" name="xcloner_sftp_path" class="validate" value="<?php echo get_option("xcloner_sftp_path")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_timeout"><?php echo __("SFTP Timeout",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m2">
							<input placeholder="<?php echo __("SFTP Timeout",'xcloner-backup-and-restore')?>" id="sftp_timeout" type="text" name="xcloner_sftp_timeout" class="validate" value="<?php echo get_option("xcloner_sftp_timeout", 30)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_cleanup_days"><?php echo __("SFTP Cleanup (days)",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("how many days to keep the backups for",'xcloner-backup-and-restore')?>" id="sftp_cleanup_days" type="text" name="xcloner_sftp_cleanup_days" class="validate" value="<?php echo get_option("xcloner_sftp_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="sftp"><?php echo __("Save Settings",'xcloner-backup-and-restore')?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="sftp" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify",'xcloner-backup-and-restore')?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			
			<!-- AWS STORAGE-->
			<li id="aws">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("AWS Storage",'xcloner-backup-and-restore')?>
					<div class="switch right">
						<label>
						Off
						<input type="checkbox" name="xcloner_aws_enable" class="status" value="1" <?php if(get_option("xcloner_aws_enable")) echo "checked"?> \>
						<span class="lever"></span>
						On
						</label>
					</div>
				</div>
				<div class="collapsible-body">
			        
			        <div class="row">
						<div class="col s12 m3 label">
							&nbsp;
						</div>	
						<div class=" col s12 m6">
							<p>
								<?php echo sprintf(__('Visit %s and get your "Key" and "Secret".'), "<a href='https://aws.amazon.com/s3/' target='_blank'>https://aws.amazon.com/s3/</a>")?>
							</p>
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="aws_key"><?php echo __("AWS Key",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("AWS Key",'xcloner-backup-and-restore')?>" id="aws_key" type="text" name="xcloner_aws_key" class="validate" value="<?php echo get_option("xcloner_aws_key")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="aws_secret"><?php echo __("AWS Secret",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("AWS Secret",'xcloner-backup-and-restore')?>" id="aws_secret" type="text" name="xcloner_aws_secret" class="validate" value="<?php echo get_option("xcloner_aws_secret")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="aws_region"><?php echo __("AWS Region",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<select placeholder="<?php echo __("example: us-east-1",'xcloner-backup-and-restore')?>" id="aws_region" type="text" name="xcloner_aws_region" class="validate" value="<?php echo get_option("xcloner_aws_region")?>" autocomplete="off" >
							<option readonly value=""><?php echo __("Please Select AWS Region")?></option>
							<?php 							
							$aws_regions = $remote_storage->get_aws_regions();
							
							foreach($aws_regions as $key=>$region){
								?>
								<option value="<?php echo $key?>" <?php echo ($key == get_option('xcloner_aws_region')?"selected":"")?>><?php echo $region?> = <?php echo $key?></option>
								<?php
								}
							?>
							</select>
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="aws_bucket_name"><?php echo __("AWS Bucket Name",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("AWS Bucket Name",'xcloner-backup-and-restore')?>" id="aws_bucket_name" type="text" name="xcloner_aws_bucket_name" class="validate" value="<?php echo get_option("xcloner_aws_bucket_name")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="aws_cleanup_days"><?php echo __("AWS Cleanup (days)",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("how many days to keep the backups for",'xcloner-backup-and-restore')?>" id="aws_cleanup_days" type="text" name="xcloner_aws_cleanup_days" class="validate" value="<?php echo get_option("xcloner_aws_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="aws"><?php echo __("Save Settings",'xcloner-backup-and-restore')?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="aws" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify",'xcloner-backup-and-restore')?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			
			<!-- DROPBOX STORAGE-->
			<li id="dropbox">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("Dropbox Storage",'xcloner-backup-and-restore')?>
					<div class="switch right">
						<label>
						Off
						<input type="checkbox" name="xcloner_dropbox_enable" class="status" value="1" <?php if(get_option("xcloner_dropbox_enable")) echo "checked"?> \>
						<span class="lever"></span>
						On
						</label>
					</div>
				</div>
				<div class="collapsible-body">
			        
			        <div class="row">
						<div class="col s12 m3 label">
							&nbsp;
						</div>	
						<div class=" col s12 m6">
							<p>
								<?php echo sprintf(__('Visit %s and get your "App secret".'), "<a href='https://www.dropbox.com/developers/apps' target='_blank'>https://www.dropbox.com/developers/apps</a>")?>
							</p>
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="dropbox_access_token"><?php echo __("Dropbox Access Token",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Dropbox Access Token",'xcloner-backup-and-restore')?>" id="dropbox_access_token" type="text" name="xcloner_dropbox_access_token" class="validate" value="<?php echo get_option("xcloner_dropbox_access_token")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="dropbox_app_secret"><?php echo __("Dropbox App Secret",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Dropbox App Secret",'xcloner-backup-and-restore')?>" id="dropbox_app_secret" type="text" name="xcloner_dropbox_app_secret" class="validate" value="<?php echo get_option("xcloner_dropbox_app_secret")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="dropbox_prefix"><?php echo __("Dropbox Prefix",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Dropbox Prefix",'xcloner-backup-and-restore')?>" id="dropbox_prefix" type="text" name="xcloner_dropbox_prefix" class="validate" value="<?php echo get_option("xcloner_dropbox_prefix")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="dropbox_cleanup_days"><?php echo __("Dropbox Cleanup (days)",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("how many days to keep the backups for",'xcloner-backup-and-restore')?>" id="dropbox_cleanup_days" type="text" name="xcloner_dropbox_cleanup_days" class="validate" value="<?php echo get_option("xcloner_dropbox_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="dropbox"><?php echo __("Save Settings",'xcloner-backup-and-restore')?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="dropbox" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify",'xcloner-backup-and-restore')?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			
			<!-- AZURE STORAGE-->
			<li id="azure">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("Azure Blob Storage",'xcloner-backup-and-restore')?>
					<div class="switch right">
						<label>
						Off
						<input type="checkbox" name="xcloner_azure_enable" class="status" value="1" <?php if(get_option("xcloner_azure_enable")) echo "checked"?> \>
						<span class="lever"></span>
						On
						</label>
					</div>
				</div>
				<div class="collapsible-body">
			        
			        <div class="row">
						<div class="col s12 m3 label">
							&nbsp;
						</div>	
						<div class=" col s12 m6">
							<p>
								<?php echo sprintf(__('Visit %s and get your "Api Key".','xcloner-backup-and-restore'), '<a href="https://azure.microsoft.com/en-us/services/storage/blobs/" target="_blank">https://azure.microsoft.com/en-us/services/storage/blobs/</a>')?>
							</p>
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="azure_account_name"><?php echo __("Azure Account Name",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Azure Account Name",'xcloner-backup-and-restore')?>" id="azure_account_name" type="text" name="xcloner_azure_account_name" class="validate" value="<?php echo get_option("xcloner_azure_account_name")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="azure_api_key"><?php echo __("Azure Api Key",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Azure Api Key",'xcloner-backup-and-restore')?>" id="azure_api_key" type="text" name="xcloner_azure_api_key" class="validate" value="<?php echo get_option("xcloner_azure_api_key")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="azure_container"><?php echo __("Azure Container",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Azure Container",'xcloner-backup-and-restore')?>" id="azure_container" type="text" name="xcloner_azure_container" class="validate" value="<?php echo get_option("xcloner_azure_container")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="azure_cleanup_days"><?php echo __("Azure Cleanup (days)",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("how many days to keep the backups for",'xcloner-backup-and-restore')?>" id="azure_cleanup_days" type="text" name="xcloner_azure_cleanup_days" class="validate" value="<?php echo get_option("xcloner_azure_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="azure"><?php echo __("Save Settings",'xcloner-backup-and-restore')?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="azure" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify",'xcloner-backup-and-restore')?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			
			<!-- BACKBLAZE STORAGE-->
			<li id="backblaze">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("Backblaze Storage",'xcloner-backup-and-restore')?>
					<div class="switch right">
						<label>
						Off
						<input type="checkbox" name="xcloner_backblaze_enable" class="status" value="1" <?php if(get_option("xcloner_backblaze_enable")) echo "checked"?> \>
						<span class="lever"></span>
						On
						</label>
					</div>
				</div>
				<div class="collapsible-body">
			        
			        <div class="row">
						<div class="col s12 m3 label">
							&nbsp;
						</div>	
						<div class=" col s12 m6">
							<p>
								<?php echo sprintf(__('Visit %s and get your Account Id and  Application Key.','xcloner-backup-and-restore'), '<a href="https://secure.backblaze.com/b2_buckets.htm" target="_blank">https://secure.backblaze.com/b2_buckets.htm</a>')?>
							</p>
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="backblaze_account_id"><?php echo __("Backblaze Account Id",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Backblaze Account Id",'xcloner-backup-and-restore')?>" id="backblaze_account_id" type="text" name="xcloner_backblaze_account_id" class="validate" value="<?php echo get_option("xcloner_backblaze_account_id")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="backblaze_application_key"><?php echo __("Backblaze Application Key",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Backblaze Application Key",'xcloner-backup-and-restore')?>" id="backblaze_application_key" type="text" name="xcloner_backblaze_application_key" class="validate" value="<?php echo get_option("xcloner_backblaze_application_key")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="backblaze_bucket_name"><?php echo __("Backblaze Bucket Name",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Backblaze Bucket Name",'xcloner-backup-and-restore')?>" id="backblaze_bucket_name" type="text" name="xcloner_backblaze_bucket_name" class="validate" value="<?php echo get_option("xcloner_backblaze_bucket_name")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="backblaze_cleanup_days"><?php echo __("Backblaze Cleanup (days)",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("how many days to keep the backups for",'xcloner-backup-and-restore')?>" id="backblaze_cleanup_days" type="text" name="xcloner_backblaze_cleanup_days" class="validate" value="<?php echo get_option("xcloner_backblaze_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="backblaze"><?php echo __("Save Settings",'xcloner-backup-and-restore')?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="backblaze" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify",'xcloner-backup-and-restore')?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			
			<!-- WEBDAV STORAGE-->
			<li id="webdav">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("WebDAV Storage",'xcloner-backup-and-restore')?>
					<div class="switch right">
						<label>
						Off
						<input type="checkbox" name="xcloner_webdav_enable" class="status" value="1" <?php if(get_option("xcloner_webdav_enable")) echo "checked"?> \>
						<span class="lever"></span>
						On
						</label>
					</div>
				</div>
				<div class="collapsible-body">
			        
			        <div class="row">
						<div class="col s12 m3 label">
							&nbsp;
						</div>	
						<div class=" col s12 m6">
							<p>
								<?php //echo sprintf(__('Visit %s and get your Account Id and  Application Key.','xcloner-backup-and-restore'), '<a href="https://secure.backblaze.com/b2_buckets.htm" target="_blank">https://secure.backblaze.com/b2_buckets.htm</a>')?>
							</p>
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="webdav_url"><?php echo __("WebDAV Base Url",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("WebDAV Base Url",'xcloner-backup-and-restore')?>" id="webdav_url" type="text" name="xcloner_webdav_url" class="validate" value="<?php echo get_option("xcloner_webdav_url")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="webdav_username"><?php echo __("WebDAV Username",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("WebDAV Username",'xcloner-backup-and-restore')?>" id="webdav_username" type="text" name="xcloner_webdav_username" class="validate" value="<?php echo get_option("xcloner_webdav_username")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="webdav_password"><?php echo __("WebDAV Password",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("WebDAV Password",'xcloner-backup-and-restore')?>" id="webdav_password" type="password" name="xcloner_webdav_password" class="validate" value="<?php echo get_option("xcloner_webdav_password")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="webdav_target_folder"><?php echo __("WebDAV Target Folder",'xcloner-backup-and-restore')?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("WebDAV Target Folder",'xcloner-backup-and-restore')?>" id="webdav_target_folder" type="text" name="xcloner_webdav_target_folder" class="validate" value="<?php echo get_option("xcloner_webdav_target_folder")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="webdav_cleanup_days"><?php echo __("WebDAV Cleanup (days)",'xcloner-backup-and-restore')?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("how many days to keep the backups for",'xcloner-backup-and-restore')?>" id="webdav_cleanup_days" type="text" name="xcloner_webdav_cleanup_days" class="validate" value="<?php echo get_option("xcloner_webdav_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="webdav"><?php echo __("Save Settings",'xcloner-backup-and-restore')?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="webdav" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify",'xcloner-backup-and-restore')?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			
			<!-- Google DRIVE STORAGE-->
			<li id="gdrive">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("Google Drive Storage",'xcloner-backup-and-restore')?>
					<?php if($gdrive_construct):?>
					<div class="switch right">
						<label>
						Off
						<input type="checkbox" name="xcloner_gdrive_enable" class="status" value="1" <?php if(get_option("xcloner_gdrive_enable")) echo "checked"?> \>
						<span class="lever"></span>
						On
						</label>
					</div>
					<?php endif?>
				</div>
				<div class="collapsible-body">
			        
			        <?php if($gdrive_construct) : ?>
			        
				        <div class="row">
							<div class="col s12 m3 label">
								&nbsp;
							</div>	
							<div class=" col s12 m9">
								<p>
									<?php echo sprintf(__('Visit %s to create a new application and get your Client ID and Client Secret.','xcloner-backup-and-restore'), '<a href="https://console.developers.google.com" target="_blank">https://console.developers.google.com</a>')?>
									<a href="https://youtu.be/YXUVPUVgG8k" target="_blank" class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-html="true" 
									data-tooltip="<?php echo sprintf(__('Click here to view a short video explaining how to create the Client ID and Client Secret as well as connecting XCloner with the Google Drive API %s','xcloner-backup-and-restore'),"<br />https://youtu.be/YXUVPUVgG8k")?>" data-tooltip-id="92c95730-94e9-7b59-bd52-14adc30d5e3e"><i class="material-icons">help_outline</i></a>	
								</p>
					        </div>
				        </div>
			        
						<div class="row">
							<div class="col s12 m3 label">
								<label for="gdrive_client_id"><?php echo __("Client ID",'xcloner-backup-and-restore')?></label>
							</div>
							<div class=" col s12 m6">
								<input placeholder="<?php echo __("Google Client ID",'xcloner-backup-and-restore')?>" id="gdrive_client_id" type="text" name="xcloner_gdrive_client_id" class="validate" value="<?php echo get_option("xcloner_gdrive_client_id")?>">
					        </div>
				        </div>
				        
				        <div class="row">
							<div class="col s12 m3 label">
								<label for="gdrive_client_secret"><?php echo __("Client Secret",'xcloner-backup-and-restore')?></label>
							</div>
							<div class=" col s12 m6">
								<input placeholder="<?php echo __("Google Client Secret",'xcloner-backup-and-restore')?>" id="gdrive_client_secret" type="text" name="xcloner_gdrive_client_secret" class="validate" value="<?php echo get_option("xcloner_gdrive_client_secret")?>">
					        </div>
				        </div>
				        
				        	
				        <div class="row">
							<div class="col s12 m3 label">
								&nbsp;
							</div>	
							<div class=" col s12 m6">
									<a class="btn" target="_blank" id="gdrive_authorization_click" onclick="jQuery('#authentification_code').show()" href="<?php echo $gdrive_auth_url?>"><?php echo sprintf(__('Authorize Google Drive','xcloner-backup-and-restore'))?></a>
									<input type="text" name="authentification_code" id="authentification_code" placeholder="<?php echo __("Paste Authorization Code Here","xcloner-backup-and-restore")?>">
					        </div>
				        </div>
				        
				        <div class="row">
							<div class="col s12 m3 label">
								<label for="gdrive_target_folder"><?php echo __("Folder ID or Root Path",'xcloner-backup-and-restore')?>
									<a class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-html="true" \
									data-tooltip="<?php echo __('Folder ID can be found by right clicking on the folder name and selecting \'Get shareable link\' menu, format https://drive.google.com/open?id={FOLDER_ID}<br />
									If you supply a folder name, it has to exists in the drive root and start with / , example /backups.xcloner.com/','xcloner-backup-and-restore')?>" data-tooltip-id="92c95730-94e9-7b59-bd52-14adc30d5e3e"><i class="material-icons">help_outline</i></a>	
								</label>
							</div>	
							<div class=" col s12 m6">
								<input placeholder="<?php echo __("Target Folder ID or Root Path",'xcloner-backup-and-restore')?>" id="gdrive_target_folder" type="text" name="xcloner_gdrive_target_folder" class="validate" value="<?php echo get_option("xcloner_gdrive_target_folder")?>" autocomplete="off" >
					        </div>
				        </div>
				        
				        <div class="row">
							<div class="col s12 m3 label">
								<label for="gdrive_cleanup_days"><?php echo __("Google Drive Cleanup (days)",'xcloner-backup-and-restore')?></label>
							</div>
							<div class=" col s12 m6">
								<input placeholder="<?php echo __("how many days to keep the backups for",'xcloner-backup-and-restore')?>" id="gdrive_cleanup_days" type="text" name="xcloner_gdrive_cleanup_days" class="validate" value="<?php echo get_option("xcloner_gdrive_cleanup_days")?>">
					        </div>
				        </div>
				        
				        <div class="row">
							<div class="col s6 m4">
								<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="gdrive"><?php echo __("Save Settings",'xcloner-backup-and-restore')?>
									<i class="material-icons right">save</i>
								</button>
							</div>	
							<div class="col s6 m4">
								<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="gdrive" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify",'xcloner-backup-and-restore')?>
									<i class="material-icons right">import_export</i>
								</button>
							</div>
						</div>
					<?php else:?>
				
						<div class="row">
							<div class=" col s12">
								<div class="center">
									<?php 
										$url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=xcloner-google-drive'), 'install-plugin_xcloner-google-drive');
									?>
									<h6><?php echo __("This storage option requires the XCloner-Google-Drive Wordpress Plugin to be installed and activated.")?></h6>
									<h6><?php echo __("PHP 5.5 minimum version is required.")?></h6>
									<br />
									<a class="install-now btn" data-slug="xcloner-google-drive" href="<?php echo $url;?>" aria-label="Install XCloner Google Drive 1.0.0 now" data-name="XCloner Google Drive 1.0.0">
										<?php echo sprintf(__('Install Now','xcloner-backup-and-restore'))?>
									</a>
									
									<a href="<?php echo admin_url("plugin-install.php")?>?tab=plugin-information&amp;plugin=xcloner-google-drive&amp;TB_iframe=true&amp;width=772&amp;height=499" class="btn thickbox open-plugin-details-modal" aria-label="More information about Theme Check 20160523.1" data-title="Theme Check 20160523.1">
									<!--
									<a class="btn" href="https://github.com/ovidiul/XCloner-Google-Drive/archive/master.zip">
									-->
										<?php echo sprintf(__('More Details','xcloner-backup-and-restore'))?>
									</a>
								</div>
					        </div>
				        </div>
						
					<?php endif; ?>
			        
				</div>
			</li>
			
			

		</ul>
	</div>
</div>  

</form>

<script>
jQuery(document).ready(function(){
	
	var remote_storage = new Xcloner_Remote_Storage();
	
	jQuery(".remote-storage .status").on("change", function(){
			remote_storage.toggle_status(this);
	})
	
	jQuery(".remote-storage-form #action").on("click", function(){
		var tag = jQuery(this).val()
		window.location.hash = "#"+tag;
	})
	
	jQuery("#gdrive_authorization_click").on("click", function(e){
		
		var href = (jQuery(this).attr("href"))
		
		var new_href= href.replace(/(client_id=).*?(&)/,'$1' + jQuery("#gdrive_client_id").val() + '$2');
		
		jQuery(this).attr("href", new_href)
		
	});
	
	if(location.hash)
		jQuery(location.hash+" div.collapsible-header").addClass("active");
	
	jQuery('.collapsible').collapsible();
	
	Materialize.updateTextFields();
});
        
</script>
