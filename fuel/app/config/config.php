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
     *  基本設定
     * -------------------------------------------------------------------------
     */
    'base_url' => '/task-manager/public/',
    'url_suffix' => '',
    'index_file' => false,
    
    /**
     * -------------------------------------------------------------------------
     *  多言語・タイムゾーン設定
     * -------------------------------------------------------------------------
     */
    'language' => 'ja',
    'language_fallback' => 'en',
    'locale' => 'ja_JP.UTF-8',
    'default_timezone' => 'Asia/Tokyo',
    'encoding' => 'UTF-8',
    
    /**
     * -------------------------------------------------------------------------
     *  ログ設定
     * -------------------------------------------------------------------------
     */
    'log_threshold' => Fuel::L_WARNING,
    'log_path' => APPPATH.'logs/',
    'log_date_format' => 'Y-m-d H:i:s',
    
    /**
     * -------------------------------------------------------------------------
     *  セキュリティ設定
     * -------------------------------------------------------------------------
     */
    'security' => array(
        'csrf_autoload' => false,
        'csrf_autoload_methods' => array('post', 'put', 'delete'),
        'csrf_bad_request_on_fail' => false,
        'csrf_auto_token' => false,
        'csrf_token_key' => 'fuel_csrf_token',
        'csrf_expiration' => 0,
        'token_salt' => 'put your salt value here to make the token more secure',
        'allow_x_headers' => false,
        'uri_filter' => array('htmlentities'),
        'input_filter' => array(),
        'output_filter' => array('Security::htmlentities'),
        'htmlentities_flags' => ENT_QUOTES,
        'htmlentities_double_encode' => false,
        'auto_filter_output' => true,
        'whitelisted_classes' => array(
            'Fuel\\Core\\Presenter',
            'Fuel\\Core\\Response',
            'Fuel\\Core\\View',
            'Fuel\\Core\\ViewModel',
            'Closure',
        ),
    ),
    
    /**
     * -------------------------------------------------------------------------
     *  Cookie設定
     * -------------------------------------------------------------------------
     */
    'cookie' => array(
        'expiration' => 0,
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => false,
    ),
    
    /**
     * -------------------------------------------------------------------------
     *  バリデーション設定
     * -------------------------------------------------------------------------
     */
    'validation' => array(
        'global_input_fallback' => true,
    ),
    
    /**
     * -------------------------------------------------------------------------
     *  コントローラー設定
     * -------------------------------------------------------------------------
     */
    'controller_prefix' => 'Controller_',
    
    /**
     * -------------------------------------------------------------------------
     *  ルーティング設定
     * -------------------------------------------------------------------------
     */
    'routing' => array(
        'case_sensitive' => true,
        'strip_extension' => true,
    ),
    
    /**
     * -------------------------------------------------------------------------
     *  パッケージパス
     * -------------------------------------------------------------------------
     */
    'package_paths' => array(
        PKGPATH,
    ),
    
    /**
     * -------------------------------------------------------------------------
     *  自動読み込み設定
     * -------------------------------------------------------------------------
     */
    'always_load' => array(
        'packages' => array(
            'orm',
        ),
        'config' => array(),
        'language' => array(),
    ),
);