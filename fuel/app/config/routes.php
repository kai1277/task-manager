<?php

return array(
    '_root_' => 'user/login',

    '_404_' => 'welcome/404',

    'user/register' => 'user/register',
    'user/create' => 'user/create',
    'user/login' => 'user/login',
    'user/logout' => 'user/logout',

    'task' => 'task/index',
    'task/index' => 'task/index',
    'task/create' => 'task/create',
    'task/edit/(:segment)' => 'task/edit/$1',
    'task/delete/(:segment)' => 'task/delete/$1',
    'task/toggle_status/(:num)' => 'task/toggle_status/$1',
    'task/index/(:segment)' => 'task/index/$1',

    'schedule' => 'schedule/index',
    'schedule/index' => 'schedule/index',
    'schedule/create' => 'schedule/create',
    'schedule/edit/(:segment)' => 'schedule/edit/$1',
    'schedule/delete/(:segment)' => 'schedule/delete/$1',
    
    'class' => 'class/index',
    'class/index' => 'class/index',
    'class/create' => 'class/create',
    'class/edit/(:segment)' => 'class/edit/$1',
    'class/delete/(:segment)' => 'class/delete/$1',

    
);