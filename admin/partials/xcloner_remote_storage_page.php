<h1><?= esc_html(get_admin_page_title()); ?></h1>

<form class="remote-storage-form" method="POST">

<input type="hidden" id="connection_check" name="connection_check" value="">

<div class="row remote-storage">
	<div class="col s12 m12 l8">
		<ul class="collapsible popout" data-collapsible="accordion">
			<!-- FTP STORAGE-->
			<li>
				<div class="collapsible-header">
					<i class="material-icons">computer</i>Ftp Storage
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
							<label for="ftp_host">Ftp Hostname</label>
						</div>
						<div class="col s12 m6">
							<input placeholder="Ftp Hostname" id="ftp_host" type="text" name="xcloner_ftp_hostname" class="validate" value="<?php echo get_option("xcloner_ftp_hostname")?>">
				        </div>
				        <div class=" col s12 m2">
							<input placeholder="Ftp Port" id="ftp_port" type="text" name="xcloner_ftp_port" class="validate" value="<?php echo get_option("xcloner_ftp_port", 21)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_username">Ftp Username</label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="Ftp Username" id="ftp_username" type="text" name="xcloner_ftp_username" class="validate" value="<?php echo get_option("xcloner_ftp_username")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_password">Ftp Password</label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="Ftp Password" id="ftp_password" type="password" name="xcloner_ftp_password" class="validate" value="<?php echo get_option("xcloner_ftp_password")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_root">Ftp Storage Folder</label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="Ftp Storage Folder" id="ftp_root" type="text" name="xcloner_ftp_path" class="validate" value="<?php echo get_option("xcloner_ftp_path")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_root">Ftp Transfer Mode</label>
						</div>
						<div class=" col s12 m6 input-field inline">
							<input name="xcloner_ftp_transfer_mode" type="radio" id="passive" value="1" <?php if(get_option("xcloner_ftp_transfer_mode", 1)) echo "checked"?> />
							<label for="passive">Passive</label>

							<input name="xcloner_ftp_transfer_mode" type="radio" id="active" value="0" <?php if(!get_option("xcloner_ftp_transfer_mode", 1)) echo "checked"?> />
							<label for="active">Active</label>
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_ssl_mode">Ftp Secure Connection</label>
						</div>
						<div class=" col s12 m6 input-field inline">
							<input name="xcloner_ftp_ssl_mode" type="radio" id="ftp_ssl_mode_inactive" value="0" <?php if(!get_option("xcloner_ftp_ssl_mode")) echo "checked"?> />
							<label for="ftp_ssl_mode_inactive">Disable</label>

							<input name="xcloner_ftp_ssl_mode" type="radio" id="ftp_ssl_mode_active" value="1" <?php if(get_option("xcloner_ftp_ssl_mode")) echo "checked"?> />
							<label for="ftp_ssl_mode_active">Enable</label>
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_timeout">Ftp Timeout</label>
						</div>
						<div class=" col s12 m2">
							<input placeholder="Ftp Timeout" id="ftp_timeout" type="text" name="xcloner_ftp_timeout" class="validate" value="<?php echo get_option("xcloner_ftp_timeout", 30)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="ftp_cleanup_days">FTP Cleanup (days)</label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="how many days to keep the backups for" id="ftp_cleanup_days" type="text" name="xcloner_ftp_cleanup_days" class="validate" value="<?php echo get_option("xcloner_ftp_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m2">
							<button class="btn waves-effect waves-light" type="submit" name="action" value="ftp">Save Settings
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s12 m2">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" value="ftp" onclick="jQuery('#connection_check').val('ftp_check')">Verify
								<i class="material-icons right">import_export</i>
							</button>
						</div>
					</div>
			        
				</div>
			</li>
			<!-- SFTP STORAGE-->
			<li>
				<div class="collapsible-header active">
					<i class="material-icons">computer</i>SFTP Storage
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
							<label for="sftp_host">SFTP Hostname</label>
						</div>
						<div class="col s12 m6">
							<input placeholder="SFTP Hostname" id="sftp_host" type="text" name="xcloner_sftp_hostname" class="validate" value="<?php echo get_option("xcloner_sftp_hostname")?>">
				        </div>
				        <div class=" col s12 m2">
							<input placeholder="SFTP Port" id="sftp_port" type="text" name="xcloner_sftp_port" class="validate" value="<?php echo get_option("xcloner_sftp_port", 22)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_username">SFTP Username</label>
						</div>	
						<div class=" col s12 m6">
							<input placeholder="SFTP Username" id="sftp_username" type="text" name="xcloner_sftp_username" class="validate" value="<?php echo get_option("xcloner_sftp_username")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_password">SFTP Password</label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="SFTP Password" id="ftp_spassword" type="password" name="xcloner_sftp_password" class="validate" value="<?php echo get_option("xcloner_sftp_password")?>" autocomplete="off" >
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_private_key">SFTP Private Key</label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="SFTP Private Key" id="sftp_private_key" type="text" name="xcloner_sftp_private_key" class="validate" value="<?php echo get_option("xcloner_sftp_private_key")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_root">SFTP Storage Folder</label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="SFTP Storage Folder" id="sftp_root" type="text" name="xcloner_sftp_path" class="validate" value="<?php echo get_option("xcloner_sftp_path")?>">
						</div>	
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_timeout">SFTP Timeout</label>
						</div>
						<div class=" col s12 m2">
							<input placeholder="SFTP Timeout" id="sftp_timeout" type="text" name="xcloner_sftp_timeout" class="validate" value="<?php echo get_option("xcloner_sftp_timeout", 30)?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m3 label">
							<label for="sftp_cleanup_days">SFTP Cleanup (days)</label>
						</div>
						<div class=" col s12 m6">
							<input placeholder="how many days to keep the backups for" id="sftp_cleanup_days" type="text" name="xcloner_sftp_cleanup_days" class="validate" value="<?php echo get_option("xcloner_sftp_cleanup_days")?>">
				        </div>
			        </div>
			        
			        <div class="row">
						<div class="col s12 m2">
							<button class="btn waves-effect waves-light" type="submit" name="action" value="sftp">Save Settings
								<i class="material-icons right">save</i>
							</button>
						</div>	
						<div class="col s12 m2">
							<button class="btn waves-effect waves-light orange" type="submit" name="action" value="sftp" onclick="jQuery('#connection_check').val('sftp_check')">Verify
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
	
	jQuery('.collapsible').collapsible();
	
	Materialize.updateTextFields();
});
        
</script>
