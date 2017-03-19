<?php

$xcloner_file_system 		= $this->get_xcloner_container()->get_xcloner_filesystem();
$xcloner_sanitization 		= $this->get_xcloner_container()->get_xcloner_sanitization();
$xcloner_remote_storage 	= $this->get_xcloner_container()->get_xcloner_remote_storage();
$storage_selection 			= "";

if(isset($_GET['storage_selection']) and $_GET['storage_selection'])
{
	$storage_selection = $xcloner_sanitization->sanitize_input_as_string($_GET['storage_selection']);
}

$backup_list = $xcloner_file_system->get_backup_archives_list($storage_selection);

$available_storages = $xcloner_remote_storage->get_available_storages();


?>

<div class="row">
	<div class="col s12 m6 l9">
		<h1><?= esc_html(get_admin_page_title()); ?></h1>
	</div>	
	<?php if(sizeof($available_storages)):?>
		<div class="col s12 m6 l3 remote-storage-selection">
				<select name="storage_selection" id="storage_selection" class="validate" required >
					
					<?php if($storage_selection):?>
						<option value="" selected><?php echo __('Change To Local Storage...', 'xcloner-backup-and-restore') ?></option>
					<?php else: ?>
						<option value="" selected><?php echo __('Change To Remote Storage...', 'xcloner-backup-and-restore') ?></option>
					<?php endif;?>
						
					<?php foreach($available_storages as $storage=>$text):?>
						<option value="<?php echo $storage?>"<?php if($storage == $storage_selection) echo "selected"?>><?php echo $text?></option>
					<?php endforeach?>
				</select>
	<?php endif?>
</div>	

<table id="manage_backups">
	<thead>
	  <tr class="grey lighten-2">
		  <th class="no-sort">
				<p>
				  <input name="select_all" class="" id="select_all" value="1" type="checkbox">
				  <label for="select_all">&nbsp;</label>
				</p> 
		  </th>
		  <th data-field="id"><?php echo __("Backup Name",'xcloner-backup-and-restore')?></th>
		  <th data-field="name"><?php echo __("Created Time",'xcloner-backup-and-restore')?></th>
		  <th data-field="name"><?php echo __("Size",'xcloner-backup-and-restore')?></th>
		  <th class="no-sort" data-field="price"><?php echo __("Action",'xcloner-backup-and-restore')?></th>
		  
	  </tr>
	</thead>
	
	<tbody>

      
<?php 
$i = 0;
foreach($backup_list as $file_info):?>
<?php 
	if($storage_selection == "gdrive")
		$file_info['path'] = $file_info['filename'].".".$file_info['extension'];
	$file_exists_on_local_storage = true;
	
	if($storage_selection)
	{
		if(!$xcloner_file_system->get_storage_filesystem()->has($file_info['path']))
			$file_exists_on_local_storage = false;
	}

?>
<?php if(!isset($file_info['parent'])):?>	
	
	<tr>
		<td class="checkbox">
			<p>
			<input name="backup[]" value="<?php echo $file_info['basename']?>" type="checkbox" id="checkbox_<?php echo ++$i?>">
			<label for="checkbox_<?php echo $i?>">&nbsp;</label>
			</p>
		</td>
		<td>
			<span class=""><?php echo $file_info['path']?></span>
			<?php if(!$file_exists_on_local_storage): ?>
				<a href="#" title="<?php echo __("File does not exists on local storage","xcloner-backup-and-restore")?>"><i class="material-icons backup_warning">warning</i></a>
			<?php endif?>
			<?php 
			if(isset($file_info['childs']) and is_array($file_info['childs'])):
			?>
			<a href="#" title="expand" class="expand-multipart add"><i class="material-icons">add</i></a>
			<a href="#" title="collapse" class="expand-multipart remove"><i class="material-icons">remove</i></a>
			<ul class="multipart">
			<?php foreach($file_info['childs'] as $child):?>
				<li>
					<?php echo $child[0]?> (<?php echo size_format($child[2])?>) 
					<?php
					$child_exists_on_local_storage = true;
					if($storage_selection)
					{
						if(!$xcloner_file_system->get_storage_filesystem()->has($child[0]))
							$child_exists_on_local_storage = false;
					}
					?>
					<?php if(!$child_exists_on_local_storage): ?>
						<a href="#" title="<?php echo __("File does not exists on local storage","xcloner-backup-and-restore")?>"><i class="material-icons backup_warning">warning</i></a>
					<?php endif?>
					<?php if(!$storage_selection) :?>
						<a href="#<?php echo $child[0];?>" class="download" title="Download Backup"><i class="material-icons">file_download</i></a>
						<a href="#<?php echo $child[0]?>" class="list-backup-content" title="<?php echo __('List Backup Content','xcloner-backup-and-restore')?>"><i class="material-icons">folder_open</i></a> 
					<?php elseif($storage_selection != "gdrive" && !$xcloner_file_system->get_storage_filesystem()->has($child[0])): ?>
						<a href="#<?php echo $child[0]?>" class="copy-remote-to-local" title="<?php echo __('Push Backup To Local Storage','xcloner-backup-and-restore')?>"><i class="material-icons">file_upload</i></a>
					<?php endif?>
				</li>
				<?php endforeach;?>
			</ul>
			<?php endif;?>
		</td>
		<td><?php if(isset($file_info['timestamp'])) echo date("d M, Y H:i", $file_info['timestamp'])?></td>
		<td><?php echo size_format($file_info['size'])?></td>
		<td>
			<?php if(!$storage_selection):?>
				<a href="#<?php echo $file_info['basename'];?>" class="download" title="<?php echo __('Download Backup','xcloner-backup-and-restore')?>"><i class="material-icons">file_download</i></a>

				<?php if(sizeof($available_storages)):?>
					<a href="#<?php echo $file_info['basename']?>" class="cloud-upload" title="<?php echo __('Send Backup To Remote Storage','xcloner-backup-and-restore')?>"><i class="material-icons">cloud_upload</i></a>
				<?php endif?>
				<a href="#<?php echo $file_info['basename']?>" class="list-backup-content" title="<?php echo __('List Backup Content','xcloner-backup-and-restore')?>"><i class="material-icons">folder_open</i></a>
			 <?php endif;?>
			 	
			<a href="#<?php echo $file_info['basename']?>" class="delete" title="<?php echo __('Delete Backup','xcloner-backup-and-restore')?>"><i class="material-icons">delete</i></a>
			<?php if($storage_selection and !$file_exists_on_local_storage):?>
				<a href="#<?php echo $file_info['basename'];?>" class="copy-remote-to-local" title="<?php echo __('Push Backup To Local Storage','xcloner-backup-and-restore')?>"><i class="material-icons">file_upload</i></a>
			<?php endif?>	
			
		</td>
		
	</tr>
	
<?php endif?>
<?php endforeach?>
	
	</tbody>
</table>

<a class="waves-effect waves-light btn delete-all"><i class="material-icons left">delete</i><?php echo __("Delete",'xcloner-backup-and-restore')?></a>

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

<!-- Local Transfer Modal-->
<div id="local_storage_upload_modal" class="modal">
	<div class="modal-content">
		<h4><?php echo sprintf(__("Transfer Remote Backup To Local Storage",'xcloner-backup-and-restore'), "")?></h4>
		<h5 class="backup-name"></h5>
		
		<div class="row status">
			<div class="progress">
				<div class="indeterminate"></div>
			</div>
			<?php echo __("Uploading backup to the local storage filesystem...",'xcloner-backup-and-restore')?> <span class="status-text"></span>
		</div>
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
					<label><?php echo sprintf(__('Send %s to remote storage','xcloner-backup-and-restore'), "<span class='backup_name'></span>") ?></label>
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

