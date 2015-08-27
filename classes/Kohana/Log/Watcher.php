<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Watcher for kohana log files
 * Class Kohana_Log_Watcher
 */
class Kohana_Log_Watcher {

    /**
     * @var Log_Watcher
     */
    private static $instance = null;

    private $check = null;

    public static function instance(){

        if(!isset(self::$instance)) {
            self::$instance = new static();
            self::$instance->check = new Kohana_Log_Watcher_Check();
        }

        return self::$instance;
    }

    protected function __construct(){

        $this->config = Kohana::$config->load('LogWatcher');
    }

}