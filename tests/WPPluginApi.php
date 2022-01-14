<?php


class WPPluginApi {

    public static function getLatestWordPressVersion(){
        $api = json_decode(file_get_contents('https://api.wordpress.org/core/version-check/1.7/'), true);
        $latestVersion = $api['offers'][0]['current'];
        return $latestVersion;
    }
}
