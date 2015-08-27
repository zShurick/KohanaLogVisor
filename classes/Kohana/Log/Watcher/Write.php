<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Log_Watcher_Write extends Log_Watcher
{

    /**
     * @param string $path
     * @param int $position
     * @param string $line
     */
    public static function write_status($path = '', $position = 0, $line = '')
    {

        $log = ORM::factory('Log_Watcher', ['user_id' => Auth::instance()->get_user()->pk()]);
        try {
            $log
                ->set('user_id', Auth::instance()->get_user()->pk())
                ->set('path', $path)
                ->set('line', $line)
                ->set('position', $position)
                ->save();

        } catch (ORM_Validation_Exception $e) {

            Log::instance()->orm_exception($e, 'Log_Watcher_Read не удалось записать log_watcher позицию');
        }
    }

}