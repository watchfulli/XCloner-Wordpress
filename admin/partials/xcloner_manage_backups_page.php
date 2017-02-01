<?php

$xcloner_file_system 		= new Xcloner_File_System();

$backup_list = $xcloner_file_system->get_backup_archives_list();

//print_r($backup_list);
?>

<h1><?= esc_html(get_admin_page_title()); ?></h1>

<table id="manage_backups">
	<thead>
	  <tr>
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
				</li>
				<?php endforeach;?>
			</ul>
			<?php endif;?>
		</td>
		<td><?php echo date("d M, Y H:i", $file_info['timestamp'])?></td>
		<td><?php echo size_format($file_info['size'])?></td>
		<td>
			 <a href="#<?php echo $file_info['path'];?>" class="download" title="Download Backup"><i class="material-icons">file_download</i></a>
			 <!--<a href="#<?php echo $file_info['path']?>" class="list-view" title="View Backup Files List"><i class="material-icons">view_list</i></a>-->
			 <a href="#<?php echo $file_info['path']?>" class="delete" title="Delete Backup"><i class="material-icons">delete</i></a>
		</td>
		
	</tr>
	
<?php endif?>
<?php endforeach?>
	
	</tbody>
</table>

<a class="waves-effect waves-light btn delete-all"><i class="material-icons left">delete</i><?php echo __("Delete","xcloner")?></a>

