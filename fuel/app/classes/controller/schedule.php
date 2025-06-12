<?php

use Fuel\Core\Session;

class Controller_Schedule extends Controller_Base  // Controller → Controller_Base に変更
{
    // 一覧表示
    public function action_index()
    {
        // セッションチェックは不要（beforeメソッドで処理済み）
        $schedules = Model_Schedule::find('all', array(
            'where' => array(array('user_id', $this->user_id)),  // $this->user_id を使用
            'order_by' => array('start_datetime' => 'asc')
        ));

        return View::forge('schedule/index', array(
            'schedules' => $schedules
        ));
    }

    // 新規作成フォーム表示・保存処理
    public function action_create()
    {
        // セッションチェックは不要（beforeメソッドで処理済み）
        if (Input::method() == 'POST') {
            $schedule = Model_Schedule::forge(array(
                'user_id' => $this->user_id,  // $this->user_id を使用
                'title' => Input::post('title'),
                'location' => Input::post('location'),
                'description' => Input::post('description'),
                'start_datetime' => Input::post('start_date') . ' ' . Input::post('start_time'),
                'end_datetime' => Input::post('end_date') . ' ' . Input::post('end_time'),
                'all_day' => Input::post('all_day') ? 1 : 0,
            ));

            if ($schedule->save()) {
                return Response::redirect('schedule');
            } else {
                return Response::forge('保存に失敗しました');
            }
        }

        return View::forge('schedule/create');
    }

    // 編集
    public function action_edit($id)
    {
        // セッションチェックは不要（beforeメソッドで処理済み）
        $schedule = Model_Schedule::find($id);
        if (!$schedule || $schedule->user_id != $this->user_id) {  // セキュリティチェック
            return Response::redirect('schedule');
        }

        if (Input::method() == 'POST') {
            $schedule->title = Input::post('title');
            $schedule->location = Input::post('location');
            $schedule->description = Input::post('description');
            $schedule->start_datetime = Input::post('start_date') . ' ' . Input::post('start_time');
            $schedule->end_datetime = Input::post('end_date') . ' ' . Input::post('end_time');
            $schedule->all_day = Input::post('all_day') ? 1 : 0;

            if ($schedule->save()) {
                return Response::redirect('schedule');
            }
        }

        return View::forge('schedule/edit', array('schedule' => $schedule));
    }

    // 削除
    public function action_delete($id)
    {
        // セッションチェックは不要（beforeメソッドで処理済み）
        $schedule = Model_Schedule::find($id);
        if ($schedule && $schedule->user_id == $this->user_id) {  // セキュリティチェック
            $schedule->delete();
        }

        return Response::redirect('schedule');
    }
}