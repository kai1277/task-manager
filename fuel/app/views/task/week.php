<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>週表示 - タスク管理</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
</head>
<body>
    <div class="week-view-container">
        <!-- ヘッダー -->
        <div class="week-header">
            <button class="hamburger-menu" onclick="openMenu()">☰</button>
            
            <div class="header-top">
                <div class="date-display" onclick="openDatePicker()">
                    <button onclick="changeWeek(-1); event.stopPropagation();" style="background:none;border:none;font-size:18px;cursor:pointer;margin-right:10px;">‹</button>
                    <span style="margin: 0 10px;">
                        <?= date('Y年m月d日', strtotime($weekStart)) ?>〜<?= date('d日', strtotime($weekEnd)) ?>
                    </span>
                    <button onclick="changeWeek(1); event.stopPropagation();" style="background:none;border:none;font-size:18px;cursor:pointer;margin-left:10px;">›</button>
                </div>
                
                <div class="view-switcher">
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/day') ?>'">日</button>
                    <button class="view-btn active">週</button>
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/month') ?>'">月</button>
                </div>
            </div>
        </div>

        <!-- 今日のタスク一覧 -->
        <div class="today-tasks-section">
            <?php 
            $today = date('Y-m-d');
            $todayTasks = array();
            foreach ($tasks as $task) {
                if ($task->due_date === $today) {
                    $todayTasks[] = $task;
                }
            }
            ?>
            
            <?php foreach ($todayTasks as $task): ?>
                <div class="task-item <?= $task->status == 1 ? 'task-completed' : '' ?>" onclick="openEditModal(<?= $task->id ?>, 'task')">
                    <input type="checkbox" class="task-checkbox" 
                           <?= $task->status == 1 ? 'checked' : '' ?>
                           data-task-id="<?= $task->id ?>"
                           onclick="event.stopPropagation();">
                    
                    <div class="task-content">
                        <span class="task-title"><?= $task->title ?></span>
                        <span class="task-time">
                            <?= $task->due_time ? date('H:i', strtotime($task->due_time)) : '' ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($todayTasks)): ?>
                <div style="padding: 15px; text-align: center; color: #666; font-size: 14px;">
                    今日のタスクはありません
                </div>
            <?php endif; ?>
        </div>

        <!-- 週間タイムテーブル -->
        <div class="week-timetable">
            <!-- ヘッダー（曜日） -->
            <div class="timetable-header">
                <div class="time-column-header"></div>
                <?php 
                $days = ['月', '火', '水', '木', '金', '土', '日'];
                for ($i = 0; $i < 7; $i++):
                    $currentDate = date('Y-m-d', strtotime($weekStart . ' +' . $i . ' days'));
                    $isToday = ($currentDate === date('Y-m-d'));
                ?>
                    <div class="day-header <?= $isToday ? 'today' : '' ?>">
                        <div class="day-name"><?= $days[$i] ?></div>
                        <div class="day-date"><?= date('n/j', strtotime($currentDate)) ?></div>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- タイムテーブル本体 -->
            <div class="timetable-body">
                <?php 
                // 時間帯定義
                $timeSlots = [
                    ['8:30', '10:15'],
                    ['10:25', '12:10'],
                    ['13:00', '14:45'],
                    ['14:55', '16:40'],
                    ['16:50', '18:35'],
                    ['18:45', '20:30']  // 夜間授業用
                ];
                
                foreach ($timeSlots as $slotIndex => $slot):
                ?>
                    <div class="time-row">
                        <div class="time-column">
                            <div class="time-start"><?= $slot[0] ?></div>
                            <div class="time-end"><?= $slot[1] ?></div>
                        </div>
                        
                        <?php for ($dayIndex = 0; $dayIndex < 7; $dayIndex++): 
                            $currentDate = date('Y-m-d', strtotime($weekStart . ' +' . $dayIndex . ' days'));
                            $dayOfWeek = $dayIndex + 1; // 1=月曜日
                        ?>
                            <div class="day-cell" 
                                 data-date="<?= $currentDate ?>" 
                                 data-slot="<?= $slotIndex ?>"
                                 onclick="openQuickAdd('<?= $currentDate ?>', '<?= $slot[0] ?>')">
                                
                                <?php 
                                // 履修科目を表示
                                foreach ($classes as $class):
                                    if ($class->day_of_week == $dayOfWeek && ($class->period - 1) == $slotIndex):
                                ?>
                                    <div class="class-item" onclick="event.stopPropagation(); openEditModal(<?= $class->id ?>, 'class')">
                                        <div class="class-title"><?= $class->title ?></div>
                                        <?php if ($class->class_room): ?>
                                            <div class="class-room"><?= $class->class_room ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php 
                                    endif;
                                endforeach;
                                
                                // スケジュールを表示
                                foreach ($schedules as $schedule):
                                    $scheduleDate = date('Y-m-d', strtotime($schedule->start_datetime));
                                    $startTime = date('H:i', strtotime($schedule->start_datetime));
                                    $endTime = date('H:i', strtotime($schedule->end_datetime));
                                    
                                    // 時間帯が一致するかチェック
                                    if ($scheduleDate === $currentDate && 
                                        $startTime >= $slot[0] && $startTime < $slot[1]):
                                ?>
                                    <div class="schedule-item" onclick="event.stopPropagation(); openEditModal(<?= $schedule->id ?>, 'schedule')">
                                        <div class="schedule-title"><?= $schedule->title ?></div>
                                        <div class="schedule-time"><?= $startTime ?>-<?= $endTime ?></div>
                                        <?php if ($schedule->location): ?>
                                            <div class="schedule-location">📍<?= $schedule->location ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php 
                                    endif;
                                endforeach;
                                
                                // タスクを表示（時間が設定されているもの）
                                foreach ($tasks as $task):
                                    if ($task->due_date === $currentDate && $task->due_time):
                                        $taskTime = date('H:i', strtotime($task->due_time));
                                        if ($taskTime >= $slot[0] && $taskTime < $slot[1]):
                                ?>
                                    <div class="task-item-small <?= $task->status == 1 ? 'completed' : '' ?>" 
                                         onclick="event.stopPropagation(); openEditModal(<?= $task->id ?>, 'task')">
                                        <div class="task-title-small"><?= $task->title ?></div>
                                        <div class="task-time-small"><?= $taskTime ?></div>
                                    </div>
                                <?php 
                                        endif;
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endforeach; ?>
                
                <!-- アルバイトなど自由枠 -->
                <div class="time-row flexible">
                    <div class="time-column">
                        <div class="time-flexible">その他</div>
                    </div>
                    
                    <?php for ($dayIndex = 0; $dayIndex < 7; $dayIndex++): 
                        $currentDate = date('Y-m-d', strtotime($weekStart . ' +' . $dayIndex . ' days'));
                    ?>
                        <div class="day-cell flexible-cell" 
                             data-date="<?= $currentDate ?>"
                             onclick="openQuickAdd('<?= $currentDate ?>', '')">
                            
                            <?php 
                            // 時間未設定のタスクやその他の予定を表示
                            foreach ($tasks as $task):
                                if ($task->due_date === $currentDate && !$task->due_time):
                            ?>
                                <div class="task-item-small <?= $task->status == 1 ? 'completed' : '' ?>" 
                                     onclick="event.stopPropagation(); openEditModal(<?= $task->id ?>, 'task')">
                                    <div class="task-title-small"><?= $task->title ?></div>
                                </div>
                            <?php 
                                endif;
                            endforeach;
                            ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- 追加ボタン -->
        <div class="add-section">
            <button class="add-btn" onclick="openAddModal()">
                ＋ 追加
            </button>
        </div>
    </div>

    <!-- モーダル類（既存のものを再利用） -->
    <!-- 追加モーダル -->
    <div class="modal-overlay" id="addModal">
        <!-- 既存のモーダル内容をそのまま使用 -->
        <div class="modal-container">
            <div class="modal-header">
                <input type="text" class="modal-title-input" id="modalTitle" placeholder="タイトル">
                <div class="modal-tabs">
                    <button class="tab-btn" data-tab="schedule">予定</button>
                    <button class="tab-btn active" data-tab="task">タスク</button>
                    <button class="tab-btn" data-tab="class">授業</button>
                </div>
            </div>
            
            <div class="modal-body">
                <!-- タブ内容は既存のものを使用 -->
                <div class="tab-content" id="schedule-tab">
                    <div class="form-row">
                        <div class="form-group">
                            <label>開始</label>
                            <input type="date" class="form-control" id="scheduleStartDate">
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="scheduleStartTime" value="12:00">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>終了</label>
                            <input type="date" class="form-control" id="scheduleEndDate">
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="scheduleEndTime" value="13:00">
                        </div>
                    </div>
                    <div class="form-group location-group">
                        <label>場所</label>
                        <span class="location-icon">📍</span>
                        <input type="text" class="form-control location-input" id="scheduleLocation" placeholder="池袋">
                    </div>
                    <div class="form-group">
                        <label>備考</label>
                        <textarea class="form-control" id="scheduleDescription" placeholder="パソコンを持っていく"></textarea>
                    </div>
                </div>
                
                <div class="tab-content active" id="task-tab">
                    <div class="form-row">
                        <div class="form-group">
                            <label>締め切り</label>
                            <input type="date" class="form-control" id="taskDueDate">
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="taskDueTime" value="12:00">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>備考</label>
                        <textarea class="form-control" id="taskDescription"></textarea>
                    </div>
                </div>
                
                <div class="tab-content" id="class-tab">
                    <div class="form-row">
                        <div class="form-group">
                            <label>曜・限</label>
                            <select class="form-control" id="classDayOfWeek">
                                <option value="">曜日</option>
                                <option value="1">月曜日</option>
                                <option value="2">火曜日</option>
                                <option value="3">水曜日</option>
                                <option value="4">木曜日</option>
                                <option value="5">金曜日</option>
                                <option value="6">土曜日</option>
                                <option value="7">日曜日</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <select class="form-control" id="classPeriod">
                                <option value="">時限</option>
                                <option value="1">1時限</option>
                                <option value="2">2時限</option>
                                <option value="3">3時限</option>
                                <option value="4">4時限</option>
                                <option value="5">5時限</option>
                                <option value="6">6時限</option>
                                <option value="7">7時限</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>教室</label>
                        <input type="text" class="form-control" id="classRoom" placeholder="1331教室">
                    </div>
                    <div class="form-group">
                        <label>先生</label>
                        <input type="text" class="form-control" id="classInstructor" placeholder="川崎先生">
                    </div>
                    <div class="form-group">
                        <label>年度</label>
                        <select class="form-control" id="classYear">
                            <option value="">年度</option>
                            <?php for($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?>年度</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeAddModal()">キャンセル</button>
                <button class="btn btn-save" onclick="saveModal()">保存</button>
            </div>
        </div>
    </div>

    <?php include(APPPATH.'views/common/menu.php'); ?>

    <script>
        // 週切り替え関数
        function changeWeek(weeks) {
            const currentDate = new Date('<?= $weekStart ?>');
            currentDate.setDate(currentDate.getDate() + (weeks * 7));
            const newDate = currentDate.toISOString().split('T')[0];
            location.href = '<?= Uri::create('task/week') ?>/' + newDate;
        }


        // クイック追加（セルクリック時）
        function openQuickAdd(date, time) {
            openAddModal();
            
            // 選択された日付と時間を設定
            document.getElementById('scheduleStartDate').value = date;
            document.getElementById('scheduleEndDate').value = date;
            document.getElementById('taskDueDate').value = date;
            
            if (time) {
                document.getElementById('scheduleStartTime').value = time;
                document.getElementById('taskDueTime').value = time;
            }
        }

        // その他の関数は既存のものを使用
        function openAddModal() {
            const modal = document.getElementById('addModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeAddModal() {
            const modal = document.getElementById('addModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            resetModalForm();
        }

        function resetModalForm() {
            document.getElementById('modalTitle').value = '';
            document.querySelectorAll('.form-control').forEach(input => {
                if (input.type === 'text' || input.type === 'textarea') {
                    input.value = '';
                } else if (input.type === 'select-one') {
                    input.selectedIndex = 0;
                }
            });
        }

        function switchTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.add('active');
        }

        function saveModal() {
            const activeTab = document.querySelector('.tab-btn.active').dataset.tab;
            const title = document.getElementById('modalTitle').value;
            
            if (!title.trim()) {
                alert('タイトルを入力してください');
                return;
            }
            
            let formData = new FormData();
            formData.append('title', title);
            
            if (activeTab === 'schedule') {
                saveSchedule(formData);
            } else if (activeTab === 'task') {
                saveTask(formData);
            } else if (activeTab === 'class') {
                saveClass(formData);
            }
        }

        function saveSchedule(formData) {
            const startDate = document.getElementById('scheduleStartDate').value;
            const startTime = document.getElementById('scheduleStartTime').value;
            const endDate = document.getElementById('scheduleEndDate').value;
            const endTime = document.getElementById('scheduleEndTime').value;
            
            formData.append('start_date', startDate);
            formData.append('start_time', startTime);
            formData.append('end_date', endDate);
            formData.append('end_time', endTime);
            formData.append('location', document.getElementById('scheduleLocation').value);
            formData.append('description', document.getElementById('scheduleDescription').value);
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= Uri::create('schedule/create') ?>';
            
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }

        function saveTask(formData) {
            const dueDate = document.getElementById('taskDueDate').value;
            const dueTime = document.getElementById('taskDueTime').value;
            
            formData.append('due_date', dueDate);
            formData.append('due_time', dueTime);
            formData.append('description', document.getElementById('taskDescription').value);
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= Uri::create('task/create') ?>';
            
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }

        function saveClass(formData) {
            formData.append('day_of_week', document.getElementById('classDayOfWeek').value);
            formData.append('period', document.getElementById('classPeriod').value);
            formData.append('class_room', document.getElementById('classRoom').value);
            formData.append('instructor', document.getElementById('classInstructor').value);
            formData.append('year', document.getElementById('classYear').value);
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= Uri::create('class/create') ?>';
            
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }

        // イベントリスナー
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.hamburger-menu').addEventListener('click', function() {
                openMenu();
            });
            
            // タブ切り替えイベント
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    switchTab(this.dataset.tab);
                });
            });
            
            // モーダル外側クリックで閉じる
            document.getElementById('addModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAddModal();
                }
            });
            
            // ESCキーで閉じる
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAddModal();
                }
            });

            // チェックボックスのイベントリスナー
            const checkboxes = document.querySelectorAll('.task-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const taskId = this.dataset.taskId;
                    const taskItem = this.closest('.task-item');
                    
                    if (this.checked) {
                        taskItem.classList.add('task-completed');
                    } else {
                        taskItem.classList.remove('task-completed');
                    }
                });
            });
        });
    </script>

    <!-- 通知機能関連ファイルの読み込み -->
    <script src="<?= Uri::create('assets/js/notification-manager.js') ?>"></script>
    <script src="<?= Uri::create('assets/js/notification-settings.js') ?>"></script>
    <?php include(APPPATH.'views/common/notification-settings-modal.php'); ?>
</body>
</html>