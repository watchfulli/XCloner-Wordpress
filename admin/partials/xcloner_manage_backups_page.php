<?php

$xcloner_file_system 		= new Xcloner_File_System();

$backup_list = $xcloner_file_system->get_backup_archives_list();

$xcloner_remote_storage = new Xcloner_Remote_Storage();
$available_storages = $xcloner_remote_storage->get_available_storages();
?>

<h1><?= esc_html(get_admin_page_title()); ?></h1>

<table id="manage_backups">
	<thead>
	  <tr class="grey lighten-2">
		  <th class="no-sort">
				<p>
				  <input name="select_all" class="" id="select_all" value="1" type="checkbox">
				  <label for="select_all">&nbsp;</label>
				</p> 
		  </th>
		  <th data-field="id"><?php echo __("Backup Name","xcloner")?></th>
		  <th data-field="name"><?php echo __("Created Time","xcloner")?></th>
		  <th data-field="name"><?php echo __("Size","xcloner")?></th>
		  <th class="no-sort" data-field="price"><?php echo __("Action","xcloner")?></th>
		  
	  </tr>
	</thead>
	
	<tbody>

      
<?php 
$i = 0;
foreach($backup_list as $file_info):?>
<?php if(!isset($file_info['parent'])):?>	
	
	<tr>
		<td class="checkbox">
			<p>
			<input name="backup[]" value="<?php echo $file_info['path']?>" type="checkbox" id="checkbox_<?php echo ++$i?>">
			<label for="checkbox_<?php echo $i?>">&nbsp;</label>
			</p>
		</td>
		<td>
			<?php echo $file_info['path']?>
			<?php 
			if(isset($file_info['childs']) and is_array($file_info['childs'])):
			?>
			<a href="#" title="expand" class="expand-multipart add"><i class="material-icons">add</i></a>
			<a href="#" title="collapse" class="expand-multipart remove"><i class="material-icons">remove</i></a>
			<ul class="multipart">
			<?php foreach($file_info['childs'] as $child):?>
				<?php #$download_file .= "|".$child[0];?>
				<li>
					<?php echo $child[0]?> (<?php echo size_format($child[2])?>) 
					<a href="#<?php echo $child[0];?>" class="download" title="Download Backup"><i class="material-icons">file_download</i></a>
					<a href="#<?php echo $child[0]?>" class="list-backup-content" title="<?php echo __('List Backup Content','xcloner')?>"><i class="material-icons">folder_open</i></a> 
				</li>
				<?php endforeach;?>
			</ul>
			<?php endif;?>
		</td>
		<td><?php echo date("d M, Y H:i", $file_info['timestamp'])?></td>
		<td><?php echo size_format($file_info['size'])?></td>
		<td>
			 <a href="#<?php echo $file_info['path'];?>" class="download" title="<?php echo __('Download Backup','xcloner')?>"><i class="material-icons">file_download</i></a>
			 <?php if(sizeof($available_storages)):?>
				<a href="#<?php echo $file_info['path']?>" class="cloud-upload" title="<?php echo __('Send Backup To Remote Storage','xcloner')?>"><i class="material-icons">cloud_upload</i></a>
			 <?php endif?>
			 <a href="#<?php echo $file_info['path']?>" class="list-backup-content" title="<?php echo __('List Backup Content','xcloner')?>"><i class="material-icons">folder_open</i></a>
			 <a href="#<?php echo $file_info['path']?>" class="delete" title="<?php echo __('Delete Backup','xcloner')?>"><i class="material-icons">delete</i></a>
		</td>
		
	</tr>
	
<?php endif?>
<?php endforeach?>
	
	</tbody>
</table>

<a class="waves-effect waves-light btn delete-all"><i class="material-icons left">delete</i><?php echo __("Delete","xcloner")?></a>

<!-- List Backup Content Modal-->

<div id="backup_cotent_modal" class="modal">
	<div class="modal-content">
		<h4><?php echo sprintf(__("Listing Backup Content ","xcloner"), "")?></h4>
		<h5 class="backup-name"></h5>
		
		<div class="progress">
			<div class="indeterminate"></div>
		</div>
		<div class="files-list"></div>
	</div>	
</div>

<!-- Remote Storage Modal Structure -->
<div id="remote_storage_modal" class="modal">
	<form method="POST" class="remote-storage-form">
	<input type="hidden" name="file" class="backup_name">	  
	<div class="modal-content">
	  <h4><?php echo __("Remote Storage Transfer","xcloner")?></h4>
	  <p>
	  <?php if(sizeof($available_storages)):?>
			<div class="row">
				<div class="col s12 label">
					<label><?php echo sprintf(__('Send %s to remote storage','xcloner'), "<span class='backup_name'></span>") ?></label>
				</div>
				<div class="input-field col s8 m10">
					<select name="transfer_storage" id="transfer_storage" class="validate" required >
						<option value="" selected><?php echo __('please select...', 'xcloner') ?></option>
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
				<?php echo __("Uploading backup to the selected remote storage...","xcloner")?> <span class="status-text"></span>
				<div class="progress">
					<div class="indeterminate"></div>
				</div>
			</div>
		<?php endif?>
		</p>
	</div>
	</form>	
</div>

