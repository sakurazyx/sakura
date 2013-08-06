<?php

namespace Sakura\Exception;

class ContentDirectoryNotFoundException extends \Exception {
    /** @var string The directory */
    public $directory;

    /** @var string Config file name */
    public $contentDirectoryName;

    /**
     * @param string $directory
     * @param string $contentDirectoryName
     * @param int    $code
     */
    public function __construct($directory, $contentDirectoryName, $code = 0) {
        $this->directory            = $directory;
        $this->contentDirectoryName = $contentDirectoryName;

        parent::__construct('Cannot find readable directory ' . $contentDirectoryName . ' in directory: ' . $directory, $code);
    }
}