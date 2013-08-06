<?php

namespace Sakura\Exception;

class SiteDirectoryInvalidException extends \Exception {
    /** @var string The directory */
    public $directory;

    /** @var string Config file name */
    public $siteDirectoryName;

    /**
     * @param string $directory
     * @param int    $siteDirectoryName
     * @param int    $code
     */
    public function __construct($directory, $siteDirectoryName, $code = 0) {
        $this->directory         = $directory;
        $this->siteDirectoryName = $siteDirectoryName;

        parent::__construct('Directory ' . $directory . ' needs to be writable in order to create the directory ' . $siteDirectoryName, $code);
    }
}