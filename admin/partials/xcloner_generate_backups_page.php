<?php 
$xcloner_settings = new Xcloner_Settings();
?>
<h1><?= esc_html(get_admin_page_title()); ?></h1>
         
<ul class="nav-tab-wrapper content row">
	<li><a href="#backup_options" class="nav-tab col s12 m3 l2 nav-tab-active"><?php echo __('1. Backup Options')?></a></li>
	<li><a href="#database_options" class="nav-tab col s12 m3 l2 "><?php echo __('2. Database Options')?></a></li>
	<li><a href="#files_options" class="nav-tab col s12 m3 l2 "><?php echo __('3. Files Options')?></a></li>
	<li><a href="#generate_backup" class="nav-tab col s12 m3 l2 "><?php echo __('4. Generate Backup')?></a></li>
</ul>

<form action="" method="POST">
	<div class="nav-tab-wrapper-content">
		<!-- Backup Options Content Tab-->
		<div id="backup_options" class="tab-content active">
			<div class="row">
		        <div class="input-field inline col s12 m10 l6">
					<i class="material-icons prefix">input</i>
					<input id="backup_name" type="text" value=<?php echo $xcloner_settings->get_default_backup_name() ?> >
					<label for="backup_name"><?php echo __('Backup Name')?></label>
				</div>
				<div class="hide-on-small-only m2">
					<a class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-tooltip="<?php echo __('The default backup name')?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
				</div>
		     </div>
		     
		     <div class="row">
		        <div class="input-field inline col s12 m10 l6">
					<i class="material-icons prefix">input</i>
					<input id="email_notification" type="text" value="<?php echo get_option('admin_email');?>" >
					<label for="email_notification"><?php echo __('Send Email Notification To')?></label>
				</div>
				<div class="hide-on-small-only m2">
					<a class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-tooltip="<?php echo __('If left blank, no notification will be sent')?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
				</div>
		     </div>
		     
		     <div class="row">
				<div class="input-field col s12 m10 l6">
					<i class="material-icons prefix">input</i>
					<textarea id="backup_comments" class="materialize-textarea"></textarea>
					<label for="backup_comments"><?php echo __('Backup Comments')?></label>
				</div>
				<div class="hide-on-small-only m2">
					<a class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-tooltip="<?php echo __('Some default backup comments that will be stored inside the backup archive')?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
				</div>
		     </div>
		     
		     <div class="row">
					<div class="input-field col s12 m10 l6 right-align">
						<a class="waves-effect waves-light btn" onclick="next_tab('#database_options');"><i class="material-icons right">skip_next</i>Next</a>
					</div>
			 </div>
		</div>
		
		<div id="database_options" class="tab-content">
			<h2><?php echo __('Select database data to include in the backup')?>:
			<a class="btn-floating tooltipped btn-small" data-position="right" data-delay="50" data-tooltip="<?php echo __('Disable the \'Backup only WP tables\' setting if you don\'t want to show all other databases and tables not related to this Wordpress install');?>" data-tooltip-id=""><i class="material-icons">help_outline</i></a>
			</h2>
			<div id="jstree_database_container">
			<!-- database/tables tree -->
		    </div>
		</div>
		
		<div id="files_options" class="tab-content">
			<div id="jstree_files_container">
			<!-- database/tables tree -->
		    </div>
		</div>
		<div id="generate_backup" class="tab-content">
			<div class="row center">
				<a class="waves-effect waves-light btn-large red darken-1" onclick="start_backup()">Start Backup<i class="material-icons left">forward</i></a>
			</div>
		</div>
	</div>
</form>

<!-- Modal Structure -->
  <div id="error_modal" class="modal">
    <div class="modal-content">
      <h4 class="title_line"><span class="title"></span></h4>
      <h5 class="title_line">Message: <span class="msg"></span></h5>
	  <h5>Response: </h5>
	  <textarea  class="body" rows="5"></textarea>
    </div>
    <div class="modal-footer">
      <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Close</a>
    </div>
  </div>

  
<script>
jQuery(function () { 
	
	jQuery('#jstree_database_container').jstree({
			'core' : {
				'check_callback' : true,
				'data' : {
					'method': 'POST',
					'dataType': 'json',
					'url' : ajaxurl,
					'data' : function (node) {
								var data = { 
									'action': 'get_database_tables_action',
									'id' : node.id
									}
								return data;
							}
				},		
					
			'error' : function (err) { 
				//alert("We have encountered a communication error with the server, please review the javascript console."); 
				show_ajax_error("Error loading database structure ", err.reason, err.data);
				},
			 
			'strings' : { 'Loading ...' : 'Loading the database structure...' },			
			'themes' : {
					"variant" : "default"
				},
			},
			'checkbox': {
				  three_state: true
			},
			'plugins' : [
					"checkbox",
					"massload",
					"search",
					//"sort",
					//"state",
					"types",
					"unique",
				]
		});
});


function start_backup()
{
		jQuery.each(jQuery("#jstree_database_container").jstree("get_checked",true),function(){console.log(this.id+"-"+this.parent);});

}

function show_ajax_error(title, msg, body){
	console.log(title+""+body);
	jQuery("#error_modal .title").text(title);
	jQuery("#error_modal .msg").text(msg);
	jQuery("#error_modal .body").text(body);
	var error_modal = jQuery("#error_modal").modal();
	error_modal.modal('open');
	}
</script>
