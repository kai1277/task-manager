<?php

use Orm\Model;

class Model_Schedule extends Model
{
    protected static $_properties = array(
        'id',
        'user_id',
        'title',
        'location',
        'description',
        'start_datetime',
        'end_datetime',
        'all_day',
        'created_at',
        'updated_at',
    );

    protected static $_table_name = 'schedules';

    protected static $_observers = array(
        'Orm\\Observer_CreatedAt' => array(
            'events' => array('before_insert'),
            'mysql_timestamp' => true,
        ),
        'Orm\\Observer_UpdatedAt' => array(
            'events' => array('before_update'),
            'mysql_timestamp' => true,
        ),
    );

    protected static $_belongs_to = array(
        'user' => array(
            'key_from' => 'user_id',
            'model_to' => 'Model_User',
            'key_to' => 'id',
        ),
    );
}