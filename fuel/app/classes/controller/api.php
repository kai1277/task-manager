<?php

use Fuel\Core\Controller;
use Fuel\Core\Input;
use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\Validation;
use Fuel\Core\Log;
use Fuel\Core\Security;

class Controller_Api extends Controller_Base
{
    protected $user_id = null;

    public function before()
    {
        parent::before();
        
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        $this->user_id = Session::get('user_id');
        
        if (!$this->user_id) {
            $this->output_json(array(
                'message' => 'ログインが必要です',
                'code' => 401
            ), 401);
            return false;
        }
    }

    public function action_tasks($id = null)
    {
        $method = Input::method();
        
        try {
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
                        'message' => 'サポートされていないメソッドです'
                    ), 405);
            }
        } catch (Exception $e) {
            return $this->handle_error($e, 'API処理でエラーが発生しました');
        }
    }

    public function action_toggle($id = null)
    {
        try {
            if (!$id || !is_numeric($id) || $id <= 0) {
                $this->output_json(array(
                    'message' => '有効なタスクIDが必要です'
                ), 400);
                return;
            }
            
            $task = Model_Task::find($id);
            if (!$task || $task->user_id != $this->user_id) {
                $this->output_json(array(
                    'message' => 'タスクが見つからないか、アクセス権限がありません'
                ), 404);
                return;
            }

            $input = $this->get_safe_json_input();
            
            if (isset($input['status'])) {
                $status = (int)$input['status'];
                if ($status !== 0 && $status !== 1) {
                    $this->output_json(array(
                        'message' => 'ステータスは0または1である必要があります'
                    ), 400);
                    return;
                }
                $task->status = $status;
            } else {
                $task->status = $task->status == 0 ? 1 : 0;
            }
            
            $task->updated_at = date('Y-m-d H:i:s');

            if ($task->save()) {
                $this->output_json(array(
                    'message' => 'ステータスを更新しました',
                    'task' => array(
                        'id' => (int)$task->id,
                        'status' => (int)$task->status
                    )
                ));
            } else {
                throw new Exception('ステータスの更新に失敗しました');
            }

        } catch (Exception $e) {
            Log::error('API Toggle Error: ' . $e->getMessage(), array(
                'user_id' => $this->user_id,
                'task_id' => $id
            ));
            $this->output_json(array(
                'message' => 'ステータスの更新に失敗しました'
            ), 500);
        }
    }

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
                    'id' => (int)$task->id,
                    'title' => $this->safe_output($task->title),
                    'description' => $this->safe_output($task->description),
                    'due_date' => $task->due_date,
                    'due_time' => $task->due_time,
                    'status' => (int)$task->status,
                    'created_at' => $task->created_at,
                    'updated_at' => $task->updated_at
                );
            }

            $this->output_json(array(
                'tasks' => $task_data,
                'count' => count($task_data)
            ));

        } catch (Exception $e) {
            Log::error('API Tasks GET Error: ' . $e->getMessage(), array(
                'user_id' => $this->user_id
            ));
            $this->output_json(array(
                'message' => 'タスクの取得に失敗しました'
            ), 500);
        }
    }

    protected function get_task($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                $this->output_json(array(
                    'message' => '有効なタスクIDが必要です'
                ), 400);
                return;
            }

            $task = Model_Task::find($id);
            if (!$task || $task->user_id != $this->user_id) {
                $this->output_json(array(
                    'message' => 'タスクが見つからないか、アクセス権限がありません'
                ), 404);
                return;
            }

            $this->output_json(array(
                'task' => array(
                    'id' => (int)$task->id,
                    'title' => $this->safe_output($task->title),
                    'description' => $this->safe_output($task->description),
                    'due_date' => $task->due_date,
                    'due_time' => $task->due_time,
                    'status' => (int)$task->status,
                    'created_at' => $task->created_at,
                    'updated_at' => $task->updated_at
                )
            ));

        } catch (Exception $e) {
            Log::error('API Task GET Error: ' . $e->getMessage(), array(
                'user_id' => $this->user_id,
                'task_id' => $id
            ));
            $this->output_json(array(
                'message' => 'タスクの取得に失敗しました'
            ), 500);
        }
    }

    protected function create_task()
    {
        try {
            $input = $this->get_safe_json_input();
            
            if (!$input) {
                $this->output_json(array(
                    'message' => '不正なJSONデータです'
                ), 400);
                return;
            }

            $validation_result = $this->validate_task_data($input);
            if ($validation_result !== true) {
                $this->output_json(array(
                    'message' => 'バリデーションエラー',
                    'errors' => $validation_result
                ), 400);
                return;
            }

            $task = Model_Task::forge(array(
                'user_id' => $this->user_id,
                'title' => Security::clean($input['title']),
                'description' => isset($input['description']) ? Security::clean($input['description']) : '',
                'due_date' => $input['due_date'],
                'due_time' => isset($input['due_time']) ? $input['due_time'] : null,
                'status' => isset($input['status']) ? (int)$input['status'] : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            if ($task->save()) {
                $this->output_json(array(
                    'message' => 'タスクを作成しました',
                    'task' => array(
                        'id' => (int)$task->id,
                        'title' => $this->safe_output($task->title),
                        'description' => $this->safe_output($task->description),
                        'due_date' => $task->due_date,
                        'due_time' => $task->due_time,
                        'status' => (int)$task->status
                    )
                ), 201);
            } else {
                throw new Exception('タスクの保存に失敗しました');
            }

        } catch (Exception $e) {
            Log::error('API Tasks POST Error: ' . $e->getMessage(), array(
                'user_id' => $this->user_id,
                'input' => isset($input) ? $input : null
            ));
            $this->output_json(array(
                'message' => 'タスクの作成に失敗しました'
            ), 500);
        }
    }

    protected function update_task($id)
    {
        try {
            if (!$id || !is_numeric($id) || $id <= 0) {
                $this->output_json(array(
                    'message' => '有効なタスクIDが必要です'
                ), 400);
                return;
            }

            $task = Model_Task::find($id);
            if (!$task || $task->user_id != $this->user_id) {
                $this->output_json(array(
                    'message' => 'タスクが見つからないか、アクセス権限がありません'
                ), 404);
                return;
            }

            $input = $this->get_safe_json_input();
            
            if (isset($input['title'])) {
                if (empty(trim($input['title']))) {
                    $this->output_json(array(
                        'message' => 'タイトルは必須です'
                    ), 400);
                    return;
                }
                $task->title = Security::clean($input['title']);
            }
            
            if (isset($input['description'])) {
                $task->description = Security::clean($input['description']);
            }
            
            if (isset($input['due_date'])) {
                if (!$this->validate_date($input['due_date'])) {
                    $this->output_json(array(
                        'message' => '無効な日付形式です'
                    ), 400);
                    return;
                }
                $task->due_date = $input['due_date'];
            }
            
            if (isset($input['due_time'])) {
                if (!empty($input['due_time']) && !$this->validate_time($input['due_time'])) {
                    $this->output_json(array(
                        'message' => '無効な時刻形式です'
                    ), 400);
                    return;
                }
                $task->due_time = $input['due_time'];
            }
            
            if (isset($input['status'])) {
                $status = (int)$input['status'];
                if ($status !== 0 && $status !== 1) {
                    $this->output_json(array(
                        'message' => 'ステータスは0または1である必要があります'
                    ), 400);
                    return;
                }
                $task->status = $status;
            }
            
            $task->updated_at = date('Y-m-d H:i:s');

            if ($task->save()) {
                $this->output_json(array(
                    'message' => 'タスクを更新しました',
                    'task' => array(
                        'id' => (int)$task->id,
                        'title' => $this->safe_output($task->title),
                        'description' => $this->safe_output($task->description),
                        'due_date' => $task->due_date,
                        'due_time' => $task->due_time,
                        'status' => (int)$task->status
                    )
                ));
            } else {
                throw new Exception('タスクの更新に失敗しました');
            }

        } catch (Exception $e) {
            Log::error('API Tasks PUT Error: ' . $e->getMessage(), array(
                'user_id' => $this->user_id,
                'task_id' => $id
            ));
            $this->output_json(array(
                'message' => 'タスクの更新に失敗しました'
            ), 500);
        }
    }

    protected function delete_task($id)
    {
        try {
            if (!$id || !is_numeric($id) || $id <= 0) {
                $this->output_json(array(
                    'message' => '有効なタスクIDが必要です'
                ), 400);
                return;
            }

            $task = Model_Task::find($id);
            if (!$task || $task->user_id != $this->user_id) {
                $this->output_json(array(
                    'message' => 'タスクが見つからないか、アクセス権限がありません'
                ), 404);
                return;
            }

            if ($task->delete()) {
                $this->output_json(array(
                    'message' => 'タスクを削除しました'
                ));
            } else {
                throw new Exception('タスクの削除に失敗しました');
            }

        } catch (Exception $e) {
            Log::error('API Tasks DELETE Error: ' . $e->getMessage(), array(
                'user_id' => $this->user_id,
                'task_id' => $id
            ));
            $this->output_json(array(
                'message' => 'タスクの削除に失敗しました'
            ), 500);
        }
    }

    private function get_safe_json_input()
    {
        $json = Input::json();
        if (!$json) {
            return null;
        }
        
        $input = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $this->sanitize_input($input);
    }

    private function sanitize_input($data)
    {
        if (is_array($data)) {
            $sanitized = array();
            foreach ($data as $key => $value) {
                $sanitized[$key] = $this->sanitize_input($value);
            }
            return $sanitized;
        } elseif (is_string($data)) {
            return trim($data);
        } else {
            return $data;
        }
    }

    private function validate_task_data($input)
    {
        $errors = array();

        if (empty($input['title']) || !is_string($input['title'])) {
            $errors['title'] = 'タイトルは必須です';
        } elseif (strlen($input['title']) > 255) {
            $errors['title'] = 'タイトルは255文字以下である必要があります';
        }

        if (empty($input['due_date'])) {
            $errors['due_date'] = '期限日は必須です';
        } elseif (!$this->validate_date($input['due_date'])) {
            $errors['due_date'] = '有効な日付形式（YYYY-MM-DD）で入力してください';
        }

        if (!empty($input['due_time']) && !$this->validate_time($input['due_time'])) {
            $errors['due_time'] = '有効な時刻形式（HH:MM）で入力してください';
        }

        if (isset($input['description']) && strlen($input['description']) > 1000) {
            $errors['description'] = '説明は1000文字以下である必要があります';
        }

        if (isset($input['status'])) {
            $status = (int)$input['status'];
            if ($status !== 0 && $status !== 1) {
                $errors['status'] = 'ステータスは0または1である必要があります';
            }
        }

        return empty($errors) ? true : $errors;
    }

    private function validate_date($date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        $timestamp = strtotime($date);
        return $timestamp !== false && date('Y-m-d', $timestamp) === $date;
    }

    private function validate_time($time)
    {
        return preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time) && 
               strtotime('2000-01-01 ' . $time) !== false;
    }
}