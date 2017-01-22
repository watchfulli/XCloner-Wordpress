<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.thinkovi.com
 * @since      1.0.0
 *
 * @package    Xcloner
 * @subpackage Xcloner/admin/partials
 */
 
 $requirements 			= new XCloner_Requirements();
 $xcloner_file_system 	= new Xcloner_File_System();
 
 $xcloner_file_system->backup_storage_cleanup();
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="row dashboard">
	<div class="col s12 m12 l6">
		<div>
			<h5 class="center-align">Backup Ready</h5>
		</div>
	</div>
	<div class="col s12 m12 l6">
	  
	  <div class="card blue-grey darken-1 z-depth-4 backup-ready">
		<div class="card-content white-text">
		  <span class="card-title"><?php echo __("Backup Ready Check")?></span>
		  <ul>
				<li class="card-panel <?php echo ($requirements->check_xcloner_start_path(1)?"teal":"red")?> lighten-2" >
					<?php echo __('Backup Start Location')?>: <span class="shorten_string "><?php echo $requirements->check_xcloner_start_path();?></span>
				</li>
				<li class="card-panel <?php echo ($requirements->check_xcloner_store_path(1)?"teal":"red")?> lighten-2" >
					<?php echo __('Backup Storage Location')?>: <span class="shorten_string"><?php echo $requirements->check_xcloner_store_path();?></span>
				</li>
				<li class="card-panel <?php echo ($requirements->check_xcloner_tmp_path(1)?"teal":"red")?> lighten-2" >
					<?php echo __('Temporary Location')?>: <span class="shorten_string"><?php echo $requirements->check_xcloner_tmp_path();?></span>
				</li>
				
				<li class="card-panel <?php echo ($requirements->check_min_php_version(1)?"teal":"red")?> lighten-2" >
					<?php echo __('PHP Version Check')?>: <?php echo $requirements->check_min_php_version();?>
					( >= <?php echo $requirements->get_constant('min_php_version')?>)
				</li>
				<li class="card-panel <?php echo ($requirements->check_safe_mode(1)?"teal":"orange")?> lighten-2" >
					<?php echo __('PHP Safe Mode')?>: <?php echo $requirements->check_safe_mode();?>
					( <?php echo $requirements->get_constant('safe_mode')?>)
				</li>
				<li class="card-panel <?php echo ($requirements->check_backup_ready_status()?"teal":"red")?> lighten-2">
					<?php echo ($requirements->check_backup_ready_status()?__('BACKUP READY'):__('Backup not ready, please check above requirements'))?>
					<i class="material-icons right tiny"><?php echo ($requirements->check_backup_ready_status()?'thumb_up':'thumb_down')?></i>
				</li>
		  </ul>
		  <ul class="additional_system_info">
				<li class="card-panel grey darken-1" >
					<?php echo __('PHP max_execution_time')?>: <?php echo $requirements->get_max_execution_time();?>
				</li>
				<li class="card-panel grey darken-1" >
					<?php echo __('PHP memory_limit')?>: <?php echo $requirements->get_memory_limit();?>
				</li>
				<li class="card-panel grey darken-1" >
					<?php echo __('PHP open_basedir')?>: <?php echo $requirements->get_open_basedir();?>
				</li>
				<?php $data = $xcloner_file_system->estimate_read_write_time();?>
				<li class="card-panel grey darken-1" >
					<?php echo __('Reading Time 1MB Block')?>: <?php echo $data['reading_time'];?>
				</li>
				<li class="card-panel grey darken-1" >
					<?php echo __('Writing Time 1MB Block')?>: <?php echo $data['writing_time'];?>
				</li>
				<li class="card-panel grey darken-1" >
					<?php echo __('Free Disk Space')?>: <?php echo $requirements->get_free_disk_space();;?>
				</li>
		  </ul>
		</div>
		<div class="card-action">
		  <a class="waves-effect waves-light btn system_info_toggle blue darken-1"><i class="material-icons left">list</i><?php echo __('Toggle Additional System Info')?></a>
		</div>
	  </div>

	</div>
 </div>
