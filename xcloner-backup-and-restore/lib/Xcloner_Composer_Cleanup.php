<?php

namespace Watchfulli\XClonerCore;

class Xcloner_Composer_Cleanup
{
    public static function cleanup($event) {
        \Aws\Script\Composer\Composer::removeUnusedServices($event);
        \Google\Task\Composer::cleanup($event);
    }
}
