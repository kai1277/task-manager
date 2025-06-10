<?php

use Fuel\Core\Session;

class Controller_Schedule extends Controller
{
    public function action_index()
    {
        $user_id = Session::get('user_id');
        if (!$user_id) {
            return Response::redirect('user/register');
        }

        $schedules = Model_Schedule::find('all', array(
            'where' => array(array('user_id', $user_id)),
            'order_by' => array('start_datetime' => 'asc')
        ));

        return View::forge('schedule/index', array(
            'schedules' => $schedules
        ));
    }

    public function action_create()
    {
        $user_id = Session::get('user_id');
        if (!$user_id) {
            return Response::redirect('user/register');
        }

        if (Input::method() == 'POST') {
            $schedule = Model_Schedule::forge(array(
                'user_id' => $user_id,
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

    public function action_edit($id)
    {
        $user_id = Session::get('user_id');
        if (!$user_id) {
            return Response::redirect('user/register');
        }

        $schedule = Model_Schedule::find($id);
        if (!$schedule || $schedule->user_id != $user_id) {
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

    public function action_delete($id)
    {
        $user_id = Session::get('user_id');
        if (!$user_id) {
            return Response::redirect('user/register');
        }

        $schedule = Model_Schedule::find($id);
        if ($schedule && $schedule->user_id == $user_id) {
            $schedule->delete();
        }

        return Response::redirect('schedule');
    }
}