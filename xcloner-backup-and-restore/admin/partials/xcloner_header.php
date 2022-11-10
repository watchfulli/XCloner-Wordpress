<a href="https://www.xcloner.com" target="_blank" title="XCloner.com">
    <img src="<?php echo plugin_dir_url((__DIR__)) ?>/images/xcloner-logo.svg" class="xcloner-logo"
         alt="XCloner backup and restore plugin"/>
</a>
<!-- Dropdown Trigger -->
<h1 class="xcloner-menu dropdown-trigger btn" href="#" data-target="dropdown1">
    <?php echo esc_html(get_admin_page_title()); ?><i class="material-icons">expand_more</i>
</h1>

<!-- Dropdown Structure -->
<ul id="dropdown1" class="xcloner-menu dropdown-content" style="width: 250px">
    <li>
        <a href="<?php echo menu_page_url('xcloner_init_page', false) ?>">
            <i class="material-icons">dashboard</i>
            <?php echo __('Dashboard', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo menu_page_url('xcloner_settings_page', false) ?>">
            <i class="material-icons">settings</i>
            <?php echo __('Settings', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li class="divider" tabindex="-1"></li>
    <li>
        <a href="<?php echo menu_page_url('xcloner_remote_storage_page', false) ?>">
            <i class="material-icons">swap_horiz</i>
            <?php echo __('Storage Locations', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo menu_page_url('xcloner_scheduled_backups_page', false) ?>">
            <i class="material-icons">schedule</i>
            <?php echo __('Schedules & Profiles', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo menu_page_url('xcloner_manage_backups_page', false) ?>">
            <i class="material-icons">archive</i>
            <?php echo __('Manage Backups', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo menu_page_url('xcloner_generate_backups_page', false) ?>">
            <i class="material-icons">create</i>
            <?php echo __('Generate Backups', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo menu_page_url('xcloner_restore_page', false) ?>">
            <i class="material-icons">restore</i>
            <?php echo __('Restore Site', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo menu_page_url('xcloner_restore_page', false) ?>">
            <i class="material-icons">restore</i>
            <?php echo __('Clone Site', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
</ul>

<script>
  jQuery(".dropdown-trigger").dropdown({
    constrainWidth: true
  });
</script>
