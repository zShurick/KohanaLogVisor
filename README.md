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
