<?php

use Fuel\Core\Session;

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
                return Response::redirect('user/login');
            }
        }
    }
    
    // ログインが必要かどうかを判定（オーバーライド可能）
    protected function requires_login()
    {
        return true; // デフォルトはログイン必須
    }
}