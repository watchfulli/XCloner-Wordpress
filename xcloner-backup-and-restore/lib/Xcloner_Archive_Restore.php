<?php

namespace Watchfulli\XClonerCore;

class Xcloner_Archive_Restore extends Xcloner_Archive
{
    public function __construct($archive_name = "") {
        if (isset($archive_name) && $archive_name) {
            $this->set_archive_name($archive_name);
        }
    }
}
