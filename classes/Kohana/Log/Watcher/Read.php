<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Log_Watcher_Read extends Log_Watcher
{

    /**
     * Последний проверенный Kohana log file
     * @var string
     */
    private $last_file = '';
    /**
     * Позиция в последнем логе, которую пометили, как проверенно
     * @var int
     */
    private $last_position = 0;
    /**
     * Последняя обработанная строка
     * @var int
     */
    private $last_line = 0;

    /**
     * Счетчик ошибок
     * @var int
     */
    private $error_count = 0;

    /**
     * Собранные логи, подготовленные для вывода
     * @var array
     */
    private $aggregate_logs = [];

    /**
     * HTML собранных строк из лога коханы
     * @return string
     */
    public function view()
    {
        $this->aggregate();

        return View::factory('Log/watcher', [
            'last_file' => str_replace(APPPATH, '', $this->last_file),
            'last_line' => $this->last_line($this->last_line),
            'last_position' => $this->last_position,
            'aggregate_logs' => $this->aggregate_logs,
            'error_count' => $this->error_count,
        ]);
    }

    /**
     * Генерирование отчета по логам
     */
    private function aggregate()
    {
        $this->init_position();
        $this->read_all_logs();
    }

    /**
     * Считаем от последнего просмотренного лог-файла, есть ли за следующее число?
     * сбор информации по всем логам от последнего проверенного
     * @throws ErrorException
     */
    private function read_all_logs()
    {
        while ($this->read_log() !== FALSE) {

            $matches = array();
            if (preg_match('/' . str_replace('/', '\/', $this->config['logs']) . '\/([\d]+)\/([\d]+)\/([\d]+)\.php/', $this->last_file, $matches) == 0)
                throw new ErrorException('Undefined logs path format: ' . $this->last_file);

            $new_date = mktime(0, 0, 0, $matches[2], $matches[3] + 1, $matches[1]);
            if ($new_date <= time()) {
                $this->last_file = $this->config['logs'] . '/' . date('Y', $new_date) . '/' . date('m', $new_date) . '/' . date('d', $new_date) . '.php';
                $this->last_position = 0;
                $this->last_line = '';
                continue;
            } else break;
        }
    }

    private function init_position()
    {
        $user_id = Auth::instance()->get_user()->pk();

        // from model
        $log_watcher = ORM::factory('Log_Watcher', ['user_id' => $user_id]);

        // doesn't have model, create
        if ($log_watcher->loaded()) {
            $this->last_file = APPPATH.$log_watcher->path;
            $this->last_line = $log_watcher->line;
            $this->last_position = $log_watcher->position;
        } else {

            $this->last_file = $this->find_last_log();

            $last_file = str_replace(APPPATH, '', $this->last_file);
            Log_Watcher_Write::write_status($last_file, $this->last_position, $this->last_line);
        }
    }

    /**
     * От текущей даты, -5 дней пытаемся найти последний лог коханы (за раньше, думаю, не надо)
     * @return string
     */
    private function find_last_log()
    {
        $path = '';
        $time = time();

        for ($i = 0; $i < 5; $i++) {
            $day = date('d', $time);
            $month = date('m', $time);
            $year = date('Y', $time);

            $_path = $this->config['logs'] . '/' . $year . '/' . $month . '/' . $day . '.php';

            if (!file_exists($_path))
                $time = mktime(0, 0, 0, $month, $day - 1, $year);
            else {
                $path = $_path;
                break;
            }
        }
        return $path;
    }

    /**
     * Обработка строки из лога
     * @param string $line
     * @return string
     */
    private function last_line($line = '')
    {
        return trim(str_replace('$', '\$', str_replace('"', '\'', trim($line, '\\'))));
    }

    /**
     * Когда определили лог файл, считываем данные по строчно
     * @return bool
     */
    private function read_log()
    {

        if (!file_exists($this->last_file)) return true;

        $hLog = fopen($this->last_file, 'r');

        if ($this->last_position) {

            /////// get log file end position
            fseek($hLog, 0, SEEK_END);
            $eof_pos = ftell($hLog);

            if ($eof_pos >= $this->last_position) {
                /////// position in file
                fseek($hLog, $this->last_position);

                $log_file_str = fgets($hLog);

                $this->last_line = $this->last_line($this->last_line);
                $log_file_str = $this->last_line($log_file_str);
                if (strcmp(trim($log_file_str), $this->last_line) != 0 || !trim($log_file_str) || !trim($this->last_line))
                    fseek($hLog, 0);
            } else
                fseek($hLog, 0);
        }


        $prev_type = 'DEBUG';
        $prev_error = '';

        $next_position = $this->last_position;
        // читаю по строке
        while ($line = fgets($hLog)) {

            if (!empty($line)) {
                $this->last_line = $line;
                $this->last_position = $next_position;
                $next_position = ftell($hLog);
            }

            // первая строка ошибки
            if (preg_match("/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) --- ([A-Z]+):(.+)/", $line, $matches) == 1) {
                // если дебаг после ошибки - это описание ошибки
                if ($matches[2] == 'DEBUG' && $prev_type != 'DEBUG'
                    && strpos($matches[3], '#0') !== FALSE
                ) {

                    if (!isset($this->aggregate_logs[$prev_type][$prev_error]['debug'])) {

                        $this->aggregate_logs[$prev_type][$prev_error]['debug'] = array();
                        $this->aggregate_logs[$prev_type][$prev_error]['debug'][] = $matches[3];

                        $new_str_pos = ftell($hLog);
                        while ($line_debug = fgets($hLog)) {

                            if (empty($line_debug)) continue;

                            $this->last_line = $line_debug;
                            $this->last_position = $next_position;
                            $next_position = ftell($hLog);

                            if (preg_match("/^(#.+)/", $line_debug, $matches) == 1) {
                                $new_str_pos = ftell($hLog);
                                $this->aggregate_logs[$prev_type][$prev_error]['debug'][] = $matches[1];
                            } else {
                                $next_position = $new_str_pos;
                                fseek($hLog, $new_str_pos);
                                break;
                            }
                        }
                    }
                    continue;
                } else {
                    if (!isset($this->aggregate_logs[$matches[2]]))
                        $this->aggregate_logs[$matches[2]] = array();
                    if (!isset($this->aggregate_logs[$matches[2]][$matches[3]]))
                        $this->aggregate_logs[$matches[2]][$matches[3]] = array();
                    if (!isset($this->aggregate_logs[$matches[2]][$matches[3]]['time']))
                        $this->aggregate_logs[$matches[2]][$matches[3]]['time'] = array();

                    $this->aggregate_logs[$matches[2]][$matches[3]]['time'][] = $matches[1];
                    $prev_type = $matches[2];
                    $prev_error = $matches[3];

                    $this->error_count++;
                }

            } else {

                // если это не описание дебага
                if ($line[0] == '#' && preg_match('[0-9]', $line[1]) !== FALSE) continue;

                // строка не соответсвует рег. выражению - просто добавляем ее в сообщение об ошибке
                $buff_key = $prev_error;
                if (!isset($this->aggregate_logs[$prev_type])) $this->aggregate_logs[$prev_type] = array();
                if (!isset($this->aggregate_logs[$prev_type][$buff_key]))
                    $this->aggregate_logs[$prev_type][$buff_key] = array();
                $buff = $this->aggregate_logs[$prev_type][$buff_key];

                $prev_error .= $line;
                unset($this->aggregate_logs[$prev_type][$buff_key]);
                $this->aggregate_logs[$prev_type][$prev_error] = $buff;

            } //preg match
        } //while fgets

        fclose($hLog);

        return true;
    }
}