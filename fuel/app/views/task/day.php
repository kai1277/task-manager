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
                    <button onclick="changeDate(-1); event.stopPropagation();" 
                            style="background:none;border:none;font-size:18px;cursor:pointer;margin-right:10px;">‹</button>
                    <span style="margin: 0 10px;">
                        <?= date('Y年m月d日', strtotime($selectedDate)) ?>
                    </span>
                    <button onclick="changeDate(1); event.stopPropagation();" 
                            style="background:none;border:none;font-size:18px;cursor:pointer;margin-left:10px;">›</button>
                </div>
                
                <div class="view-switcher">
                    <button class="view-btn active">日</button>
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/week') ?>'">週</button>
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/month') ?>'">月</button>
                </div>
            </div>
        </div>

        <!-- タスクリスト（クリック可能に修正） -->
        <div class="tasks-section">
            <?php foreach ($tasks as $task): ?>
                <div class="task-item <?= $task->status == 1 ? 'task-completed' : '' ?>">
                    <input type="checkbox" class="task-checkbox" 
                           <?= $task->status == 1 ? 'checked' : '' ?>
                           data-task-id="<?= $task->id ?>"
                           onclick="event.stopPropagation();">
                    
                    <div class="task-content clickable-item" 
                         onclick="openEditModal(<?= $task->id ?>, 'task')"
                         style="cursor: pointer;">
                        <span class="task-title"><?= $task->title ?></span>
                        <span class="task-time">
                            <?= $task->due_date && $task->due_time ? 
                                date('H:i', strtotime($task->due_time)) : '' ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- タスクが空の場合 -->
            <?php if (empty($tasks)): ?>
                <div style="padding: 20px; text-align: center; color: #666;">
                    今日のタスクはありません
                </div>
            <?php endif; ?>
        </div>

        <!-- 追加ボタン -->
        <div class="add-task-section">
            <button class="add-task-btn" onclick="openAddModal()">
                ＋ タスクを追加
            </button>
        </div>

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
                                <div class="schedule-item task clickable-timeline-item" 
                                    onclick="openEditModal(<?= $task->id ?>, 'task')"
                                    style="cursor: pointer;"
                                    title="クリックして編集">
                                    <?= $task->title ?>
                                    <small>(<?= date('H:i', strtotime($task->due_time)) ?>)</small>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            
                            // スケジュールを表示（開始時間のみ、長さを視覚化）
                            if (isset($schedules)):
                                foreach ($schedules as $schedule):
                                    $startHour = date('H', strtotime($schedule->start_datetime));
                                    $endHour = date('H', strtotime($schedule->end_datetime));
                                    
                                    // 開始時間の時間帯のみに表示（長さを視覚的に表現）
                                    if ($startHour == $hour):
                                        $startTime = date('H:i', strtotime($schedule->start_datetime));
                                        $endTime = date('H:i', strtotime($schedule->end_datetime));
                                        
                                        // 継続時間を計算（分単位）
                                        $startTimestamp = strtotime($schedule->start_datetime);
                                        $endTimestamp = strtotime($schedule->end_datetime);
                                        $durationMinutes = ($endTimestamp - $startTimestamp) / 60;
                                        $durationHours = $durationMinutes / 60;
                                        
                                        // 高さを計算（1時間 = 60px基準）
                                        $height = max(40, $durationMinutes); // 最小40px
                                        $maxHeight = 240; // 最大4時間分
                                        if ($height > $maxHeight) $height = $maxHeight;
                            ?>
                                <div class="schedule-item clickable-timeline-item long-schedule" 
                                    onclick="openEditModal(<?= $schedule->id ?>, 'schedule')"
                                    style="cursor: pointer; 
                                            height: <?= $height ?>px; 
                                            border-left: 6px solid var(--primary-blue);
                                            position: relative;
                                            z-index: 5;"
                                    title="クリックして編集">
                                    <div class="schedule-content">
                                        <div class="schedule-title-main"><?= $schedule->title ?></div>
                                        <div class="schedule-time-main"><?= $startTime ?>-<?= $endTime ?></div>
                                        <?php if ($schedule->location): ?>
                                            <div class="schedule-location-main">📍<?= $schedule->location ?></div>
                                        <?php endif; ?>
                                        <?php if ($durationHours >= 1): ?>
                                            <div class="schedule-duration">⏱️ <?= number_format($durationHours, 1) ?>時間</div>
                                        <?php endif; ?>
                                    </div>
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

    <!-- 編集モーダル（新規追加） -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-container">
            <!-- モーダルヘッダー -->
            <div class="modal-header">
                <input type="text" class="modal-title-input" id="editModalTitle" placeholder="タイトル">
                
                <!-- タブ切り替え（編集時は対象のタブのみ表示） -->
                <div class="modal-tabs">
                    <button class="tab-btn" id="editScheduleTab" data-tab="schedule" style="display: none;">予定</button>
                    <button class="tab-btn" id="editTaskTab" data-tab="task" style="display: none;">タスク</button>
                    <button class="tab-btn" id="editClassTab" data-tab="class" style="display: none;">授業</button>
                </div>
            </div>
            
            <!-- モーダルボディ -->
            <div class="modal-body">
                <!-- 予定編集タブ -->
                <div class="tab-content" id="edit-schedule-tab" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>開始</label>
                            <input type="date" class="form-control" id="editScheduleStartDate">
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="editScheduleStartTime">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>終了</label>
                            <input type="date" class="form-control" id="editScheduleEndDate">
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="editScheduleEndTime">
                        </div>
                    </div>
                    
                    <div class="form-group location-group">
                        <label>場所</label>
                        <span class="location-icon">📍</span>
                        <input type="text" class="form-control location-input" id="editScheduleLocation">
                    </div>
                    
                    <div class="form-group">
                        <label>備考</label>
                        <textarea class="form-control" id="editScheduleDescription"></textarea>
                    </div>
                </div>
                
                <!-- タスク編集タブ -->
                <div class="tab-content" id="edit-task-tab" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>締め切り</label>
                            <input type="date" class="form-control" id="editTaskDueDate">
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="editTaskDueTime">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>ステータス</label>
                        <select class="form-control" id="editTaskStatus">
                            <option value="0">未完了</option>
                            <option value="1">完了</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>備考</label>
                        <textarea class="form-control" id="editTaskDescription"></textarea>
                    </div>
                </div>
                
                <!-- 授業編集タブ -->
                <div class="tab-content" id="edit-class-tab" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>曜・限</label>
                            <select class="form-control" id="editClassDayOfWeek">
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
                            <select class="form-control" id="editClassPeriod">
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
                        <input type="text" class="form-control" id="editClassRoom">
                    </div>
                    
                    <div class="form-group">
                        <label>先生</label>
                        <input type="text" class="form-control" id="editClassInstructor">
                    </div>
                    
                    <div class="form-group">
                        <label>年度</label>
                        <select class="form-control" id="editClassYear">
                            <option value="">年度</option>
                            <?php for($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                                <option value="<?= $y ?>"><?= $y ?>年度</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>開始月</label>
                            <select class="form-control" id="editClassStartMonth">
                                <option value="">開始月</option>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>"><?= $m ?>月</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>終了月</label>
                            <select class="form-control" id="editClassEndMonth">
                                <option value="">終了月</option>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>"><?= $m ?>月</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>説明</label>
                        <textarea class="form-control" id="editClassDescription"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- モーダルフッター -->
            <div class="modal-footer">
                <button class="btn btn-delete" onclick="deleteItem()" id="deleteBtn">削除</button>
                <button class="btn btn-cancel" onclick="closeEditModal()">キャンセル</button>
                <button class="btn btn-save" onclick="updateItem()" id="updateBtn">更新</button>
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
                <button class="calendar-btn calendar-btn-confirm" onclick="confirmDateSelection()">決定</button>
            </div>
        </div>
    </div>

    <?php include(APPPATH.'views/common/menu.php'); ?>

    <script>
        // 日付切り替え関数（修正版）
        function changeDate(days) {
            const currentDate = new Date('<?= $selectedDate ?>');
            currentDate.setDate(currentDate.getDate() + days);
            const newDate = currentDate.toISOString().split('T')[0];
            
            // 正しいURLに修正
            location.href = '<?= Uri::create('task/day') ?>/' + newDate;
        }

        // その他の既存JavaScript関数...
        
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
            
            // 現在の日付をデフォルトに設定
            const currentDate = '<?= $selectedDate ?>';
            document.getElementById('scheduleStartDate').value = currentDate;
            document.getElementById('scheduleEndDate').value = currentDate;
            document.getElementById('taskDueDate').value = currentDate;
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

        // 編集モーダル関連（既存のコードがあれば残す）
        let currentEditItem = null;
        let currentEditType = null;

        function openEditModal(itemId, itemType) {
            currentEditItem = itemId;
            currentEditType = itemType;
            
            document.getElementById('editModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            
            hideAllEditTabs();
            showEditTab(itemType);
            loadItemData(itemId, itemType);
        }

        function hideAllEditTabs() {
            document.getElementById('editScheduleTab').style.display = 'none';
            document.getElementById('editTaskTab').style.display = 'none';
            document.getElementById('editClassTab').style.display = 'none';
            
            document.getElementById('edit-schedule-tab').style.display = 'none';
            document.getElementById('edit-task-tab').style.display = 'none';
            document.getElementById('edit-class-tab').style.display = 'none';
        }

        function showEditTab(itemType) {
            const tabButton = document.getElementById(`edit${itemType.charAt(0).toUpperCase() + itemType.slice(1)}Tab`);
            const tabContent = document.getElementById(`edit-${itemType}-tab`);
            
            if (tabButton && tabContent) {
                tabButton.style.display = 'block';
                tabButton.classList.add('active');
                tabContent.style.display = 'block';
            }
        }

        function loadItemData(itemId, itemType) {
            console.log('Loading data for:', itemType, itemId);
            
            if (itemType === 'task') {
                <?php foreach ($tasks as $task): ?>
                    if (<?= $task->id ?> === itemId) {
                        document.getElementById('editModalTitle').value = '<?= addslashes($task->title) ?>';
                        document.getElementById('editTaskDueDate').value = '<?= $task->due_date ?>';
                        document.getElementById('editTaskDueTime').value = '<?= $task->due_time ?>';
                        document.getElementById('editTaskStatus').value = '<?= $task->status ?>';
                        document.getElementById('editTaskDescription').value = '<?= addslashes($task->description) ?>';
                    }
                <?php endforeach; ?>
            } else if (itemType === 'schedule') {
                <?php if (isset($schedules)): ?>
                    <?php foreach ($schedules as $schedule): ?>
                        if (<?= $schedule->id ?> === itemId) {
                            document.getElementById('editModalTitle').value = '<?= addslashes($schedule->title) ?>';
                            const startDateTime = new Date('<?= $schedule->start_datetime ?>');
                            const endDateTime = new Date('<?= $schedule->end_datetime ?>');
                            
                            document.getElementById('editScheduleStartDate').value = startDateTime.toISOString().split('T')[0];
                            document.getElementById('editScheduleStartTime').value = startDateTime.toTimeString().slice(0, 5);
                            document.getElementById('editScheduleEndDate').value = endDateTime.toISOString().split('T')[0];
                            document.getElementById('editScheduleEndTime').value = endDateTime.toTimeString().slice(0, 5);
                            document.getElementById('editScheduleLocation').value = '<?= addslashes($schedule->location) ?>';
                            document.getElementById('editScheduleDescription').value = '<?= addslashes($schedule->description) ?>';
                        }
                    <?php endforeach; ?>
                <?php endif; ?>
            }
        }

        function updateItem() {
            if (!currentEditItem || !currentEditType) return;
            
            const title = document.getElementById('editModalTitle').value;
            if (!title.trim()) {
                alert('タイトルを入力してください');
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `<?= Uri::create('') ?>${currentEditType}/edit/${currentEditItem}`;
            
            const titleInput = document.createElement('input');
            titleInput.type = 'hidden';
            titleInput.name = 'title';
            titleInput.value = title;
            form.appendChild(titleInput);
            
            if (currentEditType === 'task') {
                const fields = ['due_date', 'due_time', 'status', 'description'];
                fields.forEach(field => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = field;
                    input.value = document.getElementById(`editTask${field.charAt(0).toUpperCase() + field.slice(1).replace('_', '')}`).value;
                    form.appendChild(input);
                });
            } else if (currentEditType === 'schedule') {
                const startDate = document.getElementById('editScheduleStartDate').value;
                const startTime = document.getElementById('editScheduleStartTime').value;
                const endDate = document.getElementById('editScheduleEndDate').value;
                const endTime = document.getElementById('editScheduleEndTime').value;
                
                ['start_date', 'start_time', 'end_date', 'end_time', 'location', 'description'].forEach((field, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = field;
                    if (field === 'start_date') input.value = startDate;
                    else if (field === 'start_time') input.value = startTime;
                    else if (field === 'end_date') input.value = endDate;
                    else if (field === 'end_time') input.value = endTime;
                    else input.value = document.getElementById(`editSchedule${field.charAt(0).toUpperCase() + field.slice(1)}`).value;
                    form.appendChild(input);
                });
            }
            
            document.body.appendChild(form);
            form.submit();
        }

        function deleteItem() {
            if (!currentEditItem || !currentEditType) return;
            
            if (!confirm('本当に削除しますか？この操作は取り消せません。')) {
                return;
            }
            
            location.href = `<?= Uri::create('') ?>${currentEditType}/delete/${currentEditItem}`;
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
            document.body.style.overflow = '';
            
            resetEditForm();
            currentEditItem = null;
            currentEditType = null;
        }

        function resetEditForm() {
            document.getElementById('editModalTitle').value = '';
            document.querySelectorAll('#editModal .form-control').forEach(input => {
                if (input.type === 'text' || input.type === 'textarea' || input.type === 'date' || input.type === 'time') {
                    input.value = '';
                } else if (input.type === 'select-one') {
                    input.selectedIndex = 0;
                }
            });
            
            document.querySelectorAll('#editModal .tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
        }

        // DOMContentLoaded イベント
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
            
            const editModal = document.getElementById('editModal');
            if (editModal) {
                editModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeEditModal();
                    }
                });
            }
            
            // ESCキーで閉じる
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAddModal();
                    closeEditModal();
                }
            });
        });
        
        // 1分ごとに現在時刻の線を更新
        setInterval(updateCurrentTimeLine, 60000);
    </script>
</body>
</html>