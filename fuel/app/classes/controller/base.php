<?php

use Fuel\Core\Controller;
use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\Input;
use Fuel\Core\Security;
use Fuel\Core\Log;
use Fuel\Core\Uri;

class Controller_Base extends Controller
{
    protected $user_id = null;
    protected $user_name = null;

    public function before()
    {
        parent::before();
        
        // セッションからユーザー情報を取得
        $this->user_id = Session::get('user_id');
        $this->user_name = Session::get('name');
        
        // ログインが必要なコントローラーかチェック
        if ($this->requires_login()) {
            if (!$this->user_id) {
                // 未ログインの場合、ログインページにリダイレクト
                Session::set_flash('error', 'ログインが必要です');
                return Response::redirect('user/login');
            }
        }
        
        // CSRF対策: POSTリクエストでCSRFトークンをチェック
        // ただし、ログイン・ユーザー登録では除外
        if (Input::method() === 'POST' && $this->should_check_csrf()) {
            $csrf_result = $this->check_csrf_token();
            if ($csrf_result !== true) {
                return $csrf_result; // リダイレクトレスポンスを返す
            }
        }
        
        return true;
    }
    
    /**
     * CSRFチェックが必要かどうか判定
     */
    protected function should_check_csrf()
    {
        // 現在のコントローラーとアクションを取得
        $controller_name = strtolower(str_replace('Controller_', '', get_class($this)));
        $action = $this->request->action ?? '';
        
        // CSRFチェックを除外するアクション
        $csrf_skip_patterns = array(
            'user/login',
            'user/create',
            'user/register',
            'user/forgot_password'
        );
        
        $current_route = $controller_name . '/' . $action;
        
        return !in_array($current_route, $csrf_skip_patterns);
    }
    
    /**
     * CSRF トークンをチェック
     */
    protected function check_csrf_token()
    {
        $token = Input::post('fuel_csrf_token');
        $session_token = Session::get(Config::get('security.csrf_token_key', 'fuel_csrf_token'));
        
        if (!$token || !Security::check_token($token)) {
            Log::warning('CSRF token validation failed', array(
                'user_id' => $this->user_id,
                'ip' => Input::ip(),
                'user_agent' => Input::user_agent(),
                'uri' => Input::uri(),
                'received_token' => $token ? substr($token, 0, 10) . '...' : 'null',
                'session_has_token' => !empty($session_token)
            ));
            
            // 開発環境では詳細なエラー情報を表示
            if (Fuel::$env === Fuel::DEVELOPMENT) {
                $error_message = 'CSRF token validation failed.' . PHP_EOL;
                $error_message .= 'Received token: ' . ($token ?: 'null') . PHP_EOL;
                $error_message .= 'Session has token: ' . ($session_token ? 'yes' : 'no') . PHP_EOL;
                $error_message .= 'Request method: ' . Input::method() . PHP_EOL;
                $error_message .= 'Request URI: ' . Input::uri() . PHP_EOL;
                $error_message .= 'Controller: ' . get_class($this) . PHP_EOL;
                $error_message .= 'Action: ' . $this->request->action;
                
                // 例外を投げる代わりに、エラーメッセージを表示してリダイレクト
                Session::set_flash('error', 'CSRF Token Error (Development):<br><pre>' . htmlspecialchars($error_message) . '</pre>');
                return Response::redirect(Input::referrer() ?: 'task');
            }
            
            // 本番環境では安全なメッセージでリダイレクト
            Session::set_flash('error', '不正なアクセスが検出されました。もう一度お試しください。');
            return Response::redirect(Input::referrer() ?: 'task');
        }
        
        return true;
    }
    
    /**
     * ログインが必要かどうかを判定（オーバーライド可能）
     */
    protected function requires_login()
    {
        return true; // デフォルトはログイン必須
    }
    
    /**
     * 安全な出力のためのヘルパーメソッド（XSS対策）
     */
    protected function safe_output($text)
    {
        if ($text === null) {
            return '';
        }
        return Security::htmlentities($text);
    }
    
    /**
     * 配列を再帰的にエスケープ（XSS対策）
     */
    protected function escape_array($data)
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
    
    /**
     * 複数レベルの配列をエスケープ（深い配列対応）
     */
    protected function deep_escape_array($data, $max_depth = 10, $current_depth = 0)
    {
        // 無限ループ防止
        if ($current_depth >= $max_depth) {
            return $data;
        }
        
        if (is_array($data)) {
            $escaped = array();
            foreach ($data as $key => $value) {
                $escaped[$key] = $this->deep_escape_array($value, $max_depth, $current_depth + 1);
            }
            return $escaped;
        } elseif (is_object($data)) {
            // オブジェクトの場合は配列に変換してからエスケープ
            $array_data = (array) $data;
            return $this->deep_escape_array($array_data, $max_depth, $current_depth + 1);
        } elseif (is_string($data)) {
            return Security::htmlentities($data);
        } else {
            return $data;
        }
    }
    
    /**
     * JSONレスポンス用のヘルパーメソッド（XSS対策済み）
     */
    protected function output_json($data, $status_code = 200)
    {
        // データを再帰的にエスケープ
        $escaped_data = $this->escape_array($data);
        
        $response = array(
            'success' => $status_code < 400,
            'timestamp' => date('c')
        );
        
        if ($status_code < 400) {
            $response['data'] = $escaped_data;
            $response['csrf_token'] = Security::fetch_token(); // 新しいCSRFトークンを含める
        } else {
            $response['error'] = $escaped_data;
        }
        
        // セキュリティヘッダーを設定
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        http_response_code($status_code);
        
        // JSON出力時の自動エスケープ処理
        echo json_encode(
            $response, 
            JSON_UNESCAPED_UNICODE | 
            JSON_HEX_TAG | 
            JSON_HEX_AMP | 
            JSON_HEX_APOS | 
            JSON_HEX_QUOT
        );
        exit;
    }
    
    /**
     * 安全なJSONレスポンス（成功用）
     */
    protected function json_success($data = array(), $message = null)
    {
        $response = array(
            'success' => true,
            'data' => $this->escape_array($data)
        );
        
        if ($message) {
            $response['message'] = $this->safe_output($message);
        }
        
        $this->output_json($response, 200);
    }
    
    /**
     * 安全なJSONレスポンス（エラー用）
     */
    protected function json_error($message, $status_code = 400, $data = array())
    {
        $response = array(
            'success' => false,
            'message' => $this->safe_output($message)
        );
        
        if (!empty($data)) {
            $response['data'] = $this->escape_array($data);
        }
        
        $this->output_json($response, $status_code);
    }
    
    /**
     * ビューデータの安全なエスケープ
     */
    protected function safe_view_data($data)
    {
        if (is_array($data)) {
            $safe_data = array();
            foreach ($data as $key => $value) {
                $safe_data[$key] = $this->safe_view_data($value);
            }
            return $safe_data;
        } elseif (is_string($data)) {
            return $this->safe_output($data);
        } else {
            return $data;
        }
    }
    
    /**
     * リダイレクト先のURLを検証
     */
    protected function safe_redirect($url, $default = 'task')
    {
        // URLの検証（オープンリダイレクト対策）
        if (empty($url) || !$this->is_safe_redirect_url($url)) {
            $url = $default;
        }
        
        return Response::redirect($url);
    }
    
    /**
     * リダイレクト先URLが安全かどうかチェック
     */
    private function is_safe_redirect_url($url)
    {
        // 空文字や null の場合は false
        if (empty($url)) {
            return false;
        }
        
        // 相対URLのみ許可
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            return true;
        }
        
        // 許可されたパスのパターン
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
    
    /**
     * 入力値の安全な取得
     */
    protected function safe_input($key, $default = null, $method = 'post')
    {
        $value = null;
        
        if ($method === 'post') {
            $value = Input::post($key, $default);
        } elseif ($method === 'get') {
            $value = Input::get($key, $default);
        }
        
        if (is_string($value)) {
            return Security::clean($value);
        } elseif (is_array($value)) {
            return $this->escape_array($value);
        }
        
        return $value;
    }
    
    /**
     * エラーハンドリングの統一化
     */
    protected function handle_error($e, $user_message = 'エラーが発生しました')
    {
        // ログに記録
        Log::error('Application Error: ' . $e->getMessage(), array(
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => $this->user_id,
            'uri' => Uri::current(),
            'trace' => $e->getTraceAsString()
        ));
        
        // 開発環境では詳細表示
        if (Fuel::$env === Fuel::DEVELOPMENT) {
            throw $e;
        }
        
        // 本番環境では安全なエラーメッセージ
        Session::set_flash('error', $user_message);
        return $this->safe_redirect(Input::referrer(), 'task');
    }
    
    /**
     * バリデーションエラーの安全な処理
     */
    protected function handle_validation_errors($validation_errors)
    {
        $safe_errors = array();
        
        foreach ($validation_errors as $field => $error) {
            if (is_object($error)) {
                $safe_errors[$field] = $this->safe_output($error->get_message());
            } elseif (is_string($error)) {
                $safe_errors[$field] = $this->safe_output($error);
            } else {
                $safe_errors[$field] = $error;
            }
        }
        
        return $safe_errors;
    }
}