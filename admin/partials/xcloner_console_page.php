<?php

$xcloner_settings 		= new Xcloner_Settings();
$logger				= new Xcloner_Logger();


$xcloner_scheduler = new Xcloner_Scheduler();
$xcloner_scheduler->xcloner_scheduler_callback(90);

$logger_content = $logger->getLastDebugLines();

?>
<div class="col s12 ">
	<div>
		<h5 class="left-align">
				<?php echo __('XCloner Debugger Dashboard', 'xcloner') ?>
		</h5>
		
		<?php if($xcloner_settings->get_xcloner_option('xcloner_enable_log')) :?>
		<ul class="collapsible xcloner-debugger" data-collapsible="accordion">
			<li class="active">
				<div class="collapsible-header active"><i class="material-icons">bug_report</i>XCloner Debugger</div>
				<div class="collapsible-body">
					<div class="console" id="xcloner-console"><?php if($logger_content) echo implode("<br />\n", $logger_content); ?></div>
				</div>
			</li>
		</ul>
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
	</div>
</div>
