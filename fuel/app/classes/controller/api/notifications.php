<?php

use Fuel\Core\Controller;
use Fuel\Core\Input;
use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\Log;

class Controller_Api_Notifications extends Controller
{
    protected $user_id = null;

    public function before()
    {
        parent::before();
        header('Content-Type: application/json');
        
        $this->user_id = Session::get('user_id');
        if (!$this->user_id) {
            $this->output_json(array(
                'success' => false,
                'message' => 'ログインが必要です',
            ), 401);
            return false;
        }
    }

    /**
     * GET /api/notifications/upcoming-tasks - 近づいているタスクを取得
     */
    public function action_upcoming_tasks()
    {
        try {
            $minutes = Input::get('minutes', 30); // デフォルト30分前
            $now = date('Y-m-d H:i:s');
            $targetTime = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));

            // 今から指定分後までのタスクを取得
            $tasks = Model_Task::find('all', array(
                'where' => array(
                    array('user_id', $this->user_id),
                    array('status', 0), // 未完了のみ
                ),
                'order_by' => array('due_date' => 'asc', 'due_time' => 'asc')
            ));

            $upcomingTasks = array();
            
            foreach ($tasks as $task) {
                if (!$task->due_date) continue;
                
                // 締切日時を作成
                $dueDateTime = $task->due_date;
                if ($task->due_time) {
                    $dueDateTime .= ' ' . $task->due_time;
                } else {
                    $dueDateTime .= ' 23:59:59'; // 時間未設定の場合は日末
                }

                // 通知対象かチェック
                if (strtotime($dueDateTime) >= strtotime($now) && 
                    strtotime($dueDateTime) <= strtotime($targetTime)) {
                    
                    $upcomingTasks[] = array(
                        'id' => (int)$task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'due_date' => $task->due_date,
                        'due_time' => $task->due_time,
                        'due_datetime' => $dueDateTime,
                        'minutes_until' => round((strtotime($dueDateTime) - strtotime($now)) / 60)
                    );
                }
            }

            $this->output_json(array(
                'success' => true,
                'tasks' => $upcomingTasks,
                'count' => count($upcomingTasks),
                'check_time' => $now,
                'target_time' => $targetTime
            ));

        } catch (Exception $e) {
            Log::error('Upcoming Tasks API Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => '近づいているタスクの取得に失敗しました'
            ), 500);
        }
    }

    /**
     * GET /api/notifications/upcoming-schedules - 近づいている予定を取得
     */
    public function action_upcoming_schedules()
    {
        try {
            $minutes = Input::get('minutes', 30);
            $now = date('Y-m-d H:i:s');
            $targetTime = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));

            // 指定時間内の予定を取得
            $schedules = Model_Schedule::find('all', array(
                'where' => array(
                    array('user_id', $this->user_id),
                    array('start_datetime', '>=', $now),
                    array('start_datetime', '<=', $targetTime)
                ),
                'order_by' => array('start_datetime' => 'asc')
            ));

            $upcomingSchedules = array();
            
            foreach ($schedules as $schedule) {
                $upcomingSchedules[] = array(
                    'id' => (int)$schedule->id,
                    'title' => $schedule->title,
                    'description' => $schedule->description,
                    'location' => $schedule->location,
                    'start_datetime' => $schedule->start_datetime,
                    'end_datetime' => $schedule->end_datetime,
                    'minutes_until' => round((strtotime($schedule->start_datetime) - strtotime($now)) / 60)
                );
            }

            $this->output_json(array(
                'success' => true,
                'schedules' => $upcomingSchedules,
                'count' => count($upcomingSchedules),
                'check_time' => $now,
                'target_time' => $targetTime
            ));

        } catch (Exception $e) {
            Log::error('Upcoming Schedules API Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => '近づいている予定の取得に失敗しました'
            ), 500);
        }
    }

    /**
     * GET /api/notifications/daily-summary - 日次サマリーを取得
     */
    public function action_daily_summary()
    {
        try {
            $date = Input::get('date', date('Y-m-d'));
            
            // 指定日のタスクを取得
            $tasks = Model_Task::find('all', array(
                'where' => array(
                    array('user_id', $this->user_id),
                    array('due_date', $date)
                ),
                'order_by' => array('due_time' => 'asc')
            ));

            // 指定日の予定を取得
            $schedules = Model_Schedule::find('all', array(
                'where' => array(
                    array('user_id', $this->user_id),
                ),
                'order_by' => array('start_datetime' => 'asc')
            ));

            // 予定をフィルタリング
            $daySchedules = array();
            foreach ($schedules as $schedule) {
                $scheduleDate = date('Y-m-d', strtotime($schedule->start_datetime));
                if ($scheduleDate === $date) {
                    $daySchedules[] = array(
                        'id' => (int)$schedule->id,
                        'title' => $schedule->title,
                        'start_datetime' => $schedule->start_datetime,
                        'end_datetime' => $schedule->end_datetime,
                        'location' => $schedule->location
                    );
                }
            }

            // タスクデータを整理
            $taskData = array();
            $completedCount = 0;
            $pendingCount = 0;

            foreach ($tasks as $task) {
                $taskInfo = array(
                    'id' => (int)$task->id,
                    'title' => $task->title,
                    'due_time' => $task->due_time,
                    'status' => (int)$task->status
                );
                
                $taskData[] = $taskInfo;
                
                if ($task->status == 1) {
                    $completedCount++;
                } else {
                    $pendingCount++;
                }
            }

            $this->output_json(array(
                'success' => true,
                'date' => $date,
                'summary' => array(
                    'total_tasks' => count($taskData),
                    'completed_tasks' => $completedCount,
                    'pending_tasks' => $pendingCount,
                    'total_schedules' => count($daySchedules)
                ),
                'tasks' => $taskData,
                'schedules' => $daySchedules
            ));

        } catch (Exception $e) {
            Log::error('Daily Summary API Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => '日次サマリーの取得に失敗しました'
            ), 500);
        }
    }

    /**
     * GET /api/notifications/overdue-tasks - 期限切れタスクを取得
     */
    public function action_overdue_tasks()
    {
        try {
            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');

            $tasks = Model_Task::find('all', array(
                'where' => array(
                    array('user_id', $this->user_id),
                    array('status', 0), // 未完了のみ
                    array('due_date', '<', $today) // 今日より前
                ),
                'order_by' => array('due_date' => 'desc', 'due_time' => 'desc')
            ));

            $overdueTasks = array();
            
            foreach ($tasks as $task) {
                $dueDateTime = $task->due_date;
                if ($task->due_time) {
                    $dueDateTime .= ' ' . $task->due_time;
                } else {
                    $dueDateTime .= ' 23:59:59';
                }

                $daysPast = floor((strtotime($now) - strtotime($dueDateTime)) / (24 * 60 * 60));
                
                $overdueTasks[] = array(
                    'id' => (int)$task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'due_date' => $task->due_date,
                    'due_time' => $task->due_time,
                    'days_overdue' => $daysPast
                );
            }

            $this->output_json(array(
                'success' => true,
                'tasks' => $overdueTasks,
                'count' => count($overdueTasks)
            ));

        } catch (Exception $e) {
            Log::error('Overdue Tasks API Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => '期限切れタスクの取得に失敗しました'
            ), 500);
        }
    }

    /**
     * POST /api/notifications/settings - 通知設定を保存
     */
    public function action_save_settings()
    {
        try {
            $input = json_decode(Input::json(), true);
            
            if (!$input) {
                $this->output_json(array(
                    'success' => false,
                    'message' => '不正なJSONデータです'
                ), 400);
                return;
            }

            // 通知設定をユーザー設定として保存
            // ここでは簡単のためセッションに保存（実際はDBに保存推奨）
            Session::set('notification_settings', $input);

            $this->output_json(array(
                'success' => true,
                'message' => '通知設定を保存しました',
                'settings' => $input
            ));

        } catch (Exception $e) {
            Log::error('Save Notification Settings Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => '通知設定の保存に失敗しました'
            ), 500);
        }
    }

    /**
     * GET /api/notifications/settings - 通知設定を取得
     */
    public function action_get_settings()
    {
        try {
            $settings = Session::get('notification_settings', array(
                'task_reminder' => true,
                'schedule_reminder' => true,
                'reminder_minutes' => 30,
                'daily_reminder' => true,
                'daily_reminder_time' => '09:00',
                'overdue_reminder' => true
            ));

            $this->output_json(array(
                'success' => true,
                'settings' => $settings
            ));

        } catch (Exception $e) {
            Log::error('Get Notification Settings Error: ' . $e->getMessage());
            $this->output_json(array(
                'success' => false,
                'message' => '通知設定の取得に失敗しました'
            ), 500);
        }
    }

    protected function output_json($data, $status_code = 200)
    {
        http_response_code($status_code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}