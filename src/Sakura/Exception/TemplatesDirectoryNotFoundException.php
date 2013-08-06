<?php

namespace Sakura\Exception;

class TemplatesDirectoryNotFoundException extends \Exception {
    /** @var string The directory */
    public $directory;

    /** @var string Config file name */
    public $templateDirectoryName;

    /**
     * @param string $directory
     * @param string $templateDirectoryName
     * @param int    $code
     */
    public function __construct($directory, $templateDirectoryName, $code = 0) {
        $this->directory             = $directory;
        $this->templateDirectoryName = $templateDirectoryName;

        parent::__construct('Cannot find readable directory ' . $templateDirectoryName . ' in directory: ' . $directory, $code);
    }
}