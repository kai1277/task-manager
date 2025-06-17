<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日表示 - タスク管理</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
</head>
<body>
    <div class="daily-view-container">
        <!-- ヘッダー -->
        <div class="daily-header">
            <button class="hamburger-menu">☰</button>
            
            <div class="header-top">
                <div class="date-display" onclick="openDatePicker()">
                    <button onclick="changeDate(-1); event.stopPropagation();" style="background:none;border:none;font-size:18px;cursor:pointer;margin-right:10px;">‹</button>
                    <span style="margin: 0 10px;">
                        <?= date('Y年m月d日', strtotime($selectedDate)) ?>
                    </span>
                    <button onclick="changeDate(1); event.stopPropagation();" style="background:none;border:none;font-size:18px;cursor:pointer;margin-left:10px;">›</button>
                </div>
                
                <div class="view-switcher">
                    <button class="view-btn active">日</button>
                    <button class="view-btn">週</button>
                    <button class="view-btn">月</button>
                </div>
            </div>
        </div>

        <!-- タスクリスト -->
        <div class="tasks-section">
            <?php foreach ($tasks as $task): ?>
                <div class="task-item <?= $task->status == 1 ? 'task-completed' : '' ?>">
                    <input type="checkbox" class="task-checkbox" 
                           <?= $task->status == 1 ? 'checked' : '' ?>
                           data-task-id="<?= $task->id ?>">
                    
                    <div class="task-content">
                        <span class="task-title"><?= $task->title ?></span>
                        <span class="task-time">
                            <?= $task->due_date && $task->due_time ? 
                                date('H:i', strtotime($task->due_time)) : '' ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 追加ボタン -->
        <div class="add-task-section">
            <button class="add-task-btn" onclick="openAddModal()">
                ＋ タスクを追加
            </button>
        </div>

        <!-- タイムライン -->
        <!-- タイムライン -->
        <div class="timeline-section" id="timeline">
            <div class="timeline-container">
                <?php for ($hour = 0; $hour < 24; $hour++): ?>
                    <div class="timeline-hour" id="hour-<?= $hour ?>">
                        <div class="hour-label">
                            <?= sprintf('%02d:00', $hour) ?>
                        </div>
                        <div class="hour-content">
                            <?php 
                            // タスクを表示
                            foreach ($tasks as $task):
                                if ($task->due_time && date('H', strtotime($task->due_time)) == $hour):
                            ?>
                                <div class="schedule-item task">
                                    <?= $task->title ?>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            
                            // スケジュールを表示
                            if (isset($schedules)):
                                foreach ($schedules as $schedule):
                                    $startHour = date('H', strtotime($schedule->start_datetime));
                                    $endHour = date('H', strtotime($schedule->end_datetime));
                                    
                                    // この時間帯にスケジュールが含まれるかチェック
                                    if ($startHour <= $hour && $hour <= $endHour):
                                        $startTime = date('H:i', strtotime($schedule->start_datetime));
                                        $endTime = date('H:i', strtotime($schedule->end_datetime));
                            ?>
                                <div class="schedule-item">
                                    <?= $schedule->title ?>
                                    <?php if ($hour == $startHour): ?>
                                        <small>(<?= $startTime ?>-<?= $endTime ?>)</small>
                                    <?php endif; ?>
                                </div>
                            <?php 
                                    endif;
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                <?php endfor; ?>
                
                <!-- 現在時刻の線 -->
                <div class="current-time-line" id="currentTimeLine" style="display: none;">
                    <div class="current-time-dot"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 追加モーダル -->
    <div class="modal-overlay" id="addModal">
        <div class="modal-container">
            <!-- モーダルヘッダー -->
            <div class="modal-header">
                <input type="text" class="modal-title-input" id="modalTitle" placeholder="タイトル">
                
                <!-- タブ切り替え -->
                <div class="modal-tabs">
                    <button class="tab-btn" data-tab="schedule">予定</button>
                    <button class="tab-btn active" data-tab="task">タスク</button>
                    <button class="tab-btn" data-tab="class">授業</button>
                </div>
            </div>
            
            <!-- モーダルボディ -->
            <div class="modal-body">
                <!-- 予定タブ -->
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
                
                <!-- タスクタブ -->
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
                
                <!-- 授業タブ -->
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
            
            <!-- モーダルフッター -->
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeAddModal()">キャンセル</button>
                <button class="btn btn-save" onclick="saveModal()">保存</button>
            </div>
        </div>
    </div>

    <!-- 日付選択カレンダー -->
    <div class="date-picker-overlay" id="datePickerOverlay">
        <div class="date-picker-container">
            <!-- カレンダーヘッダー -->
            <div class="calendar-header">
                <button class="calendar-close" onclick="closeDatePicker()">×</button>
                <div class="calendar-month-nav">
                    <button class="calendar-nav-btn" onclick="changeCalendarMonth(-1)">‹</button>
                    <div class="calendar-month-year" id="calendarMonthYear"></div>
                    <button class="calendar-nav-btn" onclick="changeCalendarMonth(1)">›</button>
                </div>
                <div class="calendar-selected-date" id="calendarSelectedDate">
                    日付を選択してください
                </div>
            </div>
            
            <!-- カレンダーボディ -->
            <div class="calendar-body">
                <div class="calendar-weekdays">
                    <div class="calendar-weekday">日</div>
                    <div class="calendar-weekday">月</div>
                    <div class="calendar-weekday">火</div>
                    <div class="calendar-weekday">水</div>
                    <div class="calendar-weekday">木</div>
                    <div class="calendar-weekday">金</div>
                    <div class="calendar-weekday">土</div>
                </div>
                <div class="calendar-days" id="calendarDays">
                    <!-- カレンダーの日付がJavaScriptで生成される -->
                </div>
            </div>
            
            <!-- カレンダーフッター -->
            <div class="calendar-footer">
                <button class="calendar-btn calendar-btn-cancel" onclick="closeDatePicker()">キャンセル</button>
                <button class="calendar-btn calendar-btn-today" onclick="goToToday()">今日</button>
            </div>
        </div>
    </div>

    <?php include(APPPATH.'views/common/menu.php'); ?>

    <script>
        // カレンダー関連JavaScript
        let calendarCurrentDate = new Date('<?= $selectedDate ?>');
        let calendarSelectedDate = new Date('<?= $selectedDate ?>');

        // カレンダーを開く
        function openDatePicker() {
            document.getElementById('datePickerOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
            updateCalendar();
        }

        // カレンダーを閉じる
        function closeDatePicker() {
            document.getElementById('datePickerOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        // カレンダーの月を変更
        function changeCalendarMonth(delta) {
            calendarCurrentDate.setMonth(calendarCurrentDate.getMonth() + delta);
            updateCalendar();
        }

        // カレンダーを更新
        function updateCalendar() {
            const monthNames = ['1月', '2月', '3月', '4月', '5月', '6月', 
                              '7月', '8月', '9月', '10月', '11月', '12月'];
            
            const year = calendarCurrentDate.getFullYear();
            const month = calendarCurrentDate.getMonth();
            
            // ヘッダー更新
            document.getElementById('calendarMonthYear').textContent = 
                year + '年 ' + monthNames[month];
            
            // 選択日表示更新
            const selectedStr = calendarSelectedDate.getFullYear() + '年' + 
                              (calendarSelectedDate.getMonth() + 1) + '月' + 
                              calendarSelectedDate.getDate() + '日';
            document.getElementById('calendarSelectedDate').textContent = selectedStr;
            
            // カレンダー日付生成
            generateCalendarDays(year, month);
        }

        // カレンダー日付を生成
        function generateCalendarDays(year, month) {
            const daysContainer = document.getElementById('calendarDays');
            daysContainer.innerHTML = '';
            
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            for (let i = 0; i < 42; i++) {
                const date = new Date(startDate);
                date.setDate(startDate.getDate() + i);
                
                const dayDiv = document.createElement('div');
                dayDiv.className = 'calendar-day';
                dayDiv.textContent = date.getDate();
                
                // クラス設定
                if (date.getMonth() !== month) {
                    dayDiv.classList.add('other-month');
                }
                
                if (date.getTime() === today.getTime()) {
                    dayDiv.classList.add('today');
                }
                
                if (date.getTime() === calendarSelectedDate.getTime()) {
                    dayDiv.classList.add('selected');
                }
                
                // イベント設定
                dayDiv.addEventListener('click', function() {
                    selectCalendarDate(date);
                });
                
                daysContainer.appendChild(dayDiv);
            }
        }

        // カレンダー日付を選択
        function selectCalendarDate(date) {
            calendarSelectedDate = new Date(date);
            updateCalendar();
            
            // 日付フォーマット
            const dateStr = date.getFullYear() + '-' + 
                          String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                          String(date.getDate()).padStart(2, '0');
            
            // ページ移動
            setTimeout(() => {
                closeDatePicker();
                location.href = '<?= Uri::create('task/index') ?>/' + dateStr;
            }, 200);
        }

        // 日付切り替え関数
        function changeDate(days) {
            const currentDate = new Date('<?= $selectedDate ?>');
            currentDate.setDate(currentDate.getDate() + days);
            const newDate = currentDate.toISOString().split('T')[0];
            location.href = '<?= Uri::create('task/index') ?>/' + newDate;
        }

        // 今日に戻るボタン（オプション）
        function goToToday() {
            closeDatePicker();
            location.href = '<?= Uri::create('task') ?>';
        }

        // 現在時刻の線を表示
        function updateCurrentTimeLine() {
            const now = new Date();
            const hour = now.getHours();
            const minute = now.getMinutes();
            
            const hourElement = document.getElementById('hour-' + hour);
            if (hourElement) {
                const currentTimeLine = document.getElementById('currentTimeLine');
                const offsetTop = hourElement.offsetTop + (minute / 60) * 60;
                
                currentTimeLine.style.top = offsetTop + 'px';
                currentTimeLine.style.display = 'block';
            }
        }

        // モーダルを開く
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

        // モーダルを閉じる
        function closeAddModal() {
            const modal = document.getElementById('addModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            
            // フォームをリセット
            resetModalForm();
        }

        // フォームリセット
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

        // タブ切り替え
        function switchTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.add('active');
        }

        // 保存処理
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

        // 予定保存
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

        // タスク保存
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

        // 授業保存
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

        // DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            updateCurrentTimeLine();

            document.querySelector('.hamburger-menu').addEventListener('click', function() {
                openMenu();
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
            
            // 表示切り替えボタン
            const viewBtns = document.querySelectorAll('.view-btn');
            viewBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    viewBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    console.log('切り替え:', this.textContent);
                });
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
                    closeDatePicker();
                }
            });

            // オーバーレイクリックで閉じる
            document.getElementById('datePickerOverlay').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDatePicker();
                }
            });
        });
        
        // 1分ごとに現在時刻の線を更新
        setInterval(updateCurrentTimeLine, 60000);
    </script>
</body>
</html>