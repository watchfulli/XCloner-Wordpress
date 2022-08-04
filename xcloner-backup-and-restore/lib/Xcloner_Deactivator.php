<?php

namespace Watchfulli\XClonerCore;

use Exception;

class Xcloner_Deactivator
{
    public static function deactivate()
    {

        global $xcloner_plugin;

        if (is_a($xcloner_plugin, 'Xcloner')) {
            try {
                $xcloner_plugin->get_xcloner_filesystem()->cleanup_tmp_directories();
            } catch (Exception $e) {
                $xcloner_plugin->trigger_message_notice($e->getMessage());
            }

            $xcloner_scheduler = $xcloner_plugin->get_xcloner_scheduler();
            $xcloner_scheduler->deactivate_wp_cron_hooks();
        }
    }

}
