<?php

namespace Sakura\Exception;

class DirectoryNotFoundException extends \Exception {
    /** @var string The directory */
    public $directory;

    /**
     * @param string $directory
     * @param int    $code
     */
    public function __construct($directory, $code = 0) {
        $this->directory = $directory;

        parent::__construct('Cannot find readable directory: ' . $directory, $code);
    }
}