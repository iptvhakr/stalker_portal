<?php
namespace Lib;

class Base {
    protected $_app;
    protected $_error = null;

    public function __construct($app) {
        $this->_app = $app;
    }

    public function getError() {
        return $this->_error;
    }
}