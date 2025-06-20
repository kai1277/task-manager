<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>月表示 - タスク管理</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
</head>
<body>
    <div class="month-view-container">
        <!-- ヘッダー -->
        <div class="month-header">
            <button class="hamburger-menu" onclick="openMenu()">☰</button>
            
            <div class="header-top">
                <div class="date-display" onclick="openDatePicker()">
                    <button onclick="changeMonth(-1); event.stopPropagation();" style="background:none;border:none;font-size:18px;cursor:pointer;margin-right:10px;">‹</button>
                    <span style="margin: 0 10px;">
                        <?= $year ?>年<?= $month ?>月
                    </span>
                    <button onclick="changeMonth(1); event.stopPropagation();" style="background:none;border:none;font-size:18px;cursor:pointer;margin-left:10px;">›</button>
                </div>
                
                <div class="view-switcher">
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/day') ?>'">日</button>
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/week') ?>'">週</button>
                    <button class="view-btn active">月</button>
                </div>
            </div>
        </div>

        <!-- 上部タスクリスト -->
        <div class="month-tasks-section">
            <?php 
            $todayTasks = array();
            if (isset($calendarData[date('Y-m-d')])) {
                $todayTasks = $calendarData[date('Y-m-d')]['tasks'];
            }
            ?>
            
            <div class="task-summary">
                <?php if (!empty($todayTasks)): ?>
                    <?php foreach (array_slice($todayTasks, 0, 2) as $task): ?>
                        <div class="task-summary-item <?= $task->status == 1 ? 'completed' : '' ?>" 
                             onclick="openEditModal(<?= $task->id ?>, 'task')">
                            <span class="task-summary-title"><?= $task->title ?></span>
                            <?php if ($task->due_time): ?>
                                <span class="task-summary-time"><?= date('H:i', strtotime($task->due_time)) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($todayTasks) > 2): ?>
                        <div class="task-summary-more" onclick="location.href='<?= Uri::create('task') ?>'">
                            他 <?= count($todayTasks) - 2 ?>件のタスク
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-tasks">今日のタスクはありません</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- カレンダー -->
        <div class="month-calendar">
            <!-- 曜日ヘッダー -->
            <div class="calendar-weekdays">
                <div class="weekday">日</div>
                <div class="weekday">月</div>
                <div class="weekday">火</div>
                <div class="weekday">水</div>
                <div class="weekday">木</div>
                <div class="weekday">金</div>
                <div class="weekday">土</div>
            </div>

            <!-- カレンダー本体 -->
            <div class="calendar-grid">
                <?php foreach ($calendarData as $date => $dayData): ?>
                    <div class="calendar-day 
                                <?= !$dayData['is_current_month'] ? 'other-month' : '' ?>
                                <?= $dayData['is_today'] ? 'today' : '' ?>
                                <?= (!empty($dayData['tasks']) || !empty($dayData['schedules']) || !empty($dayData['classes'])) ? 'has-events' : '' ?>"
                         data-date="<?= $date ?>"
                         onclick="openDayDetail('<?= $date ?>')">
                        
                        <!-- 日付 -->
                        <div class="day-number"><?= $dayData['day'] ?></div>
                        
                        <!-- イベント表示エリア -->
                        <div class="day-events">
                            <?php 
                            $eventCount = 0;
                            $maxEvents = 3; // 1日に表示する最大イベント数
                            
                            // 履修科目を表示（最優先）
                            foreach ($dayData['classes'] as $class):
                                if ($eventCount >= $maxEvents) break;
                                $eventCount++;
                            ?>
                                <div class="event-item class-event" 
                                     onclick="event.stopPropagation(); openEditModal(<?= $class->id ?>, 'class')"
                                     title="<?= $class->title ?> - <?= $class->class_room ?>">
                                    <span class="event-text"><?= mb_strlen($class->title) > 6 ? mb_substr($class->title, 0, 6) . '...' : $class->title ?></span>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- スケジュールを表示 -->
                            <?php foreach ($dayData['schedules'] as $schedule):
                                if ($eventCount >= $maxEvents) break;
                                $eventCount++;
                            ?>
                                <div class="event-item schedule-event" 
                                     onclick="event.stopPropagation(); openEditModal(<?= $schedule->id ?>, 'schedule')"
                                     title="<?= $schedule->title ?> - <?= $schedule->location ?>">
                                    <span class="event-text"><?= mb_strlen($schedule->title) > 6 ? mb_substr($schedule->title, 0, 6) . '...' : $schedule->title ?></span>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- タスクを表示 -->
                            <?php foreach ($dayData['tasks'] as $task):
                                if ($eventCount >= $maxEvents) break;
                                $eventCount++;
                            ?>
                                <div class="event-item task-event <?= $task->status == 1 ? 'completed' : '' ?>" 
                                     onclick="event.stopPropagation(); openEditModal(<?= $task->id ?>, 'task')"
                                     title="<?= $task->title ?> - <?= $task->due_time ? date('H:i', strtotime($task->due_time)) : '時間未設定' ?>">
                                    <span class="event-text"><?= mb_strlen($task->title) > 6 ? mb_substr($task->title, 0, 6) . '...' : $task->title ?></span>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- 追加イベントがある場合の表示 -->
                            <?php 
                            $totalEvents = count($dayData['classes']) + count($dayData['schedules']) + count($dayData['tasks']);
                            if ($totalEvents > $maxEvents):
                            ?>
                                <div class="event-more" 
                                     onclick="event.stopPropagation(); openDayDetail('<?= $date ?>')">
                                    +<?= $totalEvents - $maxEvents ?>件
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 追加ボタン -->
        <div class="add-section">
            <button class="add-btn" onclick="openAddModal()">
                ＋ 追加
            </button>
        </div>
    </div>

    <!-- 日詳細モーダル -->
    <div class="modal-overlay" id="dayDetailModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title" id="dayDetailTitle"></h2>
                <button class="modal-close" onclick="closeDayDetail()">×</button>
            </div>
            
            <div class="modal-body">
                <div id="dayDetailContent">
                    <!-- 日の詳細内容がJavaScriptで動的に生成される -->
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeDayDetail()">閉じる</button>
                <button class="btn btn-save" onclick="goToDayView()">日表示で見る</button>
            </div>
        </div>
    </div>

    <!-- 追加モーダル（既存のものを再利用） -->
    <div class="modal-overlay" id="addModal">
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
                <!-- 既存のタブ内容を使用 -->
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
        let currentDetailDate = null;

        // 月切り替え関数
        function changeMonth(months) {
            const currentDate = new Date('<?= $selectedDate ?>');
            currentDate.setMonth(currentDate.getMonth() + months);
            const newDate = currentDate.toISOString().split('T')[0];
            location.href = '<?= Uri::create('task/month') ?>/' + newDate;
        }

        // 日詳細モーダルを開く
        function openDayDetail(date) {
            currentDetailDate = date;
            const dateObj = new Date(date);
            const title = `${dateObj.getFullYear()}年${dateObj.getMonth() + 1}月${dateObj.getDate()}日`;
            
            document.getElementById('dayDetailTitle').textContent = title;
            document.getElementById('dayDetailModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // 日の詳細内容を生成
            generateDayDetailContent(date);
        }

        // 日詳細モーダルを閉じる
        function closeDayDetail() {
            document.getElementById('dayDetailModal').classList.remove('active');
            document.body.style.overflow = '';
            currentDetailDate = null;
        }

        // 日表示に移動
        function goToDayView() {
            if (currentDetailDate) {
                location.href = '<?= Uri::create('task/day') ?>/' + currentDetailDate;
            }
        }

        // 日詳細内容を生成
        function generateDayDetailContent(date) {
            // PHP側のデータを使用して内容を生成
            const calendarData = <?= json_encode($calendarData) ?>;
            const dayData = calendarData[date];
            
            if (!dayData) {
                document.getElementById('dayDetailContent').innerHTML = '<p>データがありません</p>';
                return;
            }
            
            let content = '';
            
            // 履修科目
            if (dayData.classes && dayData.classes.length > 0) {
                content += '<h4>📚 履修科目</h4>';
                dayData.classes.forEach(cls => {
                    content += `<div class="detail-item class-item">
                        <div class="detail-title">${cls.title}</div>
                        <div class="detail-info">${cls.class_room || ''} ${cls.instructor || ''}</div>
                    </div>`;
                });
            }
            
            // スケジュール
            if (dayData.schedules && dayData.schedules.length > 0) {
                content += '<h4>📅 予定</h4>';
                dayData.schedules.forEach(schedule => {
                    const startTime = new Date(schedule.start_datetime).toLocaleTimeString('ja-JP', {hour: '2-digit', minute: '2-digit'});
                    const endTime = new Date(schedule.end_datetime).toLocaleTimeString('ja-JP', {hour: '2-digit', minute: '2-digit'});
                    content += `<div class="detail-item schedule-item">
                        <div class="detail-title">${schedule.title}</div>
                        <div class="detail-info">${startTime}-${endTime} ${schedule.location || ''}</div>
                    </div>`;
                });
            }
            
            // タスク
            if (dayData.tasks && dayData.tasks.length > 0) {
                content += '<h4>✅ タスク</h4>';
                dayData.tasks.forEach(task => {
                    const status = task.status == 1 ? '✓' : '○';
                    const time = task.due_time ? new Date('2000-01-01 ' + task.due_time).toLocaleTimeString('ja-JP', {hour: '2-digit', minute: '2-digit'}) : '';
                    content += `<div class="detail-item task-item ${task.status == 1 ? 'completed' : ''}">
                        <div class="detail-title">${status} ${task.title}</div>
                        <div class="detail-info">${time}</div>
                    </div>`;
                });
            }
            
            if (content === '') {
                content = '<p>この日には予定がありません</p>';
            }
            
            document.getElementById('dayDetailContent').innerHTML = content;
        }

        // その他の関数（既存のものを使用）
        function openAddModal() {
            const modal = document.getElementById('addModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // 今日の日付をデフォルトに設定
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('scheduleStartDate').value = today;
            document.getElementById('scheduleEndDate').value = today;
            document.getElementById('taskDueDate').value = today;
        }

        function closeAddModal() {
            const modal = document.getElementById('addModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            resetModalForm();
        }

        function resetModalForm() {
            document.getElementById('modalTitle').value = '';
            document.querySelectorAll('#addModal .form-control').forEach(input => {
                if (input.type === 'text' || input.type === 'textarea') {
                    input.value = '';
                } else if (input.type === 'select-one') {
                    input.selectedIndex = 0;
                }
            });
        }

        function switchTab(tabName) {
            document.querySelectorAll('#addModal .tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('#addModal .tab-content').forEach(content => content.classList.remove('active'));
            
            document.querySelector(`#addModal [data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.add('active');
        }

        function saveModal() {
            const activeTab = document.querySelector('#addModal .tab-btn.active').dataset.tab;
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
            document.querySelectorAll('#addModal .tab-btn').forEach(btn => {
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
            
            document.getElementById('dayDetailModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDayDetail();
                }
            });
            
            // ESCキーで閉じる
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAddModal();
                    closeDayDetail();
                }
            });
        });
    </script>
    <!-- 通知機能関連ファイルの読み込み -->
    <script src="<?= Uri::create('assets/js/notification-manager.js') ?>"></script>
    <script src="<?= Uri::create('assets/js/notification-settings.js') ?>"></script>
    <?php include(APPPATH.'views/common/notification-settings-modal.php'); ?>
</body>
</html>