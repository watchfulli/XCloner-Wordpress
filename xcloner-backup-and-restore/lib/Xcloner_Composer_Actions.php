<?php

namespace Watchfulli\XClonerCore;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Xcloner_Composer_Actions
{
    public static function cleanup($event) {
        \Aws\Script\Composer\Composer::removeUnusedServices($event);
        \Google\Task\Composer::cleanup($event);
    }

    /**
     * Prevent direct access to vendor files in order to avoid security issues
     *
     * This method will loop through all PHP files in the vendor directory and add a check for ABSPATH
     * If ABSPATH is not defined, the script will die
     */
    public static function prevent_vendor_direct_access()
    {
        $vendor_dir = __DIR__ . '/../vendor';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vendor_dir));

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $contents = file_get_contents($file->getPathname());

                $check_to_add = "if (!defined('ABSPATH') && PHP_SAPI !== 'cli') { die(); }";

                if (strpos($contents, $check_to_add) !== false) {
                    continue;
                }

                $contents = preg_replace('/namespace\s+([a-zA-Z0-9_\\\\]+);/m', "namespace $1;\n\n$check_to_add\n", $contents, 1);

                if (
                    strpos($contents, $check_to_add) === false
                )
                {
                    $contents = preg_replace('/namespace\s+([a-zA-Z0-9_\\\\]+)\s*{/', "namespace $1\n{\n$check_to_add", $contents, 1);
                }

                if (
                    strpos($contents, 'declare(strict_types=1);') !== false &&
                    strpos($contents, $check_to_add) === false
                ) {
                    $contents = str_replace(
                        "declare(strict_types=1);",
                        "declare(strict_types=1);\n\n$check_to_add\n",
                        $contents
                    );
                }

                if (strpos($contents, $check_to_add) === false) {
                    $contents = "<?php\n\n$check_to_add\n?>" . $contents;
                }

                file_put_contents($file->getPathname(), $contents);
            }
        }
    }
}
