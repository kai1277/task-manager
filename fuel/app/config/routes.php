<?php

return array(
    '_root_' => 'user/login',

    '_404_' => 'welcome/404',

    // ユーザー認証関連
    'user/register' => 'user/register',
    'user/create' => 'user/create',
    'user/login' => 'user/login',
    'user/logout' => 'user/logout',

    
    // マイページ関連（新規追加）
    'mypage' => 'user/mypage',
    'user/mypage' => 'user/mypage',
    'user/update_profile' => 'user/update_profile',
    'user/change_password' => 'user/change_password',

    'task/day' => 'task/day',
    'task/day/(:segment)' => 'task/day/$1',
    'day' => 'task/day',

    'task/week' => 'task/week',
    'task/week/(:segment)' => 'task/week/$1',
    'week' => 'task/week',

    // 月表示（新規追加）
    'task/month' => 'task/month',
    'task/month/(:segment)' => 'task/month/$1',
    'month' => 'task/month',

    // タスク管理
    'task' => 'task/index',
    'task/index' => 'task/index',
    'task/create' => 'task/create',
    'task/edit/(:segment)' => 'task/edit/$1',
    'task/delete/(:segment)' => 'task/delete/$1',
    'task/toggle_status/(:num)' => 'task/toggle_status/$1',
    'task/index/(:segment)' => 'task/index/$1',

    // スケジュール管理
    'schedule' => 'schedule/index',
    'schedule/index' => 'schedule/index',
    'schedule/create' => 'schedule/create',
    'schedule/edit/(:segment)' => 'schedule/edit/$1',
    'schedule/delete/(:segment)' => 'schedule/delete/$1',
    
    // 履修科目管理
    'class' => 'class/index',
    'class/index' => 'class/index',
    'class/create' => 'class/create',
    'class/edit/(:segment)' => 'class/edit/$1',
    'class/delete/(:segment)' => 'class/delete/$1',

    // REST API ルーティング
    'api/tasks' => 'api/tasks',
    'api/tasks/(:num)' => 'api/tasks/$1',
    'api/toggle/(:num)' => 'api/toggle/$1',
);