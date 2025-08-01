<?php

use Fuel\Core\Controller;
use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\Input;
use Fuel\Core\Security;
use Fuel\Core\Log;

class Controller_Base extends Controller
{
    protected $user_id = null;
    protected $user_name = null;

    public function before()
    {
        parent::before();
        
        if (Input::method() === 'POST') {
            $this->check_csrf_token();
        }
        
        $this->user_id = Session::get('user_id');
        $this->user_name = Session::get('name');
        
        if ($this->requires_login()) {
            if (!$this->user_id) {
                Session::set_flash('error', 'ログインが必要です');
                return Response::redirect('user/login');
            }
        }
        
        View::set_global('escape', function($text) {
            return Security::htmlentities($text);
        });
    }
    
    protected function check_csrf_token()
    {
        $token = Input::post(Config::get('security.csrf_token_key', 'fuel_csrf_token'));
        
        if (!$token || !Security::check_token($token)) {
            Log::warning('CSRF token validation failed', array(
                'user_id' => $this->user_id,
                'ip' => Input::ip(),
                'user_agent' => Input::user_agent(),
                'uri' => Input::uri()
            ));
        
            if (Fuel::$env === Fuel::DEVELOPMENT) {
                throw new HttpException('CSRF token validation failed. Expected token but got: ' . ($token ?: 'null'), 403);
            }
            
            Session::set_flash('error', '不正なアクセスが検出されました。もう一度お試しください。');
            return Response::redirect(Input::referrer() ?: '/');
        }
    }
    
    protected function requires_login()
    {
        return true; 
    }
    
    protected function safe_output($text)
    {
        return Security::htmlentities($text);
    }

    protected function output_json($data, $status_code = 200)
    {
        $escaped_data = $this->escape_array($data);
        
        $response = array(
            'success' => $status_code < 400,
            'data' => $escaped_data,
            'csrf_token' => Security::fetch_token(), 
            'timestamp' => date('c')
        );
        
        if ($status_code >= 400) {
            $response['error'] = $escaped_data;
            unset($response['data']);
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        http_response_code($status_code);
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }

    private function escape_array($data)
    {
        if (is_array($data)) {
            $escaped = array();
            foreach ($data as $key => $value) {
                $escaped[$key] = $this->escape_array($value);
            }
            return $escaped;
        } elseif (is_string($data)) {
            return Security::htmlentities($data);
        } else {
            return $data;
        }
    }

    protected function safe_redirect($url, $default = 'task')
    {
        if (empty($url) || !$this->is_safe_redirect_url($url)) {
            $url = $default;
        }
        
        return Response::redirect($url);
    }
    
    private function is_safe_redirect_url($url)
    {
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            return true;
        }
        
        $allowed_patterns = array(
            '/^task/',
            '/^schedule/',
            '/^class/',
            '/^user/',
        );
        
        foreach ($allowed_patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function handle_error($e, $user_message = 'エラーが発生しました')
    {
        Log::error('Application Error: ' . $e->getMessage(), array(
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => $this->user_id,
            'uri' => Uri::current(),
            'trace' => $e->getTraceAsString()
        ));
        
        if (Fuel::$env === Fuel::DEVELOPMENT) {
            throw $e;
        }
        
        Session::set_flash('error', $user_message);
        return $this->safe_redirect(Input::referrer(), 'task');
    }
}