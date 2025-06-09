<?php

use Fuel\Core\Session;

class Controller_Task extends Controller
{
    public function action_index()
    {
        $user_id = Session::get('user_id');
        if (!$user_id) {
            return Response::redirect('user/register'); // 未ログイン時のリダイレクト
        }

        $tasks = Model_Task::find('all', array(
            'where' => array(array('user_id', $user_id)),
            'order_by' => array('due_date' => 'asc', 'due_time' => 'asc')
        ));

        return View::forge('task/index', array(
            'tasks' => $tasks
        ));
    }


    public function action_create()
    {
        if (Input::method() == 'POST') {
            // バリデーションルールを定義
            $val = Validation::forge();
            $val->add('title', 'タイトル')
                ->add_rule('required')
                ->add_rule('max_length', 50);

            $val->add('due_date', '期限日')
                ->add_rule('required')
                ->add_rule('valid_date');

            $val->add('due_time', '期限時刻')
                ->add_rule('required')
                ->add_rule('match_pattern', '/^\d{2}:\d{2}$/');

            if ($val->run()) {
                $task = Model_Task::forge(array(
                    'user_id' => Session::get('user_id', 1),  // 仮に1
                    'title' => Input::post('title'),
                    'description' => Input::post('description'),
                    'due_date' => Input::post('due_date'),
                    'due_time' => Input::post('due_time'),
                    'status' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ));

                if ($task->save()) {
                    return Response::redirect('task/index');
                } else {
                    return Response::forge('保存に失敗しました');
                }
            } else {
                // エラーがある場合はフォームに戻して表示
                return View::forge('task/create', array(
                    'errors' => $val->error()
                ));
            }
        }

        return View::forge('task/create', array('errors' => array()));
    }


    public function action_delete($id)
    {
        $user_id = Session::get('user_id');
        if (!$user_id) {
            return Response::redirect('user/register');
        }

        $task = Model_Task::find($id);
        if ($task && $task->user_id == $user_id) {
            $task->delete();
        }

        return Response::redirect('/task');
    }

    public function action_toggle_status($id)
    {
        $task = Model_Task::find($id);

        if ($task) {
            $task->status = $task->status == 0 ? 1 : 0; // トグル処理
            $task->updated_at = date('Y-m-d H:i:s');
            $task->save();
        }

        return Response::redirect('/task');
    }

    public function action_edit($id)
    {
        $task = Model_Task::find($id);

        if (Input::method() == 'POST') {
            $task->title = Input::post('title');
            $task->description = Input::post('description');
            $task->due_date = Input::post('due_date');
            $task->due_time = Input::post('due_time');
            $task->updated_at = date('Y-m-d H:i:s');

            if ($task->save()) {
                return Response::redirect('task');
            } else {
                return Response::forge('更新に失敗しました');
            }
        }

        return View::forge('task/edit', array('task' => $task));
    }




}
