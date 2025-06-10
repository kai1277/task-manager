<?php

use Fuel\Core\Session;

class Controller_Class extends Controller
{
    public function action_index()
    {
        $user_id = Session::get('user_id');
        if (!$user_id) {
            return Response::redirect('user/register');
        }

        $classes = Model_Class::find('all', array(
            'where' => array(array('user_id', $user_id)),
            'order_by' => array('day_of_week' => 'asc', 'period' => 'asc')
        ));

        return View::forge('class/index', array(
            'classes' => $classes
        ));
    }

    public function action_create()
    {
        $user_id = Session::get('user_id');
        if (!$user_id) {
            return Response::redirect('user/register');
        }

        if (Input::method() == 'POST') {
            $class = Model_Class::forge(array(
                'user_id' => $user_id,
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

    public function action_edit($id)
    {
        $user_id = Session::get('user_id');
        if (!$user_id) {
            return Response::redirect('user/register');
        }

        $class = Model_Class::find($id);
        if (!$class || $class->user_id != $user_id) {
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

    public function action_delete($id)
    {
        $user_id = Session::get('user_id');
        if (!$user_id) {
            return Response::redirect('user/register');
        }

        $class = Model_Class::find($id);
        if ($class && $class->user_id == $user_id) {
            $class->delete();
        }

        return Response::redirect('class');
    }
}