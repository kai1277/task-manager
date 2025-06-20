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
        // 今後1ヶ月のタスクを取得（完了済みも含む）
        $startDate = date('Y-m-d', strtotime('-1 week')); // 過去1週間から
        $endDate = date('Y-m-d', strtotime('+1 month'));  // 今後1ヶ月まで
        
        $tasks = Model_Task::find('all', array(
            'where' => array(
                array('user_id', $this->user_id),
                array('due_date', '>=', $startDate),
                array('due_date', '<=', $endDate)
            ),
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
                    // リダイレクト先を判定
                    $redirect_to = $this->get_redirect_destination();
                    return Response::redirect($redirect_to);
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

        // リダイレクト先を判定
        $redirect_to = $this->get_redirect_destination();
        return Response::redirect($redirect_to);
    }

    public function action_toggle_status($id)
    {
        $task = Model_Task::find($id);

        if ($task && $task->user_id == $this->user_id) {
            $task->status = $task->status == 0 ? 1 : 0;
            $task->updated_at = date('Y-m-d H:i:s');
            $task->save();
        }

        // リダイレクト先を判定
        $redirect_to = $this->get_redirect_destination();
        return Response::redirect($redirect_to);
    }

    public function action_edit($id)
    {
        $task = Model_Task::find($id);

        if (!$task || $task->user_id != $this->user_id) {
            // リダイレクト先を判定
            $redirect_to = $this->get_redirect_destination();
            return Response::redirect($redirect_to);
        }

        if (Input::method() == 'POST') {
            $task->title = Input::post('title');
            $task->description = Input::post('description');
            $task->due_date = Input::post('due_date');
            $task->due_time = Input::post('due_time');
            $task->updated_at = date('Y-m-d H:i:s');

            if ($task->save()) {
                // リダイレクト先を判定
                $redirect_to = $this->get_redirect_destination();
                return Response::redirect($redirect_to);
            } else {
                return Response::forge('更新に失敗しました');
            }
        }

        return View::forge('task/edit', array('task' => $task));
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
                // 日付パラメータがある場合は保持
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
        }
        
        // デフォルトはタスク一覧
        return 'task';
    }

    public function action_day($date = null)
    {
        // URLパラメータまたは今日の日付を使用
        $selectedDate = $date ? $date : date('Y-m-d');
        
        // 選択された日付のタスクを取得
        $tasks = Model_Task::find('all', array(
            'where' => array(
                array('user_id', $this->user_id),
                array('due_date', $selectedDate)  // 特定の日付のみ
            ),
            'order_by' => array('due_time' => 'asc')
        ));

        // スケジュールの取得方法を変更
        $schedules = array();
        
        // 全スケジュールを取得して、PHPでフィルタリング
        $allSchedules = Model_Schedule::find('all', array(
            'where' => array(array('user_id', $this->user_id)),
            'order_by' => array('start_datetime' => 'asc')
        ));
        
        // 選択された日付に該当するスケジュールをフィルタリング
        foreach ($allSchedules as $schedule) {
            $scheduleDate = date('Y-m-d', strtotime($schedule->start_datetime));
            if ($scheduleDate === $selectedDate) {
                $schedules[] = $schedule;
            }
        }

        return View::forge('task/day', array(
            'tasks' => $tasks,
            'schedules' => $schedules,
            'selectedDate' => $selectedDate
        ));
    }

    public function action_week($date = null)
    {
        // URLパラメータまたは今週の開始日を使用
        $selectedDate = $date ? $date : date('Y-m-d');
        
        // 週の開始日（月曜日）を計算
        $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));
        $weekEnd = date('Y-m-d', strtotime('sunday this week', strtotime($selectedDate)));
        
        // 週のタスクを取得
        $tasks = Model_Task::find('all', array(
            'where' => array(
                array('user_id', $this->user_id),
                array('due_date', '>=', $weekStart),
                array('due_date', '<=', $weekEnd)
            ),
            'order_by' => array('due_date' => 'asc', 'due_time' => 'asc')
        ));

        // 週のスケジュールを取得
        $allSchedules = Model_Schedule::find('all', array(
            'where' => array(array('user_id', $this->user_id)),
            'order_by' => array('start_datetime' => 'asc')
        ));
        
        // 週に該当するスケジュールをフィルタリング
        $schedules = array();
        foreach ($allSchedules as $schedule) {
            $scheduleDate = date('Y-m-d', strtotime($schedule->start_datetime));
            if ($scheduleDate >= $weekStart && $scheduleDate <= $weekEnd) {
                $schedules[] = $schedule;
            }
        }

        // 履修科目を取得（現在の年度・月に該当するもの）
        $classes = Model_Class::find('all', array(
            'where' => array(
                array('user_id', $this->user_id),
                array('year', date('Y')),
                array('start_month', '<=', date('n')),
                array('end_month', '>=', date('n'))
            ),
            'order_by' => array('day_of_week' => 'asc', 'period' => 'asc')
        ));

        return View::forge('task/week', array(
            'tasks' => $tasks,
            'schedules' => $schedules,
            'classes' => $classes,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'selectedDate' => $selectedDate
        ));
    }

    public function action_month($date = null)
    {
        // URLパラメータまたは今月を使用
        $selectedDate = $date ? $date : date('Y-m-d');
        
        // 月の開始日と終了日を計算
        $year = date('Y', strtotime($selectedDate));
        $month = date('n', strtotime($selectedDate));
        
        $monthStart = date('Y-m-01', strtotime($selectedDate));
        $monthEnd = date('Y-m-t', strtotime($selectedDate));
        
        // カレンダー表示用の開始日（前月の日曜日から）
        $calendarStart = date('Y-m-d', strtotime('last sunday', strtotime($monthStart)));
        if ($calendarStart === $monthStart) {
            $calendarStart = date('Y-m-d', strtotime($monthStart . ' -7 days'));
        }
        
        // カレンダー表示用の終了日（翌月の土曜日まで）
        $calendarEnd = date('Y-m-d', strtotime('next saturday', strtotime($monthEnd)));
        if ($calendarEnd === $monthEnd) {
            $calendarEnd = date('Y-m-d', strtotime($monthEnd . ' +7 days'));
        }

        // 月のタスクを取得
        $tasks = Model_Task::find('all', array(
            'where' => array(
                array('user_id', $this->user_id),
                array('due_date', '>=', $calendarStart),
                array('due_date', '<=', $calendarEnd)
            ),
            'order_by' => array('due_date' => 'asc', 'due_time' => 'asc')
        ));

        // 月のスケジュールを取得
        $allSchedules = Model_Schedule::find('all', array(
            'where' => array(array('user_id', $this->user_id)),
            'order_by' => array('start_datetime' => 'asc')
        ));
        
        // カレンダー期間に該当するスケジュールをフィルタリング
        $schedules = array();
        foreach ($allSchedules as $schedule) {
            $scheduleDate = date('Y-m-d', strtotime($schedule->start_datetime));
            if ($scheduleDate >= $calendarStart && $scheduleDate <= $calendarEnd) {
                $schedules[] = $schedule;
            }
        }

        // 履修科目を取得（現在の年度・月に該当するもの）
        $classes = Model_Class::find('all', array(
            'where' => array(
                array('user_id', $this->user_id),
                array('year', $year),
                array('start_month', '<=', $month),
                array('end_month', '>=', $month)
            ),
            'order_by' => array('day_of_week' => 'asc', 'period' => 'asc')
        ));

        // 日付ごとにデータを整理
        $calendarData = array();
        $currentDate = $calendarStart;
        
        while ($currentDate <= $calendarEnd) {
            $dayOfWeek = date('w', strtotime($currentDate)); // 0=日曜日
            
            $calendarData[$currentDate] = array(
                'date' => $currentDate,
                'day' => date('j', strtotime($currentDate)),
                'is_current_month' => date('Y-m', strtotime($currentDate)) === date('Y-m', strtotime($selectedDate)),
                'is_today' => $currentDate === date('Y-m-d'),
                'day_of_week' => $dayOfWeek,
                'tasks' => array(),
                'schedules' => array(),
                'classes' => array()
            );
            
            // タスクを追加
            foreach ($tasks as $task) {
                if ($task->due_date === $currentDate) {
                    $calendarData[$currentDate]['tasks'][] = $task;
                }
            }
            
            // スケジュールを追加
            foreach ($schedules as $schedule) {
                $scheduleDate = date('Y-m-d', strtotime($schedule->start_datetime));
                if ($scheduleDate === $currentDate) {
                    $calendarData[$currentDate]['schedules'][] = $schedule;
                }
            }
            
            // 履修科目を追加（該当する曜日のみ）
            foreach ($classes as $class) {
                // 日曜日=0, 月曜日=1... なので、classのday_of_weekは1=月曜日
                $classDayOfWeek = ($class->day_of_week == 7) ? 0 : $class->day_of_week; // 日曜日の調整
                if ($classDayOfWeek === $dayOfWeek) {
                    $calendarData[$currentDate]['classes'][] = $class;
                }
            }
            
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        return View::forge('task/month', array(
            'calendarData' => $calendarData,
            'year' => $year,
            'month' => $month,
            'selectedDate' => $selectedDate,
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd
        ));
    }
}