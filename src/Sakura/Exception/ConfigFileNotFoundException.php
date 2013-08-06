<?php

namespace Sakura\Exception;

class ConfigFileNotFoundException extends \Exception {
    /** @var string The directory */
    public $directory;

    /** @var string Config file name */
    public $configFile;

    /**
     * @param string $directory
     * @param string $file
     * @param int    $code
     */
    public function __construct($directory, $file, $code = 0) {
        $this->directory = $directory;
        $this->file      = $file;

        parent::__construct('Cannot find readable ' . $file . ' in directory: ' . $directory, $code);
    }
}