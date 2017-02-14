<h1><?= esc_html(get_admin_page_title()); ?></h1>

<form class="remote-storage-form" method="POST">

<input type="hidden" id="connection_check" name="connection_check" value="">

<div class="row remote-storage">
	<div class="col s12 m12 l10">
		<ul class="collapsible popout" data-collapsible="accordion">
			<!-- FTP STORAGE-->
			<li id="ftp">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("Ftp Storage","xcloner")?>
					<div class="right">
						<div class="switch">
							<label>
							Off
							<input type="checkbox" name="xcloner_ftp_enable" class="status" value="1" <?php if(get_option("xcloner_ftp_enable")) echo "checked"?> \>
							<span class="lever"></span>
							On
							</label>
						</div>
					</div>
				</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_host"><?php echo __("Ftp Hostname","xcloner")?></label>
						</div>
						<div class="col s12 m6">
							<input placeholder="<?php echo __("Ftp Hostname","xcloner")?>" id="ftp_host" type="text" name="xcloner_ftp_hostname" class="validate" value="<?php echo get_option("xcloner_ftp_hostname")?>">
				        </div>
				        <div class=" col s12 m2">
							<input placeholder="<?php echo __("Ftp Port","xcloner")?>" id="ftp_port" type="text" name="xcloner_ftp_port" class="validate" value="<?php echo get_option("xcloner_ftp_port", 21)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_username"><?php echo __("Ftp Username","xcloner")?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Ftp Username","xcloner")?>" id="ftp_username" type="text" name="xcloner_ftp_username" class="validate" value="<?php echo get_option("xcloner_ftp_username")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_password"><?php echo __("Ftp Password","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Ftp Password","xcloner")?>" id="ftp_password" type="password" name="xcloner_ftp_password" class="validate" value="<?php echo get_option("xcloner_ftp_password")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_root"><?php echo __("Ftp Storage Folder","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Ftp Storage Folder","xcloner")?>" id="ftp_root" type="text" name="xcloner_ftp_path" class="validate" value="<?php echo get_option("xcloner_ftp_path")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_root"><?php echo __("Ftp Transfer Mode","xcloner")?></label>
						</div>
						<div class=" col s12 m6 input-field inline">
							<input name="xcloner_ftp_transfer_mode" type="radio" id="passive" value="1" <?php if(get_option("xcloner_ftp_transfer_mode", 1)) echo "checked"?> />
							<label for="passive"><?php echo __("Passive","xcloner")?></label>

							<input name="xcloner_ftp_transfer_mode" type="radio" id="active" value="0" <?php if(!get_option("xcloner_ftp_transfer_mode", 1)) echo "checked"?> />
							<label for="active"><?php echo __("Active","xcloner")?></label>
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_ssl_mode"><?php echo __("Ftp Secure Connection","xcloner")?></label>
						</div>
						<div class=" col s12 m6 input-field inline">
							<input name="xcloner_ftp_ssl_mode" type="radio" id="ftp_ssl_mode_inactive" value="0" <?php if(!get_option("xcloner_ftp_ssl_mode")) echo "checked"?> />
							<label for="ftp_ssl_mode_inactive"><?php echo __("Disable","xcloner")?></label>

							<input name="xcloner_ftp_ssl_mode" type="radio" id="ftp_ssl_mode_active" value="1" <?php if(get_option("xcloner_ftp_ssl_mode")) echo "checked"?> />
							<label for="ftp_ssl_mode_active"><?php echo __("Enable","xcloner")?></label>
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_timeout"><?php echo __("Ftp Timeout","xcloner")?></label>
						</div>
						<div class=" col s12 m2">
							<input placeholder="<?php echo __("Ftp Timeout","xcloner")?>" id="ftp_timeout" type="text" name="xcloner_ftp_timeout" class="validate" value="<?php echo get_option("xcloner_ftp_timeout", 30)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_cleanup_days"><?php echo __("Ftp Cleanup (days)","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("how many days to keep the backups for","xcloner")?>" id="ftp_cleanup_days" type="text" name="xcloner_ftp_cleanup_days" class="validate" value="<?php echo get_option("xcloner_ftp_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="ftp"><?php echo __("Save Settings","xcloner")?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="ftp" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify","xcloner")?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			<!-- SFTP STORAGE-->
			<li id="sftp">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("SFTP Storage","xcloner")?>
					<div class="right">
						<div class="switch">
							<label>
							Off
							<input type="checkbox" name="xcloner_sftp_enable" class="status" value="1" <?php if(get_option("xcloner_sftp_enable")) echo "checked"?> \>
							<span class="lever"></span>
							On
							</label>
						</div>
					</div>
				</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_host"><?php echo __("SFTP Hostname","xcloner")?></label>
						</div>
						<div class="col s12 m6">
							<input placeholder="<?php echo __("SFTP Hostname","xcloner")?>" id="sftp_host" type="text" name="xcloner_sftp_hostname" class="validate" value="<?php echo get_option("xcloner_sftp_hostname")?>">
				        </div>
				        <div class=" col s12 m2">
							<input placeholder="<?php echo __("SFTP Port","xcloner")?>" id="sftp_port" type="text" name="xcloner_sftp_port" class="validate" value="<?php echo get_option("xcloner_sftp_port", 22)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_username"><?php echo __("SFTP Username","xcloner")?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("SFTP Username","xcloner")?>" id="sftp_username" type="text" name="xcloner_sftp_username" class="validate" value="<?php echo get_option("xcloner_sftp_username")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_password"><?php echo __("SFTP Password","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("SFTP Password","xcloner")?>" id="ftp_spassword" type="password" name="xcloner_sftp_password" class="validate" value="<?php echo get_option("xcloner_sftp_password")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_private_key"><?php echo __("SFTP Private Key","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("SFTP Private Key","xcloner")?>" id="sftp_private_key" type="text" name="xcloner_sftp_private_key" class="validate" value="<?php echo get_option("xcloner_sftp_private_key")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_root"><?php echo __("SFTP Storage Folder","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("SFTP Storage Folder","xcloner")?>" id="sftp_root" type="text" name="xcloner_sftp_path" class="validate" value="<?php echo get_option("xcloner_sftp_path")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_timeout"><?php echo __("SFTP Timeout","xcloner")?></label>
						</div>
						<div class=" col s12 m2">
							<input placeholder="<?php echo __("SFTP Timeout","xcloner")?>" id="sftp_timeout" type="text" name="xcloner_sftp_timeout" class="validate" value="<?php echo get_option("xcloner_sftp_timeout", 30)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_cleanup_days"><?php echo __("SFTP Cleanup (days)","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("how many days to keep the backups for","xcloner")?>" id="sftp_cleanup_days" type="text" name="xcloner_sftp_cleanup_days" class="validate" value="<?php echo get_option("xcloner_sftp_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="sftp"><?php echo __("Save Settings","xcloner")?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="sftp" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify","xcloner")?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			
			<!-- DROPBOX STORAGE-->
			<li id="dropbox">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("Dropbox Storage","xcloner")?>
					<div class="right">
						<div class="switch">
							<label>
							Off
							<input type="checkbox" name="xcloner_dropbox_enable" class="status" value="1" <?php if(get_option("xcloner_dropbox_enable")) echo "checked"?> \>
							<span class="lever"></span>
							On
							</label>
						</div>
					</div>
				</div>
				<div class="collapsible-body">
			        
			        <div class="row">
						<div class="col s12 m3 label">
							&nbsp;
						</div>	
						<div class=" col s12 m6">
							<p>
								<?php echo sprintf(__(''))?>
							</p>
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="xcloner_azure_enable"><?php echo __("Dropbox Access Token","xcloner")?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Dropbox Access Token","xcloner")?>" id="dropbox_access_token" type="text" name="xcloner_dropbox_access_token" class="validate" value="<?php echo get_option("xcloner_dropbox_access_token")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="dropbox_app_secret"><?php echo __("Dropbox App Secret","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Dropbox App Secret","xcloner")?>" id="dropbox_app_secret" type="text" name="xcloner_dropbox_app_secret" class="validate" value="<?php echo get_option("xcloner_dropbox_app_secret")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="dropbox_prefix"><?php echo __("Dropbox Prefix","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Dropbox Prefix","xcloner")?>" id="dropbox_prefix" type="text" name="xcloner_dropbox_prefix" class="validate" value="<?php echo get_option("xcloner_dropbox_prefix")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="dropbox"><?php echo __("Save Settings","xcloner")?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="dropbox" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify","xcloner")?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			
			<!-- AZURE STORAGE-->
			
			<li id="azure">
				<div class="collapsible-header">
					<i class="material-icons">computer</i><?php echo __("Azure Blog Storage","xcloner")?>
					<div class="right">
						<div class="switch">
							<label>
							Off
							<input type="checkbox" name="xcloner_azure_enable" class="status" value="1" <?php if(get_option("xcloner_azure_enable")) echo "checked"?> \>
							<span class="lever"></span>
							On
							</label>
						</div>
					</div>
				</div>
				<div class="collapsible-body">
			        
			        <div class="row">
						<div class="col s12 m3 label">
							&nbsp;
						</div>	
						<div class=" col s12 m6">
							<p>
								<?php echo sprintf(__('Visit %s and get your "Api Key".','xcloner'), '<a href="https://azure.microsoft.com/en-us/services/storage/blobs/" target="_blank">https://azure.microsoft.com/en-us/services/storage/blobs/</a>')?>
							</p>
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="azure_account_name"><?php echo __("Azure Account Name","xcloner")?></label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Azure Account Name","xcloner")?>" id="azure_account_name" type="text" name="xcloner_azure_account_name" class="validate" value="<?php echo get_option("xcloner_azure_account_name")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="azure_api_key"><?php echo __("Azure Api Key","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Azure Api Key","xcloner")?>" id="azure_api_key" type="text" name="xcloner_azure_api_key" class="validate" value="<?php echo get_option("xcloner_azure_api_key")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="azure_container"><?php echo __("Azure Container","xcloner")?></label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="<?php echo __("Azure Container","xcloner")?>" id="azure_container" type="text" name="xcloner_azure_container" class="validate" value="<?php echo get_option("xcloner_azure_container")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light" type="submit" name="action" id="action"  value="azure"><?php echo __("Save Settings","xcloner")?>
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s6 m4">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" id="action"  value="azure" onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify","xcloner")?>
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			
			<!--<li>
				<div class="collapsible-header"><i class="material-icons">cloud</i>Amazon S3 Storage</div>
				<div class="collapsible-body"><span>Lorem ipsum dolor sit amet.</span></div>
			</li>
			<li>
				<div class="collapsible-header"><i class="material-icons">cloud</i>Dropbox Storage</div>
				<div class="collapsible-body"><span>Lorem ipsum dolor sit amet.</span></div>
			</li>
			-->
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
	
	if(location.hash)
		jQuery(location.hash+" div.collapsible-header").addClass("active");
	
	jQuery('.collapsible').collapsible();
	
	Materialize.updateTextFields();
});
        
</script>
