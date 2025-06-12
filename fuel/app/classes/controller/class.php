<?php

use Fuel\Core\Session;

class Controller_Class extends Controller_Base  // Controller → Controller_Base に変更
{
    // 一覧表示
    public function action_index()
    {
        // セッションチェックは不要（beforeメソッドで処理済み）
        $classes = Model_Class::find('all', array(
            'where' => array(array('user_id', $this->user_id)),  // $this->user_id を使用
            'order_by' => array('day_of_week' => 'asc', 'period' => 'asc')
        ));

        return View::forge('class/index', array(
            'classes' => $classes
        ));
    }

    // 新規作成フォーム表示・保存処理  
    public function action_create()
    {
        // セッションチェックは不要（beforeメソッドで処理済み）
        if (Input::method() == 'POST') {
            $class = Model_Class::forge(array(
                'user_id' => $this->user_id,  // $this->user_id を使用
                'title' => Input::post('title'),
                'description' => Input::post('description'),
                'year' => Input::post('year'),
                'start_month' => Input::post('start_month'),
                'end_month' => Input::post('end_month'),
                'day_of_week' => Input::post('day_of_week'),
                'period' => Input::post('period'),
                'class_room' => Input::post('class_room'),
                'instructor' => Input::post('instructor'),
            ));

            if ($class->save()) {
                return Response::redirect('class');
            } else {
                return Response::forge('保存に失敗しました');
            }
        }

        return View::forge('class/create');
    }

    // 編集
    public function action_edit($id)
    {
        // セッションチェックは不要（beforeメソッドで処理済み）
        $class = Model_Class::find($id);
        if (!$class || $class->user_id != $this->user_id) {  // セキュリティチェック
            return Response::redirect('class');
        }

        if (Input::method() == 'POST') {
            $class->title = Input::post('title');
            $class->description = Input::post('description');
            $class->year = Input::post('year');
            $class->start_month = Input::post('start_month');
            $class->end_month = Input::post('end_month');
            $class->day_of_week = Input::post('day_of_week');
            $class->period = Input::post('period');
            $class->class_room = Input::post('class_room');
            $class->instructor = Input::post('instructor');

            if ($class->save()) {
                return Response::redirect('class');
            }
        }

        return View::forge('class/edit', array('class' => $class));
    }

    // 削除
    public function action_delete($id)
    {
        // セッションチェックは不要（beforeメソッドで処理済み）
        $class = Model_Class::find($id);
        if ($class && $class->user_id == $this->user_id) {  // セキュリティチェック
            $class->delete();
        }

        return Response::redirect('class');
    }
}