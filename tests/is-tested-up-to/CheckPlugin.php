<?php



class CheckPlugin {
    /**
     * @var WPReadmeParser
     */
    protected $parser;
    public function __construct(WPReadmeParser $parser) {
        $this->parser = $parser;
    }

    /**
     * @param string $latestWordPressVersion Latest WordPress version
     * @return bool
     */
    public function isStableTagLatestVersion(string $latestWordPressVersion) :bool {
        if (version_compare($latestWordPressVersion, $this->getTestedUpTo()) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Get the version of WordPress plugin is tested up to.
     */
    public function getTestedUpTo(): string {
        return $this->parser->metadata[WPReadmeParser::TESTED_UP_TO];
    }

    /**
     * Get the current version "stable tag" of the plugin.
     */
    public function getStableTag(): string{
        return $this->parser->metadata[WPReadmeParser::STABLE_TAG];
    }

}
