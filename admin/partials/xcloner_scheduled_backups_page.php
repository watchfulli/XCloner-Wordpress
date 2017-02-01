<?php
$xcloner_scheduler = new Xcloner_Scheduler();

?>

<h1><?= esc_html(get_admin_page_title()); ?></h1>

<div class="row">
<table id="scheduled_backups" class="col s12 display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th><?php echo __('ID', 'xcloner')?></th>
                <th><?php echo __('Schedule Name', 'xcloner')?></th>
                <th><?php echo __('Recurrence', 'xcloner')?></th>
                <!--<th><?php echo __('Start Time', 'xcloner')?></th>-->
                <th><?php echo __('Next Execution', 'xcloner')?></th>
                <th><?php echo __('Status', 'xcloner')?></th>
                <th class="no-sort"><?php echo __('Action', 'xcloner')?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
				<th><?php echo __('ID', 'xcloner')?></th>
                <th><?php echo __('Schedule Name', 'xcloner')?></th>
                <th><?php echo __('Recurrence', 'xcloner')?></th>
                <!--<th><?php echo __('Start Time', 'xcloner')?></th>-->
                <th><?php echo __('Next Execution', 'xcloner')?></th>
                <th><?php echo __('Status', 'xcloner')?></th>
                <th><?php echo __('Action', 'xcloner')?></th>
            </tr>
        </tfoot>
        <tbody>
        <?php
        /*if(is_array($scheduled_backups))
        {
			foreach($scheduled_backups as $schedule)
			{
			?>
			<tr>
				<td><?php echo $schedule->id?></td>
				<td><?php echo $schedule->name?></td>
				<td><?php echo $schedule->start_at?></td>
				<td><?php echo $schedule->recurrence?></td>
				<td><a href="#<?php echo $schedule->id?>" class="edit"> Edit </a>| <a href="#<?php echo $schedule->id?>" class="delete">Delete</a></td>
			</tr>
			<?php
			}
		}*/
        ?>
        </tbody>    
</table>       
</div>

<div class="row">
	<div class="col s12 m6 offset-m6 teal lighten-1" id="server_time">
		<h2><?php echo __('Current Server Time', 'xcloner')?>: <span class="right"><?php echo date("Y/m/d H:i")?></span></h2>
	</div>
</div>


<!-- Modal Structure -->
  <div id="edit_schedule" class="modal">
	<form method="POST" action="" id="save_schedule">
	<input type="hidden" name="id" id="schedule_id_hidden">
	<input type="hidden" name="action" value="save_schedule">
    <div class="modal-content">
      
      <div class="row">
			<div class="col s12 m6">
			  <h4>Edit Schedule #<span id="schedule_id"></span></h4>
			</div>	  
      
			<div class="col s12 m6 right-align">
				<div class="switch">
					<label>
					Off
					<input type="checkbox" id="status" name="status" value="1">
					<span class="lever"></span>
					On
					</label>
				</div>
			</div>	
	  </div>			  

	  <p>	
		  
		<ul class="nav-tab-wrapper content row"> 
			<li> <a href="#scheduler_settings" class="nav-tab col s12 m6 nav-tab-active">Scheduler Settings</a></li>
			<li> <a href="#advanced_scheduler_settings" class="nav-tab col s12 m6">Advanced</a></li>
		</ul>
		
		<div class="nav-tab-wrapper-content">
			<div id="scheduler_settings" class="tab-content active">
			
				<div class="row">
					<div class="input-field col s12 l12">
						<input placeholder="" name="schedule_name" id="schedule_name" type="text" required value="">
						<label for="schedule_name">Schedule Name</label>
					</div>
				</div>
				
				<div class="row">
					<div class="input-field col s12 l6">
						<input placeholder="" name="schedule_start_date" id="schedule_start_date" type="datetime"  value="">
						<label for="schedule_start_date" class="active">Schedule Start At:</label>
					</div>

					<div class="input-field col s12 l6">
						<select name="schedule_frequency" id="schedule_frequency" class="validate" required>
							<option value="" disabled selected><?php echo __('Schedule Recurrence', 'xcloner') ?></option>
							<option value="single">Don't Repeat</option>
							<option value="hourly">Hourly</option>
							<option value="daily">Daily</option>
							<option value="weekly">Weekly</option>
							<option value="monthly">Monthly</option>
						</select>
					</div>
				</div>
				
				<div class="row">
					<div class="input-field col s12 l12">
						<input placeholder="" name="email_notification" id="email_notification" type="text" required value="">
						<label for="email_notification">Email Notification</label>
					</div>
				</div>
			</div>
			
			<div id="advanced_scheduler_settings" class="tab-content">
				<div class="row">
					<div class="input-field col s12 l12">
						<input placeholder="" name="backup_name" id="backup_name" type="text" required value="">
						<label for="backup_name">Backup Name</label>
					</div>
				</div>
				
				<div class="row">	
					<div class="input-field col s12 l12">
						<textarea id="table_params" name="table_params" class="materialize-textarea" rows="15"></textarea>
						<label for="table_params" class="active">Included Database Data</label>
					</div>
				</div>
				
				<div class="row">	
					<div class="input-field col s12 l12">
						<textarea id="excluded_files" name="excluded_files" class="materialize-textarea" rows="15"></textarea>
						<label for="excluded_files" class="active">Exclude Files</label>
					</div>
				</div>
			</div>	
		</div>
		
		<div class="row">
			
			<div class="input-field col s12">
				<button class="right btn waves-effect waves-light" type="submit" name="action">Save
					<i class="material-icons right">send</i>
				</button>
			</div>
		</div>
      </p>
    </div>
    </form>
</div>

