<?php

use Fuel\Core\Session;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\View;

class Controller_Schedule extends Controller_Base
{
    public function action_index()
    {
        $schedules = Model_Schedule::find('all', array(
            'where' => array(array('user_id', $this->user_id)),
            'order_by' => array('start_datetime' => 'asc')
        ));

        return View::forge('schedule/index', array(
            'schedules' => $schedules
        ));
    }

   public function action_create()
    {
        if (Input::method() == 'POST') {
            $startDate = Input::post('start_date');
            $startTime = Input::post('start_time');
            $endDate = Input::post('end_date');
            $endTime = Input::post('end_time');
            
            $startDatetime = $startDate . ' ' . $startTime;
            $endDatetime = $endDate . ' ' . $endTime;
            
            $schedule = Model_Schedule::forge(array(
                'user_id' => $this->user_id,
                'title' => Input::post('title'),
                'location' => Input::post('location'),
                'description' => Input::post('description'),
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'all_day' => 0,
            ));

            if ($schedule->save()) {
                // リダイレクト先を判定
                $redirect_to = $this->get_redirect_destination();
                return Response::redirect($redirect_to);
            } else {
                return Response::forge('保存に失敗しました');
            }
        }

        return View::forge('schedule/create');
    }

    public function action_edit($id)
    {
        $schedule = Model_Schedule::find($id);
        if (!$schedule || $schedule->user_id != $this->user_id) {
            // リダイレクト先を判定
            $redirect_to = $this->get_redirect_destination();
            return Response::redirect($redirect_to);
        }

        if (Input::method() == 'POST') {
            $schedule->title = Input::post('title');
            $schedule->location = Input::post('location');
            $schedule->description = Input::post('description');
            $schedule->start_datetime = Input::post('start_date') . ' ' . Input::post('start_time');
            $schedule->end_datetime = Input::post('end_date') . ' ' . Input::post('end_time');
            $schedule->all_day = Input::post('all_day') ? 1 : 0;

            if ($schedule->save()) {
                // リダイレクト先を判定
                $redirect_to = $this->get_redirect_destination();
                return Response::redirect($redirect_to);
            }
        }

        return View::forge('schedule/edit', array('schedule' => $schedule));
    }

    public function action_delete($id)
    {
        $schedule = Model_Schedule::find($id);
        if ($schedule && $schedule->user_id == $this->user_id) {
            $schedule->delete();
        }

        // リダイレクト先を判定
        $redirect_to = $this->get_redirect_destination();
        return Response::redirect($redirect_to);
    }

    /**
     * リダイレクト先を判定する
     */
    private function get_redirect_destination()
    {
        // HTTP_REFERERをチェック
        $referer = Input::server('HTTP_REFERER');
        
        if ($referer) {
            // 日表示からの場合
            if (strpos($referer, '/task/day') !== false) {
                $path_parts = parse_url($referer, PHP_URL_PATH);
                if (preg_match('/\/task\/day\/(\d{4}-\d{2}-\d{2})/', $path_parts, $matches)) {
                    return 'task/day/' . $matches[1];
                } else {
                    return 'task/day';
                }
            }
            // 週表示からの場合
            elseif (strpos($referer, '/task/week') !== false) {
                $path_parts = parse_url($referer, PHP_URL_PATH);
                if (preg_match('/\/task\/week\/(\d{4}-\d{2}-\d{2})/', $path_parts, $matches)) {
                    return 'task/week/' . $matches[1];
                } else {
                    return 'task/week';
                }
            }
            // 月表示からの場合
            elseif (strpos($referer, '/task/month') !== false) {
                $path_parts = parse_url($referer, PHP_URL_PATH);
                if (preg_match('/\/task\/month\/(\d{4}-\d{2}-\d{2})/', $path_parts, $matches)) {
                    return 'task/month/' . $matches[1];
                } else {
                    return 'task/month';
                }
            }
            // 予定一覧からの場合
            elseif (strpos($referer, '/schedule') !== false) {
                return 'schedule';
            }
        }
        
        // デフォルトは予定一覧
        return 'schedule';
    }
}