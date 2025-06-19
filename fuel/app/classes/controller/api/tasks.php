<?php

use Fuel\Core\Controller;
use Fuel\Core\Input;
use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\Log;

class Controller_Api_Tasks extends Controller
{
    protected $user_id = null;

    public function before()
    {
        parent::before();
        header('Content-Type: application/json');
        $this->user_id = Session::get('user_id');
        if (!$this->user_id) {
            $this->output_json(array(
                'success' => false,
                'message' => 'ログインが必要です',
            ), 401);
        }
    }

    public function action_index()
    {
        // GET一覧表示（Controller_Api の get_tasks にあたる処理をここに）
    }

    public function action_create()
    {
        // POSTで新規作成（Controller_Api の create_task 相当）
    }

    // 必要に応じて PUT / DELETE 用メソッドも追加
    // public function action_update($id) { ... }
    // public function action_delete($id) { ... }

    protected function output_json($data, $status_code = 200)
    {
        http_response_code($status_code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}