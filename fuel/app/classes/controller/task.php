<?php

use Fuel\Core\Session;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\View;
use Fuel\Core\Validation;
use Fuel\Core\Security;

class Controller_Task extends Controller_Base
{
    public function action_index()
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

            return View::forge('task/index', array(
                'tasks' => $tasks,
                'csrf_token' => Security::fetch_token()
            ));
            
        } catch (Exception $e) {
            return $this->handle_error($e, 'タスク一覧の取得に失敗しました');
        }
    }

    public function action_create()
    {
        if (Input::method() == 'POST') {
            try {
                $validation = $this->validate_task_input();
                if ($validation !== true) {
                    return View::forge('task/create', array(
                        'errors' => $validation,
                        'csrf_token' => Security::fetch_token(),
                        'old_input' => $this->get_safe_input_data()
                    ));
                }

                $task = Model_Task::forge(array(
                    'user_id' => $this->user_id,
                    'title' => Security::clean(Input::post('title')),
                    'description' => Security::clean(Input::post('description')),
                    'due_date' => Input::post('due_date'),
                    'due_time' => Input::post('due_time'),
                    'status' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ));

                if ($task->save()) {
                    Session::set_flash('success', 'タスクを作成しました');
                    $redirect_to = $this->get_safe_redirect_destination();
                    return $this->safe_redirect($redirect_to);
                } else {
                    throw new Exception('タスクの保存に失敗しました');
                }
                
            } catch (Exception $e) {
                return $this->handle_error($e, 'タスクの作成に失敗しました');
            }
        }

        return View::forge('task/create', array(
            'errors' => array(),
            'csrf_token' => Security::fetch_token()
        ));
    }

    public function action_delete($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new InvalidArgumentException('無効なタスクIDです');
            }

            $task = Model_Task::find($id);
            if (!$task || $task->user_id != $this->user_id) {
                throw new Exception('タスクが見つからないか、アクセス権限がありません');
            }

            if ($task->delete()) {
                Session::set_flash('success', 'タスクを削除しました');
            } else {
                throw new Exception('タスクの削除に失敗しました');
            }

        } catch (Exception $e) {
            return $this->handle_error($e, 'タスクの削除に失敗しました');
        }

        $redirect_to = $this->get_safe_redirect_destination();
        return $this->safe_redirect($redirect_to);
    }

    public function action_toggle_status($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new InvalidArgumentException('無効なタスクIDです');
            }

            $task = Model_Task::find($id);
            if (!$task || $task->user_id != $this->user_id) {
                throw new Exception('タスクが見つからないか、アクセス権限がありません');
            }

            $task->status = $task->status == 0 ? 1 : 0;
            $task->updated_at = date('Y-m-d H:i:s');
            
            if ($task->save()) {
                Session::set_flash('success', 'タスクのステータスを更新しました');
            } else {
                throw new Exception('ステータスの更新に失敗しました');
            }

        } catch (Exception $e) {
            return $this->handle_error($e, 'ステータスの更新に失敗しました');
        }

        $redirect_to = $this->get_safe_redirect_destination();
        return $this->safe_redirect($redirect_to);
    }

    public function action_edit($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new InvalidArgumentException('無効なタスクIDです');
            }

            $task = Model_Task::find($id);
            if (!$task || $task->user_id != $this->user_id) {
                Session::set_flash('error', 'タスクが見つからないか、アクセス権限がありません');
                $redirect_to = $this->get_safe_redirect_destination();
                return $this->safe_redirect($redirect_to);
            }

            if (Input::method() == 'POST') {
                $validation = $this->validate_task_input();
                if ($validation !== true) {
                    return View::forge('task/edit', array(
                        'task' => $task,
                        'errors' => $validation,
                        'csrf_token' => Security::fetch_token()
                    ));
                }

                $task->title = Security::clean(Input::post('title'));
                $task->description = Security::clean(Input::post('description'));
                $task->due_date = Input::post('due_date');
                $task->due_time = Input::post('due_time');
                $task->updated_at = date('Y-m-d H:i:s');

                if ($task->save()) {
                    Session::set_flash('success', 'タスクを更新しました');
                    $redirect_to = $this->get_safe_redirect_destination();
                    return $this->safe_redirect($redirect_to);
                } else {
                    throw new Exception('タスクの更新に失敗しました');
                }
            }

            return View::forge('task/edit', array(
                'task' => $task,
                'csrf_token' => Security::fetch_token()
            ));
            
        } catch (Exception $e) {
            return $this->handle_error($e, 'タスクの編集でエラーが発生しました');
        }
    }

    public function action_day($date = null)
    {
        try {
            $selectedDate = $this->validate_date_parameter($date);
            
            $tasks = Model_Task::find('all', array(
                'where' => array(
                    array('user_id', $this->user_id),
                    array('due_date', $selectedDate)
                ),
                'order_by' => array('due_time' => 'asc')
            ));

            $schedules = $this->get_schedules_for_date($selectedDate);

            return View::forge('task/day', array(
                'tasks' => $tasks,
                'schedules' => $schedules,
                'selectedDate' => $selectedDate,
                'csrf_token' => Security::fetch_token()
            ));
            
        } catch (Exception $e) {
            return $this->handle_error($e, '日表示の読み込みに失敗しました');
        }
    }

    public function action_week($date = null)
    {
        try {
            $selectedDate = $this->validate_date_parameter($date);
            
            $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));
            $weekEnd = date('Y-m-d', strtotime('sunday this week', strtotime($selectedDate)));
            
            $tasks = Model_Task::find('all', array(
                'where' => array(
                    array('user_id', $this->user_id),
                    array('due_date', '>=', $weekStart),
                    array('due_date', '<=', $weekEnd)
                ),
                'order_by' => array('due_date' => 'asc', 'due_time' => 'asc')
            ));

            $schedules = $this->get_schedules_for_date_range($weekStart, $weekEnd);

            $classes = $this->get_current_classes();

            return View::forge('task/week', array(
                'tasks' => $tasks,
                'schedules' => $schedules,
                'classes' => $classes,
                'weekStart' => $weekStart,
                'weekEnd' => $weekEnd,
                'selectedDate' => $selectedDate,
                'csrf_token' => Security::fetch_token()
            ));
            
        } catch (Exception $e) {
            return $this->handle_error($e, '週表示の読み込みに失敗しました');
        }
    }

    public function action_month($date = null)
    {
        try {
            $selectedDate = $this->validate_date_parameter($date);
            
            $year = date('Y', strtotime($selectedDate));
            $month = date('n', strtotime($selectedDate));
            
            $monthStart = date('Y-m-01', strtotime($selectedDate));
            $monthEnd = date('Y-m-t', strtotime($selectedDate));
            
            $calendarStart = date('Y-m-d', strtotime('last sunday', strtotime($monthStart)));
            if ($calendarStart === $monthStart) {
                $calendarStart = date('Y-m-d', strtotime($monthStart . ' -7 days'));
            }
            
            $calendarEnd = date('Y-m-d', strtotime('next saturday', strtotime($monthEnd)));
            if ($calendarEnd === $monthEnd) {
                $calendarEnd = date('Y-m-d', strtotime($monthEnd . ' +7 days'));
            }

            $calendarData = $this->build_calendar_data($calendarStart, $calendarEnd, $year, $month);

            return View::forge('task/month', array(
                'calendarData' => $calendarData,
                'year' => $year,
                'month' => $month,
                'selectedDate' => $selectedDate,
                'monthStart' => $monthStart,
                'monthEnd' => $monthEnd,
                'csrf_token' => Security::fetch_token()
            ));
            
        } catch (Exception $e) {
            return $this->handle_error($e, '月表示の読み込みに失敗しました');
        }
    }

    private function validate_task_input()
    {
        $val = Validation::forge();

        $val->add('title', 'タイトル')
            ->add_rule('required')
            ->add_rule('max_length', 255)
            ->add_rule('min_length', 1);

        $val->add('description', '説明')
            ->add_rule('max_length', 1000);

        $val->add('due_date', '期限日')
            ->add_rule('required')
            ->add_rule('match_pattern', '/^\d{4}-\d{2}-\d{2}$/');

        $val->add('due_time', '期限時刻')
            ->add_rule('match_pattern', '/^\d{2}:\d{2}(:\d{2})?$/');

        return $val->run() ? true : $val->error();
    }

    private function get_safe_input_data()
    {
        return array(
            'title' => Security::clean(Input::post('title', '')),
            'description' => Security::clean(Input::post('description', '')),
            'due_date' => Input::post('due_date', ''),
            'due_time' => Input::post('due_time', '')
        );
    }

    private function validate_date_parameter($date)
    {
        if ($date) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                throw new InvalidArgumentException('無効な日付形式です');
            }
            
            $timestamp = strtotime($date);
            if ($timestamp === false || date('Y-m-d', $timestamp) !== $date) {
                throw new InvalidArgumentException('無効な日付です');
            }
            
            return $date;
        }
        
        return date('Y-m-d');
    }

    private function get_schedules_for_date($date)
    {
        $allSchedules = Model_Schedule::find('all', array(
            'where' => array(array('user_id', $this->user_id)),
            'order_by' => array('start_datetime' => 'asc')
        ));
        
        $schedules = array();
        foreach ($allSchedules as $schedule) {
            $scheduleDate = date('Y-m-d', strtotime($schedule->start_datetime));
            if ($scheduleDate === $date) {
                $schedules[] = $schedule;
            }
        }
        
        return $schedules;
    }

    private function get_schedules_for_date_range($startDate, $endDate)
    {
        $allSchedules = Model_Schedule::find('all', array(
            'where' => array(array('user_id', $this->user_id)),
            'order_by' => array('start_datetime' => 'asc')
        ));
        
        $schedules = array();
        foreach ($allSchedules as $schedule) {
            $scheduleDate = date('Y-m-d', strtotime($schedule->start_datetime));
            if ($scheduleDate >= $startDate && $scheduleDate <= $endDate) {
                $schedules[] = $schedule;
            }
        }
        
        return $schedules;
    }

    private function get_current_classes()
    {
        return Model_Class::find('all', array(
            'where' => array(
                array('user_id', $this->user_id),
                array('year', date('Y')),
                array('start_month', '<=', date('n')),
                array('end_month', '>=', date('n'))
            ),
            'order_by' => array('day_of_week' => 'asc', 'period' => 'asc')
        ));
    }

    private function build_calendar_data($calendarStart, $calendarEnd, $year, $month)
    {
        $tasks = Model_Task::find('all', array(
            'where' => array(
                array('user_id', $this->user_id),
                array('due_date', '>=', $calendarStart),
                array('due_date', '<=', $calendarEnd)
            ),
            'order_by' => array('due_date' => 'asc', 'due_time' => 'asc')
        ));

        $schedules = $this->get_schedules_for_date_range($calendarStart, $calendarEnd);
        $classes = $this->get_current_classes();

        $calendarData = array();
        $currentDate = $calendarStart;
        
        while ($currentDate <= $calendarEnd) {
            $dayOfWeek = date('w', strtotime($currentDate));
            
            $calendarData[$currentDate] = array(
                'date' => $currentDate,
                'day' => date('j', strtotime($currentDate)),
                'is_current_month' => date('Y-m', strtotime($currentDate)) === date('Y-m', strtotime($year . '-' . $month . '-01')),
                'is_today' => $currentDate === date('Y-m-d'),
                'day_of_week' => $dayOfWeek,
                'tasks' => array(),
                'schedules' => array(),
                'classes' => array()
            );
            
            foreach ($tasks as $task) {
                if ($task->due_date === $currentDate) {
                    $calendarData[$currentDate]['tasks'][] = $task;
                }
            }
            
            foreach ($schedules as $schedule) {
                $scheduleDate = date('Y-m-d', strtotime($schedule->start_datetime));
                if ($scheduleDate === $currentDate) {
                    $calendarData[$currentDate]['schedules'][] = $schedule;
                }
            }
            
            foreach ($classes as $class) {
                $classDayOfWeek = ($class->day_of_week == 7) ? 0 : $class->day_of_week;
                if ($classDayOfWeek === $dayOfWeek) {
                    $calendarData[$currentDate]['classes'][] = $class;
                }
            }
            
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        return $calendarData;
    }

    private function get_safe_redirect_destination()
    {
        $referer = Input::server('HTTP_REFERER');
        
        if ($referer) {
            $path_parts = parse_url($referer, PHP_URL_PATH);
            
            if ($path_parts) {
                if (preg_match('/\/task\/day\/(\d{4}-\d{2}-\d{2})/', $path_parts, $matches)) {
                    $date = $this->validate_date_parameter($matches[1]);
                    return 'task/day/' . $date;
                } elseif (strpos($path_parts, '/task/day') !== false) {
                    return 'task/day';
                }
                
                if (preg_match('/\/task\/week\/(\d{4}-\d{2}-\d{2})/', $path_parts, $matches)) {
                    $date = $this->validate_date_parameter($matches[1]);
                    return 'task/week/' . $date;
                } elseif (strpos($path_parts, '/task/week') !== false) {
                    return 'task/week';
                }
                
                if (preg_match('/\/task\/month\/(\d{4}-\d{2}-\d{2})/', $path_parts, $matches)) {
                    $date = $this->validate_date_parameter($matches[1]);
                    return 'task/month/' . $date;
                } elseif (strpos($path_parts, '/task/month') !== false) {
                    return 'task/month';
                }
            }
        }
        
        return 'task';
    }
}
