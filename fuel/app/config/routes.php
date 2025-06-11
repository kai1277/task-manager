<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.9-dev
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

return array(
	/**
	 * -------------------------------------------------------------------------
	 *  Default route
	 * -------------------------------------------------------------------------
	 *
	 */

	'_root_' => 'user/register',

	/**
	 * -------------------------------------------------------------------------
	 *  Page not found
	 * -------------------------------------------------------------------------
	 *
	 */

	'_404_' => 'welcome/404',

	/**
	 * -------------------------------------------------------------------------
	 *  destinetion of regiater form
	 * -------------------------------------------------------------------------
	 *
	 *  
	 *
	 */


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
