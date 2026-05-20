<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<a href="https://www.xcloner.com" target="_blank" title="XCloner.com">
    <img src="<?php echo esc_url(plugin_dir_url((__DIR__))) ?>/images/xcloner-logo.svg" class="xcloner-logo"
         alt="XCloner backup and restore plugin"/>
</a>
<!-- Dropdown Trigger -->
<h1 class="xcloner-menu dropdown-trigger btn" href="#" data-target="dropdown1">
    <?php echo esc_html(get_admin_page_title()); ?><i class="material-icons">expand_more</i>
</h1>

<!-- Dropdown Structure -->
<ul id="dropdown1" class="xcloner-menu dropdown-content" style="width: 250px">
    <li>
        <a href="<?php echo esc_url(menu_page_url('xcloner_init_page', false)) ?>">
            <i class="material-icons">dashboard</i>
            <?php echo esc_html__('Dashboard', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo esc_url(menu_page_url('xcloner_settings_page', false)) ?>">
            <i class="material-icons">settings</i>
            <?php echo esc_html__('Settings', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li class="divider" tabindex="-1"></li>
    <li>
        <a href="<?php echo esc_url(menu_page_url('xcloner_remote_storage_page', false)) ?>">
            <i class="material-icons">swap_horiz</i>
            <?php echo esc_html__('Storage Locations', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo esc_url(menu_page_url('xcloner_scheduled_backups_page', false)) ?>">
            <i class="material-icons">schedule</i>
            <?php echo esc_html__('Schedules & Profiles', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo esc_url(menu_page_url('xcloner_manage_backups_page', false)) ?>">
            <i class="material-icons">archive</i>
            <?php echo esc_html__('Manage Backups', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo esc_url(menu_page_url('xcloner_generate_backups_page', false)) ?>">
            <i class="material-icons">create</i>
            <?php echo esc_html__('Generate Backups', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo esc_url(menu_page_url('xcloner_restore_site_page', false)) ?>">
            <i class="material-icons">restore</i>
            <?php echo esc_html__('Restore Site', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
    <li>
        <a href="<?php echo esc_url(menu_page_url('xcloner_clone_site_page', false)) ?>">
            <i class="material-icons">restore</i>
            <?php echo esc_html__('Clone Site', 'xcloner-backup-and-restore') ?>
        </a>
    </li>
</ul>

<script>
  jQuery(".dropdown-trigger").dropdown({
    constrainWidth: true
  });
</script>
