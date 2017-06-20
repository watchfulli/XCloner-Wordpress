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

$requirements	 		= $this->get_xcloner_container()->get_xcloner_requirements();
$xcloner_settings 		= $this->get_xcloner_container()->get_xcloner_settings();
$xcloner_file_system 	= $this->get_xcloner_container()->get_xcloner_filesystem();
$logger					= $this->get_xcloner_container()->get_xcloner_logger();
$xcloner_scheduler 		= $this->get_xcloner_container()->get_xcloner_scheduler();

$logger_content = $logger->getLastDebugLines();

$date_format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );

if($requirements->check_backup_ready_status())
{
	$latest_backup =  $xcloner_file_system->get_latest_backup();
	$xcloner_file_system->backup_storage_cleanup();
}
?>

<div class="row">
	<div class="col s12">
		<h5 class="left-align">
			<?php echo __('Backup Dashboard', 'xcloner-backup-and-restore') ?>
		</h5>
	</div>
</div>

<?php if(isset($latest_backup['timestamp']) and $latest_backup['timestamp'] < strtotime("-1 day")): ?>
	<div id="setting-error-" class="error settings-error notice is-dismissible"> 
		<p><strong>
			<?php echo __('Your latest backup is older than 24 hours, please create a new backup to keep your site protected.', 'xcloner-backup-and-restore') ?>
			</strong>
		</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>
<?php endif?>

<?php if(!isset($latest_backup['timestamp']) ): ?>
	<div id="setting-error-" class="error settings-error notice is-dismissible"> 
		<p><strong>
			<?php echo __('You have no backup that I could find, please generate a new backup to keep your site protected.', 'xcloner-backup-and-restore') ?>
			</strong>
		</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>
<?php endif?>

<?php if(!$requirements->check_backup_ready_status()):?>
	<div id="setting-error-" class="error settings-error notice is-dismissible"> 
		<p><strong>
			<?php echo __('Backup system not ready, please check and fix the issues marked in red', 'xcloner-backup-and-restore') ?>
			</strong>
		</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>
	
<?php endif ?>


				
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="row dashboard">
	<div class="col s12 m12 l7">
			
		<div class="row">
			
			<ul class="collapsible xcloner-debugger" data-collapsible="accordion">
				
				<li class="active">
					<div class="collapsible-header active"><i class="material-icons">info</i>Backup Status</div>
					<div class="collapsible-body">
						<div class="" id="backup-status">
							<div class="row">
								<h5><?php echo __("Latest Backup", 'xcloner-backup-and-restore')?></h5>
								<blockquote>
								<?php if($latest_backup):?>
									<div class="item">
										<div class="title"><?php echo __("Backup Name", 'xcloner-backup-and-restore')?>:</div>
										<?php echo $latest_backup['basename']?>
									</div> 	
									<div class="item">
										<div class="title">
											<?php echo __("Backup Size", 'xcloner-backup-and-restore')?>:
										</div>	
										<?php echo size_format($latest_backup['size'])?>
									</div> 	
									<div class="item">
										<div class="title"><?php echo __("Backup Date", 'xcloner-backup-and-restore')?>:</div>
										<?php 
										echo date($date_format." ".$time_format, $latest_backup['timestamp']+(get_option( 'gmt_offset' ) * HOUR_IN_SECONDS))
										?>
									</div> 
								<?php else:?>
									<div class="item">
										<div class="title"><?php echo __("No Backup Yet", 'xcloner-backup-and-restore')?></div>
									</div> 
								<?php endif?>
								</blockquote>
							<div>
							<h5><?php echo __("Backup Storage Usage", 'xcloner-backup-and-restore')?></h5>
								<blockquote>
								<div class="item">
									<div class="title"><?php echo __("Total Size", 'xcloner-backup-and-restore')?>:</div>
									<?php echo size_format($xcloner_file_system->get_storage_usage());?>
								</div>
								</blockquote>
							<h5><?php echo __("Next Scheduled Backup", 'xcloner-backup-and-restore')?></h5>
								<blockquote>
								<div class="item">
									<?php
									$list = ($xcloner_scheduler->get_next_run_schedule());
										
										if(is_array($list))
										{
											$xcloner_file_system->sort_by($list, "next_run_time","asc");
										}
										
										if(isset($list[0]))
											$latest_schedule = $list[0];
									?>
									<?php if(isset($latest_schedule->name)):?>
									<div class="title"><?php echo __("Schedule Name", 'xcloner-backup-and-restore')?>:</div>
										<?php	echo $latest_schedule->name;?>
									<?php endif;?>	
								</div>
								<div class="item">
									<div class="title"><?php echo __("Next Call", 'xcloner-backup-and-restore')?>:</div>
									<?php if(isset($latest_schedule->next_run_time))	
											echo date($date_format." ".$time_format, $latest_schedule->next_run_time);
										  else
											echo __("Unscheduled",'xcloner-backup-and-restore');
									?>
								</div>
								</blockquote>
						</div>
					</div>
				</li>
				
				<?php if($xcloner_settings->get_xcloner_option('xcloner_enable_log')) :?>
				<li class="active">
					<div class="collapsible-header active">
						<i class="material-icons">bug_report</i><?php echo __('XCloner Debugger', 'xcloner-backup-and-restore')?>
						<div class="right">
							<a href="#<?php echo $logger_basename = basename($logger->get_main_logger_url())?>" class="download-logger" title="<?php echo $logger_basename?>">
								<span class="shorten_string"><?php echo $logger_basename?>&nbsp;&nbsp;&nbsp;</span>
							</a>
						</div>
					</div>
					<div class="collapsible-body">
						<div class="console" id="xcloner-console"><?php if($logger_content) echo implode("<br />\n", array_reverse($logger_content)); ?></div>
					</div>
				</li>
				<script>
				jQuery(document).ready(function(){
					var objDiv = document.getElementById("xcloner-console");
					objDiv.scrollTop = objDiv.scrollHeight;
					/*setInterval(function(){
						getXclonerLog();
					}, 2000);*/
				})
				</script>
				<?php endif;?>
			
			</ul>
		
		</div>
	
	
	</div>
	<div class="col s12 m12 l5">
	  
	  <div class="card blue-grey darken-1 z-depth-4 backup-ready">
		<div class="card-content white-text">
		  <span class="card-title"><?php echo __("System Check",'xcloner-backup-and-restore')?></span>
		  <ul>
				<li class="card-panel <?php echo ($requirements->check_xcloner_start_path(1)?"teal":"red")?> lighten-2" >
					<?php echo __('Backup Start Location','xcloner-backup-and-restore')?>: <span class="shorten_string "><?php echo $requirements->check_xcloner_start_path();?></span>
				</li>
				<li class="card-panel <?php echo ($requirements->check_xcloner_store_path(1)?"teal":"red")?> lighten-2" >
					<?php echo __('Backup Storage Location','xcloner-backup-and-restore')?>: <span class="shorten_string"><?php echo $requirements->check_xcloner_store_path();?></span>
				</li>
				<li class="card-panel <?php echo ($requirements->check_xcloner_tmp_path(1)?"teal":"red")?> lighten-2" >
					<?php echo __('Temporary Location','xcloner-backup-and-restore')?>: <span class="shorten_string"><?php echo $requirements->check_xcloner_tmp_path();?></span>
				</li>
				
				<li class="card-panel <?php echo ($requirements->check_min_php_version(1)?"teal":"red")?> lighten-2" >
					<?php echo __('PHP Version Check','xcloner-backup-and-restore')?>: <?php echo $requirements->check_min_php_version();?>
					( >= <?php echo $requirements->get_constant('min_php_version')?>)
				</li>
				<li class="card-panel <?php echo ($requirements->check_safe_mode(1)?"teal":"orange")?> lighten-2" >
					<?php echo __('PHP Safe Mode','xcloner-backup-and-restore')?>: <?php echo $requirements->check_safe_mode();?>
					( <?php echo $requirements->get_constant('safe_mode')?>)
				</li>
				<li class="card-panel <?php echo ($requirements->check_backup_ready_status()?"teal":"red")?> lighten-2">
					<?php echo ($requirements->check_backup_ready_status()?__('BACKUP READY','xcloner-backup-and-restore'):__('Backup not ready, please check above requirements','xcloner-backup-and-restore'))?>
					<i class="material-icons right tiny"><?php echo ($requirements->check_backup_ready_status()?'thumb_up':'thumb_down')?></i>
				</li>
		  </ul>
		  <ul class="additional_system_info">
				<li class="card-panel grey darken-1" >
					<?php echo __('PHP max_execution_time','xcloner-backup-and-restore')?>: <?php echo $requirements->get_max_execution_time();?>
				</li>
				<li class="card-panel grey darken-1" >
					<?php echo __('PHP memory_limit','xcloner-backup-and-restore')?>: <?php echo $requirements->get_memory_limit();?>
				</li>
				<li class="card-panel grey darken-1" >
					<?php echo __('PHP open_basedir','xcloner-backup-and-restore')?>: <?php echo $requirements->get_open_basedir();?>
				</li>
				<?php 
				$data = array();
				if($requirements->check_backup_ready_status())
					$data = $xcloner_file_system->estimate_read_write_time();
				?>
				<li class="card-panel grey darken-1" >
					<?php echo __('Reading Time 1MB Block','xcloner-backup-and-restore')?>: <?php echo (isset($data['reading_time'])?$data['reading_time']:__("unknown"));?>
				</li>
				<li class="card-panel grey darken-1" >
					<?php echo __('Writing Time 1MB Block','xcloner-backup-and-restore')?>: <?php echo (isset($data['writing_time'])?$data['writing_time']:__("unknown"));?>
				</li>
				<li class="card-panel grey darken-1" >
					<?php echo __('Free Disk Space','xcloner-backup-and-restore')?>: <?php echo $requirements->get_free_disk_space();;?>
				</li>
		  </ul>
		</div>
		<div class="card-action">
		  <a class="waves-effect waves-light btn system_info_toggle blue darken-1"><i class="material-icons left">list</i><?php echo __('Toggle Additional System Info','xcloner-backup-and-restore')?></a>
		</div>
	  </div>

	</div>
 </div>
