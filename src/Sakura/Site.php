<?php

namespace Sakura;

/**
 * Class Site
 *
 * @package Sakura
 */
class Site extends \stdClass {
    /**
     * Create Site instance with properties
     *
     * @param array $properties
     */
    public function __construct($properties = array()) {
        foreach($properties as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function url($url) {
        return $url;
    }
}