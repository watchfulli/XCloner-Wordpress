<?php

class Xcloner extends watchfulli\XClonerCore\Xcloner{

    public function extra_define_ajax_hooks(){

        $this->loader->add_action('wp_ajax_restore_backup', $this, 'restore_backup');

    }

    public function restore_backup()
    {
        $this->check_access();

        define("XCLONER_PLUGIN_ACCESS", 1);
        include_once(dirname(__DIR__).DS."restore".DS."xcloner_restore.php");

        return;
    }

}
?>