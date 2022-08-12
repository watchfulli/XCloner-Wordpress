<?php

$xcloner_settings = $this->get_xcloner_container()->get_xcloner_settings();
$logger           = $this->get_xcloner_container()->get_xcloner_logger();
$logger_content   = $logger->getLastDebugLines();
?>
<div class="col s12 ">
    <div>
        <h5 class="left-align">
            <?php echo __('XCloner Debugger Dashboard', 'xcloner-backup-and-restore') ?>
        </h5>

        <?php if ($xcloner_settings->get_xcloner_option('xcloner_enable_log')) : ?>
        <ul class="collapsible xcloner-debugger" data-collapsible="accordion">
            <li class="active">
                <div class="collapsible-header active"><i class="material-icons">bug_report</i>XCloner Debugger
                </div>
                <div class="collapsible-body">
                    <div class="console" id="xcloner-console">
                        <?php
                        if (isset($logger_content)) {
								echo implode("<br />\n", esc_html($logger_content));
							}
                        ?>
                    </div>
                </div>
            </li>
        </ul>
        <script>
            jQuery(document).ready(function () {
                var objDiv = document.getElementById("xcloner-console");
                objDiv.scrollTop = objDiv.scrollHeight;
            })
        </script>
        <?php endif; ?>
    </div>
</div>
