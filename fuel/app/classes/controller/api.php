<?php

use Fuel\Core\Controller;
use Fuel\Core\Input;
use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\Validation;
use Fuel\Core\Log;

class Controller_Api extends Controller
{
    protected $user_id = null;

    public function before()
    {
        parent::before();
        
        // JSON レスポンスのヘッダーを設定
        header('Content-Type: application/json');
        
        // セッションからユーザーIDを取得
        $this->user_id = Session::get('user_id');
        
        if (!$this->user_id) {
            $this->output_json(array(
                'success' => false,
                'message' => 'ログインが必要です',
                'code' => 401
            ), 401);
            return false;
        }
    }

    /**
     * GET/POST /api/tasks - タスク取得・作成
     */
    public function action_tasks($id = null)
    {
        $method = Input::method();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->get_task($id);
                } else {
                    $this->get_tasks();
                }
                break;
                
            case 'POST':
                $this->create_task();
                break;
                
            case 'PUT':
                $this->update_task($id);
                break;
                
            case 'DELETE':
                $this->delete_task($id);
                break;
                
            default:
                $this->output_json(array(
                    'success' => false,
                    'message' => 'サポートされていないメソッドです'
                ), 405);
        }
    }

    /**
     * POST /api/toggle/{id} - ステータス切り替え
     */
    public function action_toggle($id = null)
    {
        if (!$id) {
            $this->output_json(array(
                'success' => false,
                'message' => 'タスクIDが必要です'
            ), 400);
            return;
        }
        
        try {
            $task = Model_Task::find($id);
            if (!$task || $task->user_id != $this->user_id) {
                $this->output_json(array(
                    'success' => false,
                    'message' => 'タスクが見つかりません'
                ), 404);
                return;
            }

            $input = json_decode(Input::json(), true);
            
            if (isset($input['status'])) {
                $task->status = (int)$input['status'];
            } else {
                $task->status = $task->status == 0 ? 1 : 0;
            }
            
            $task->updated_at = date('Y-m-d H:i:s');

            if ($task->save()) {
                $this->output_json(array(
                    'success' => true,
                    'message' => 'ステータスを更新しました',
                    'task' => array(
                        'id' => $task->id,
                        'status' => (int)$task->status
                    )
                ));
            } else {
                throw new Exception('ステータスの更新に失敗しました');
            }

        } catch (Exception $e) {
            Log::error('API Toggle Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => 'ステータスの更新に失敗しました'
            ), 500);
        }
    }

    /**
     * タスク一覧取得
     */
    protected function get_tasks()
    {
        try {
            $startDate = date('Y-m-d', strtotime('-1 week'));
            $endDate = date('Y-m-d', strtotime('+1 month'));
            
            $tasks = Model_Task::find('all', array(
                'where' => array(
                    array('user_id', $this->user_id),
                    array('due_date', '>=', $startDate),
                    array('due_date', '<=', $endDate)
                ),
                'order_by' => array('due_date' => 'asc', 'due_time' => 'asc')
            ));

            $task_data = array();
            foreach ($tasks as $task) {
                $task_data[] = array(
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'due_date' => $task->due_date,
                    'due_time' => $task->due_time,
                    'status' => (int)$task->status,
                    'created_at' => $task->created_at,
                    'updated_at' => $task->updated_at
                );
            }

            $this->output_json(array(
                'success' => true,
                'tasks' => $task_data,
                'count' => count($task_data)
            ));

        } catch (Exception $e) {
            Log::error('API Tasks GET Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => 'タスクの取得に失敗しました'
            ), 500);
        }
    }

    /**
     * タスク作成
     */
    protected function create_task()
    {
        try {
            $input = json_decode(Input::json(), true);
            
            if (!$input) {
                $this->output_json(array(
                    'success' => false,
                    'message' => '不正なJSONデータです'
                ), 400);
                return;
            }

            // バリデーション
            if (empty($input['title'])) {
                $this->output_json(array(
                    'success' => false,
                    'message' => 'タイトルは必須です'
                ), 400);
                return;
            }

            if (empty($input['due_date'])) {
                $this->output_json(array(
                    'success' => false,
                    'message' => '期限日は必須です'
                ), 400);
                return;
            }

            // タスク作成
            $task = Model_Task::forge(array(
                'user_id' => $this->user_id,
                'title' => $input['title'],
                'description' => isset($input['description']) ? $input['description'] : '',
                'due_date' => $input['due_date'],
                'due_time' => isset($input['due_time']) ? $input['due_time'] : null,
                'status' => isset($input['status']) ? (int)$input['status'] : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            if ($task->save()) {
                $this->output_json(array(
                    'success' => true,
                    'message' => 'タスクを作成しました',
                    'task' => array(
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'due_date' => $task->due_date,
                        'due_time' => $task->due_time,
                        'status' => (int)$task->status
                    )
                ), 201);
            } else {
                throw new Exception('タスクの保存に失敗しました');
            }

        } catch (Exception $e) {
            Log::error('API Tasks POST Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => 'タスクの作成に失敗しました'
            ), 500);
        }
    }

    /**
     * タスク更新
     */
    protected function update_task($id)
    {
        try {
            if (!$id) {
                $this->output_json(array(
                    'success' => false,
                    'message' => 'タスクIDが必要です'
                ), 400);
                return;
            }

            $task = Model_Task::find($id);
            if (!$task || $task->user_id != $this->user_id) {
                $this->output_json(array(
                    'success' => false,
                    'message' => 'タスクが見つかりません'
                ), 404);
                return;
            }

            $input = json_decode(Input::json(), true);
            
            if (isset($input['title'])) {
                $task->title = $input['title'];
            }
            if (isset($input['description'])) {
                $task->description = $input['description'];
            }
            if (isset($input['due_date'])) {
                $task->due_date = $input['due_date'];
            }
            if (isset($input['due_time'])) {
                $task->due_time = $input['due_time'];
            }
            if (isset($input['status'])) {
                $task->status = (int)$input['status'];
            }
            
            $task->updated_at = date('Y-m-d H:i:s');

            if ($task->save()) {
                $this->output_json(array(
                    'success' => true,
                    'message' => 'タスクを更新しました',
                    'task' => array(
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'due_date' => $task->due_date,
                        'due_time' => $task->due_time,
                        'status' => (int)$task->status
                    )
                ));
            } else {
                throw new Exception('タスクの更新に失敗しました');
            }

        } catch (Exception $e) {
            Log::error('API Tasks PUT Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => 'タスクの更新に失敗しました'
            ), 500);
        }
    }

    /**
     * タスク削除
     */
    protected function delete_task($id)
    {
        try {
            if (!$id) {
                $this->output_json(array(
                    'success' => false,
                    'message' => 'タスクIDが必要です'
                ), 400);
                return;
            }

            $task = Model_Task::find($id);
            if (!$task || $task->user_id != $this->user_id) {
                $this->output_json(array(
                    'success' => false,
                    'message' => 'タスクが見つかりません'
                ), 404);
                return;
            }

            if ($task->delete()) {
                $this->output_json(array(
                    'success' => true,
                    'message' => 'タスクを削除しました'
                ));
            } else {
                throw new Exception('タスクの削除に失敗しました');
            }

        } catch (Exception $e) {
            Log::error('API Tasks DELETE Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => 'タスクの削除に失敗しました'
            ), 500);
        }
    }

    /**
     * JSON出力ヘルパー
     */
    protected function output_json($data, $status_code = 200)
    {
        http_response_code($status_code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}