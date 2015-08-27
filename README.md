# KohanaLogVisor
Tracking error logs in Kohanaframeworks. Every developer in team, can view errors, and fix it for himself. 
Without complicating their lives, with searching mistakes in all logs stack

# Using:
Create Controller like that:

```php
<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Log_Watcher extends Controller_Common {

    public function action_index(){

        if($this->request->post()) {
            Log_Watcher_Write::write_status(
                $this->request->post('path'),
                $this->request->post('position'),
                $this->request->post('line')
            );
        }

        $this->content = Log_Watcher_Read::instance()->view();
    }
}
```

Use route, like that:

```php
Route::set('admin_log_watcher', 'admin/log/watcher')
    ->defaults([
        'directory' => 'Admin/Log',
        'controller' => 'Watcher',
        'action' => 'index',
    ]);
```

And database for multi user support

```mysql
CREATE TABLE `log_watchers` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`path` VARCHAR(255) NOT NULL DEFAULT '0' COMMENT 'last aggregated log file',
	`position` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'position in log file',
	`line` TEXT NOT NULL COMMENT 'last aggregated line (must be relevant with line in $position)',
	`update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Last user check',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `user_id` (`user_id`)
)
COMMENT='Kohanaframework log visor'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
```
