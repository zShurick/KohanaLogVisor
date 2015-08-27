<?php defined('SYSPATH') or die('No direct script access.');

class Model_Log_Watcher extends ORM
{

    protected $_table_columns = [
        'id' => '',
        'user_id' => '',
        'path' => '',
        'position' => '',
        'line' => '',
        'update_date' => '',
    ];

    protected $_belongs_to = [
        'user' => ['model' => 'User'],
    ];


    public function rules(){
        return [
            'user_id' => [
                ['not_empty'],
            ],
            'path' => [
                ['not_empty'],
            ],
        ];
    }

    public function filters(){
        return [
            'user_id' => [
                ['intval'],
            ],
            'path' => [
                ['strip_tags'],
                ['trim'],
            ],
            'position' => [
                ['intval'],
            ],
            'line' => [
                ['trim'],
            ]
        ];
    }
}