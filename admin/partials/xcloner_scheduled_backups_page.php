<?php
$xcloner_scheduler = new Xcloner_Scheduler();

$scheduled_backups = $xcloner_scheduler->get_scheduler_list();

?>
<h1><?= esc_html(get_admin_page_title()); ?></h1>

<div class="row">
<table id="scheduled_backups" class="col s12 display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th><?php echo __('ID #', 'xcloner')?></th>
                <th><?php echo __('Schedule Name', 'xcloner')?></th>
                <th><?php echo __('Start Time', 'xcloner')?></th>
                <th><?php echo __('Recurrence', 'xcloner')?></th>
                <th><?php echo __('Action', 'xcloner')?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
				<th><?php echo __('ID #', 'xcloner')?></th>
                <th><?php echo __('Schedule Name', 'xcloner')?></th>
                <th><?php echo __('Start Time', 'xcloner')?></th>
                <th><?php echo __('Recurrence', 'xcloner')?></th>
                <th><?php echo __('Action', 'xcloner')?></th>
            </tr>
        </tfoot>
        <tbody>
        <?php
        if(is_array($scheduled_backups))
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
		}
        ?>
        </tbody>    
</table>       
</div>


<!-- Modal Structure -->
  <div id="edit_schedule" class="modal">
	<form method="POST" action="" id="save_schedule">
	<input type="hidden" name="id" id="schedule_id_hidden">
	<input type="hidden" name="action" value="save_schedule">
    <div class="modal-content">
      <h4>Edit Schedule #<span id="schedule_id"></span></h4>
      <p>&nbsp;<p>
	  <p>	
		<div class="row">
			<div class="input-field col s12 l12">
				<input placeholder="" name="schedule_name" id="schedule_name" type="text" required value="">
				<label for="schedule_name">Schedule Name</label>
			</div>
		</div>
		
		<div class="row">
			<div class="input-field col s12 l6">
				<input placeholder="" name="start_at" id="start_at" type="text" required value="">
				<label for="start_at">Schedule Start At:</label>
			</div>
		
			<div class="input-field col s12 l6">
				<select name="schedule_frequency" id="schedule_frequency" class="validate" required>
					<option value="" disabled selected><?php echo __('Recurrence Schedule', 'xcloner') ?></option>
					<option value="one_time">One time</option>
					<option value="hourly">Hourly</option>
					<option value="daily">Daily</option>
					<option value="weekly">Weekly</option>
					<option value="monthly">Monthly</option>
				</select>
			</div>
		</div>
		
		<div class="row">	
			<div class="input-field col s12 l12">
				<textarea id="params" name="data" class="materialize-textarea" rows="15"></textarea>
				<label for="params">Schedule Params</label>
			</div>
		</div>
		
		<div class="row">
			<div class="input-field col s12 l12">
				<button class="right btn waves-effect waves-light" type="submit" name="action">Save
					<i class="material-icons right">send</i>
				</button>
			</div>
		</div>
      </p>
    </div>
    </form>
</div>

<script>
jQuery(document).ready(function() {
	
	
		
} );
</script>
