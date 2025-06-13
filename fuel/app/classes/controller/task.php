<?php

use Fuel\Core\Session;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\View;
use Fuel\Core\Validation;

class Controller_Task extends Controller_Base
{
    public function action_index()
    {
        $tasks = Model_Task::find('all', array(
            'where' => array(array('user_id', $this->user_id)),
            'order_by' => array('due_date' => 'asc', 'due_time' => 'asc')
        ));

        return View::forge('task/index', array(
            'tasks' => $tasks
        ));
    }

    public function action_create()
    {
        $val = Validation::forge();

        $val->add('title', 'タイトル')
            ->add_rule('required')
            ->add_rule('max_length', 255);

        $val->add('description', '説明')
            ->add_rule('max_length', 1000);

        $val->add('due_date', '期限日')
            ->add_rule('required')
            ->add_rule('match_pattern', '/^\d{4}-\d{2}-\d{2}$/');

        $val->add('due_time', '期限時刻')
            ->add_rule('match_pattern', '/^\d{2}:\d{2}(:\d{2})?$/');

        if (Input::method() == 'POST') {
            if ($val->run()) {
                $task = Model_Task::forge(array(
                    'user_id' => $this->user_id,
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
                $errors = $val->error();
            }
        }

        return View::forge('task/create', array(
            'errors' => isset($errors) ? $errors : array()
        ));
    }

    public function action_delete($id)
    {
        $task = Model_Task::find($id);
        if ($task && $task->user_id == $this->user_id) {
            $task->delete();
        }

        return Response::redirect('/task');
    }

    public function action_toggle_status($id)
    {
        $task = Model_Task::find($id);

        if ($task && $task->user_id == $this->user_id) {
            $task->status = $task->status == 0 ? 1 : 0;
            $task->updated_at = date('Y-m-d H:i:s');
            $task->save();
        }

        return Response::redirect('/task');
    }

    public function action_edit($id)
    {
        $task = Model_Task::find($id);

        if (!$task || $task->user_id != $this->user_id) {
            return Response::redirect('task');
        }

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