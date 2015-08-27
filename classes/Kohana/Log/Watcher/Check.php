<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Log_Watcher_Check extends Log_Watcher
{

    protected function __construct()
    {

        parent::__construct();

        $this->check_config_path('logs', 'read', 'dir');
    }

    /**
     * Check folders, access rights, config structure
     * @param string $key - "LogWatcher" config array key
     * @param string $permission - read/write
     * @throws ErrorException
     */
    private function check_config_path($key = '', $permission = 'read', $type = 'dir')
    {

        if (!isset($this->config[$key]))
            throw new ErrorException('Incorrect Log_Watcher config, must have "' . $key . '" param');

        if (!file_exists($this->config[$key]))
            throw new ErrorException('Incorrect Log_Watcher config, file "' . $this->config[$key] . '" does not exists');

        if ($type == 'dir' && !is_dir($this->config[$key]))
            throw new ErrorException('Incorrect Log_Watcher config,"' . $this->config[$key] . '" not a folder');

        if ($permission == 'read')
            if (!is_readable($this->config[$key]))
                throw new ErrorException('Incorrect Log_Watcher config,"' . $this->config[$key] . '" permission denied [must be readable]');

            elseif ($permission == 'write')
                if (!is_writeable($this->config[$key]))
                    throw new ErrorException('Incorrect Log_Watcher config, "' . $this->config[$key] . '" permission denied [must be writeable]');
    }

}