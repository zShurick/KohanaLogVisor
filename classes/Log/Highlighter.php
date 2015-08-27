<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Class Model_Log_Highlighter
 * Подсветка синтаксиса логов (вручную, для мест, где это необходимо)
 */
class Log_Highlighter {

    /**
     * Подсветка логов из плат. систем
     * @param $str
     * @return mixed
     */
    private static function payments($str){

        $pay_system = array_keys(Kohana::$config->load('payment')->as_array());

        $error_type = array('Callback');

        $str = str_replace('Payments', '<span style="color: #696969;font-weight: normal">Payments</span>', $str);
        $str = preg_replace('/('.implode(' |',$pay_system).' )/', '<span style="color: #00008b; font-weight: bold">$1</span>', $str);
        $str = preg_replace('/('.implode('|',$error_type).')/', '<span style="color: green; font-weight: bold">$1</span>', $str);

        return $str;
    }

    public static function debug($str=''){

        $str = str_replace(',.,', '<br>', $str);
        $str = preg_replace('/(#\S+)/', '<span style="color: #00008b;">$1</span>', $str);
        $str = preg_replace('/(\/home\/\S+)/', '<span style="font-weight: bold">$1</span>', $str);

        return $str;
    }

    public static function hl($str=''){

        $str = trim($str);
        if(strpos($str, 'Payments')==0) $str = self::payments($str);

        return nl2br($str);
    }
}