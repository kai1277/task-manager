<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日表示 - タスク管理</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
</head>
<body>
    <div class="daily-view-container">
        <div class="daily-header">
            <button class="hamburger-menu">☰</button>
            
            <div class="header-top">
                <div class="date-display" onclick="openDatePicker()">
                    <button onclick="changeDate(-1); event.stopPropagation();" 
                            style="background:none;border:none;font-size:18px;cursor:pointer;margin-right:10px;">‹</button>
                    <span style="margin: 0 10px;">
                        <?= Security::htmlentities(date('Y年m月d日', strtotime($selectedDate))) ?>
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

        <div class="tasks-section">
            <?php foreach ($tasks as $task): ?>
                <div class="task-item <?= $task->status == 1 ? 'task-completed' : '' ?>">
                    <input type="checkbox" class="task-checkbox" 
                           <?= $task->status == 1 ? 'checked' : '' ?>
                           data-task-id="<?= (int)$task->id ?>"
                           onclick="event.stopPropagation();">
                    
                    <div class="task-content clickable-item" 
                         onclick="openEditModal(<?= (int)$task->id ?>, 'task')"
                         style="cursor: pointer;">
                        <span class="task-title"><?= Security::htmlentities($task->title) ?></span>
                        <span class="task-time">
                            <?= $task->due_date && $task->due_time ? 
                                Security::htmlentities(date('H:i', strtotime($task->due_time))) : '' ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($tasks)): ?>
                <div style="padding: 20px; text-align: center; color: #666;">
                    今日のタスクはありません
                </div>
            <?php endif; ?>
        </div>

        <div class="add-task-section">
            <button class="add-task-btn" onclick="openAddModal()">
                ＋ タスクを追加
            </button>
        </div>

        <div class="timeline-section" id="timeline">
            <div class="timeline-container">
                <?php for ($hour = 0; $hour < 24; $hour++): ?>
                    <div class="timeline-hour" id="hour-<?= $hour ?>">
                        <div class="hour-label">
                            <?= sprintf('%02d:00', $hour) ?>
                        </div>
                        <div class="hour-content">
                            <?php 
                            foreach ($tasks as $task):
                                if ($task->due_time && date('H', strtotime($task->due_time)) == $hour):
                            ?>
                                <div class="schedule-item task clickable-timeline-item" 
                                    onclick="openEditModal(<?= (int)$task->id ?>, 'task')"
                                    style="cursor: pointer;"
                                    title="クリックして編集">
                                    <?= Security::htmlentities($task->title) ?>
                                    <small>(<?= Security::htmlentities(date('H:i', strtotime($task->due_time))) ?>)</small>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            
                            if (isset($schedules)):
                                foreach ($schedules as $schedule):
                                    $startHour = date('H', strtotime($schedule->start_datetime));
                                    $endHour = date('H', strtotime($schedule->end_datetime));
                                    
                                    if ($startHour == $hour):
                                        $startTime = date('H:i', strtotime($schedule->start_datetime));
                                        $endTime = date('H:i', strtotime($schedule->end_datetime));
                                        
                                        $startTimestamp = strtotime($schedule->start_datetime);
                                        $endTimestamp = strtotime($schedule->end_datetime);
                                        $durationMinutes = ($endTimestamp - $startTimestamp) / 60;
                                        $durationHours = $durationMinutes / 60;
                                        
                                        $height = max(40, $durationMinutes);
                                        $maxHeight = 240;
                                        if ($height > $maxHeight) $height = $maxHeight;
                            ?>
                                <div class="schedule-item clickable-timeline-item long-schedule" 
                                    onclick="openEditModal(<?= (int)$schedule->id ?>, 'schedule')"
                                    style="cursor: pointer; 
                                            height: <?= (int)$height ?>px; 
                                            border-left: 6px solid var(--primary-blue);
                                            position: relative;
                                            z-index: 5;"
                                    title="クリックして編集">
                                    <div class="schedule-content">
                                        <div class="schedule-title-main"><?= Security::htmlentities($schedule->title) ?></div>
                                        <div class="schedule-time-main"><?= Security::htmlentities($startTime) ?>-<?= Security::htmlentities($endTime) ?></div>
                                        <?php if ($schedule->location): ?>
                                            <div class="schedule-location-main">📍<?= Security::htmlentities($schedule->location) ?></div>
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
                
                <div class="current-time-line" id="currentTimeLine" style="display: none;">
                    <div class="current-time-dot"></div>
                </div>
            </div>
        </div>

    <div class="modal-overlay" id="addModal">
        <div class="modal-container">
            <div class="modal-header">
                <input type="text" class="modal-title-input" id="modalTitle" placeholder="タイトル" maxlength="255">
                
                <div class="modal-tabs">
                    <button class="tab-btn" data-tab="schedule">予定</button>
                    <button class="tab-btn active" data-tab="task">タスク</button>
                    <button class="tab-btn" data-tab="class">授業</button>
                </div>
            </div>
            
            <div class="modal-body">
                <div class="tab-content" id="schedule-tab">
                    <div class="form-row">
                        <div class="form-group">
                            <label>開始</label>
                            <input type="date" class="form-control" id="scheduleStartDate" required>
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="scheduleStartTime" value="12:00">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>終了</label>
                            <input type="date" class="form-control" id="scheduleEndDate" required>
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="scheduleEndTime" value="13:00">
                        </div>
                    </div>
                    
                    <div class="form-group location-group">
                        <label>場所</label>
                        <span class="location-icon">📍</span>
                        <input type="text" class="form-control location-input" id="scheduleLocation" 
                               placeholder="池袋" maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label>備考</label>
                        <textarea class="form-control" id="scheduleDescription" 
                                  placeholder="パソコンを持っていく" maxlength="1000"></textarea>
                    </div>
                </div>
                
                <div class="tab-content active" id="task-tab">
                    <div class="form-row">
                        <div class="form-group">
                            <label>締め切り</label>
                            <input type="date" class="form-control" id="taskDueDate" required>
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="taskDueTime" value="12:00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>備考</label>
                        <textarea class="form-control" id="taskDescription" maxlength="1000"></textarea>
                    </div>
                </div>
                
                <div class="tab-content" id="class-tab">
                    <div class="form-row">
                        <div class="form-group">
                            <label>曜・限</label>
                            <select class="form-control" id="classDayOfWeek" required>
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
                            <select class="form-control" id="classPeriod" required>
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
                        <input type="text" class="form-control" id="classRoom" 
                               placeholder="1331教室" maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label>先生</label>
                        <input type="text" class="form-control" id="classInstructor" 
                               placeholder="川崎先生" maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label>年度</label>
                        <select class="form-control" id="classYear" required>
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

    <div class="modal-overlay" id="editModal">
        <div class="modal-container">
            <div class="modal-header">
                <input type="text" class="modal-title-input" id="editModalTitle" 
                       placeholder="タイトル" maxlength="255">
                
                <div class="modal-tabs">
                    <button class="tab-btn" id="editScheduleTab" data-tab="schedule" style="display: none;">予定</button>
                    <button class="tab-btn" id="editTaskTab" data-tab="task" style="display: none;">タスク</button>
                    <button class="tab-btn" id="editClassTab" data-tab="class" style="display: none;">授業</button>
                </div>
            </div>
            
            <div class="modal-body">
                <div class="tab-content" id="edit-schedule-tab" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>開始</label>
                            <input type="date" class="form-control" id="editScheduleStartDate" required>
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="editScheduleStartTime">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>終了</label>
                            <input type="date" class="form-control" id="editScheduleEndDate" required>
                        </div>
                        <div class="form-group">
                            <input type="time" class="form-control" id="editScheduleEndTime">
                        </div>
                    </div>
                    
                    <div class="form-group location-group">
                        <label>場所</label>
                        <span class="location-icon">📍</span>
                        <input type="text" class="form-control location-input" id="editScheduleLocation" maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label>備考</label>
                        <textarea class="form-control" id="editScheduleDescription" maxlength="1000"></textarea>
                    </div>
                </div>
                
                <div class="tab-content" id="edit-task-tab" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>締め切り</label>
                            <input type="date" class="form-control" id="editTaskDueDate" required>
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
                        <textarea class="form-control" id="editTaskDescription" maxlength="1000"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-delete" onclick="deleteItem()" id="deleteBtn">削除</button>
                <button class="btn btn-cancel" onclick="closeEditModal()">キャンセル</button>
                <button class="btn btn-save" onclick="updateItem()" id="updateBtn">更新</button>
            </div>
        </div>
    </div>

    <?php include(APPPATH.'views/common/menu.php'); ?>

    <script>
        window.CSRF_TOKEN = <?= json_encode($csrf_token) ?>;
        window.SELECTED_DATE = <?= json_encode($selectedDate) ?>;
        
        // セキュリティ強化: より厳密なサニタイズ関数
        function sanitizeInput(str) {
            if (typeof str !== 'string') return str;
            
            // HTMLエンティティエンコード
            const div = document.createElement('div');
            div.textContent = str;
            let sanitized = div.innerHTML;
            
            // 追加のセキュリティ対策: 危険な文字の除去
            sanitized = sanitized
                .replace(/[<>]/g, '') // HTMLタグを完全に除去
                .replace(/javascript:/gi, '') // JavaScriptプロトコルを除去
                .replace(/on\w+=/gi, '') // イベントハンドラを除去
                .replace(/eval\(/gi, '') // eval関数を除去
                .replace(/expression\(/gi, ''); // CSS expressionを除去
            
            return sanitized;
        }
        
        // セキュリティ強化: 入力値検証関数
        function validateInput(value, type, maxLength = null) {
            if (typeof value !== 'string') return false;
            
            // 長さ制限チェック
            if (maxLength && value.length > maxLength) return false;
            
            // タイプ別検証
            switch (type) {
                case 'date':
                    return /^\d{4}-\d{2}-\d{2}$/.test(value);
                case 'time':
                    return /^\d{2}:\d{2}$/.test(value);
                case 'number':
                    return /^\d+$/.test(value);
                case 'text':
                    // 基本的な文字のみ許可
                    return !/[<>\"'&]/.test(value);
                default:
                    return true;
            }
        }
        
        function safeRequest(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            if (options.method === 'POST' || options.method === 'PUT') {
                if (options.body && typeof options.body === 'object') {
                    options.body.fuel_csrf_token = window.CSRF_TOKEN;
                    options.body = JSON.stringify(options.body);
                }
            }
            
            return fetch(url, { ...defaultOptions, ...options });
        }

        function changeDate(days) {
            try {
                const currentDate = new Date(window.SELECTED_DATE);
                if (isNaN(currentDate.getTime())) {
                    throw new Error('Invalid date');
                }
                
                currentDate.setDate(currentDate.getDate() + days);
                const newDate = currentDate.toISOString().split('T')[0];
                
                if (!validateInput(newDate, 'date')) {
                    throw new Error('Invalid date format');
                }
                
                location.href = <?= json_encode(Uri::create('task/day')) ?> + '/' + encodeURIComponent(newDate);
            } catch (e) {
                console.error('Date change error:', e);
                alert('日付の変更でエラーが発生しました');
            }
        }

        function openMenu() {
            try {
                const sideMenu = document.getElementById('sideMenu');
                const menuOverlay = document.getElementById('menuOverlay');
                
                if (sideMenu) {
                    sideMenu.classList.add('active');
                }
                if (menuOverlay) {
                    menuOverlay.classList.add('active');
                }
                document.body.style.overflow = 'hidden';
            } catch (e) {
                console.error('Menu open error:', e);
            }
        }

        function closeMenu() {
            try {
                const sideMenu = document.getElementById('sideMenu');
                const menuOverlay = document.getElementById('menuOverlay');
                
                if (sideMenu) {
                    sideMenu.classList.remove('active');
                }
                if (menuOverlay) {
                    menuOverlay.classList.remove('active');
                }
                document.body.style.overflow = '';
            } catch (e) {
                console.error('Menu close error:', e);
            }
        }

        function updateCurrentTimeLine() {
            try {
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
            } catch (e) {
                console.error('Timeline update error:', e);
            }
        }

        function openAddModal() {
            try {
                const modal = document.getElementById('addModal');
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                const currentDate = window.SELECTED_DATE;
                document.getElementById('scheduleStartDate').value = currentDate;
                document.getElementById('scheduleEndDate').value = currentDate;
                document.getElementById('taskDueDate').value = currentDate;
            } catch (e) {
                console.error('Modal open error:', e);
                alert('モーダルを開くことができませんでした');
            }
        }

        function closeAddModal() {
            try {
                const modal = document.getElementById('addModal');
                modal.classList.remove('active');
                document.body.style.overflow = '';
                resetModalForm();
            } catch (e) {
                console.error('Modal close error:', e);
            }
        }

        function resetModalForm() {
            try {
                document.getElementById('modalTitle').value = '';
                document.querySelectorAll('#addModal .form-control').forEach(input => {
                    if (input.type === 'text' || input.type === 'textarea') {
                        input.value = '';
                    } else if (input.type === 'select-one') {
                        input.selectedIndex = 0;
                    }
                });
            } catch (e) {
                console.error('Form reset error:', e);
            }
        }

        function switchTab(tabName) {
            try {
                const allowedTabs = ['schedule', 'task', 'class'];
                if (!allowedTabs.includes(tabName)) {
                    throw new Error('Invalid tab name');
                }
                
                document.querySelectorAll('#addModal .tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('#addModal .tab-content').forEach(content => content.classList.remove('active'));
                
                document.querySelector(`#addModal [data-tab="${tabName}"]`).classList.add('active');
                document.getElementById(`${tabName}-tab`).classList.add('active');
            } catch (e) {
                console.error('Tab switch error:', e);
            }
        }

        function submitForm(action, formData) {
            try {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = action;
                
                for (let [key, value] of formData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    // セキュリティ強化: 値をサニタイズ
                    input.value = sanitizeInput(value || '');
                    form.appendChild(input);
                }
                
                document.body.appendChild(form);
                form.submit();
            } catch (e) {
                console.error('Form submit error:', e);
                alert('フォームの送信でエラーが発生しました');
            }
        }

        function saveModal() {
            try {
                const activeTab = document.querySelector('#addModal .tab-btn.active').dataset.tab;
                const title = document.getElementById('modalTitle').value.trim();
                
                if (!title) {
                    alert('タイトルを入力してください');
                    return;
                }
                
                // セキュリティ強化: 入力値検証
                if (!validateInput(title, 'text', 255)) {
                    alert('タイトルに不正な文字が含まれているか、255文字を超えています');
                    return;
                }
                
                let formData = new FormData();
                formData.append('title', sanitizeInput(title));
                formData.append('fuel_csrf_token', window.CSRF_TOKEN);
                
                if (activeTab === 'schedule') {
                    saveSchedule(formData);
                } else if (activeTab === 'task') {
                    saveTask(formData);
                } else if (activeTab === 'class') {
                    saveClass(formData);
                }
            } catch (e) {
                console.error('Save modal error:', e);
                alert('保存でエラーが発生しました');
            }
        }

        function saveSchedule(formData) {
            try {
                const startDate = document.getElementById('scheduleStartDate').value;
                const startTime = document.getElementById('scheduleStartTime').value;
                const endDate = document.getElementById('scheduleEndDate').value;
                const endTime = document.getElementById('scheduleEndTime').value;
                const location = document.getElementById('scheduleLocation').value.trim();
                const description = document.getElementById('scheduleDescription').value.trim();
                
                if (!startDate || !endDate) {
                    alert('開始日と終了日を入力してください');
                    return;
                }
                
                // セキュリティ強化: 入力値検証
                if (!validateInput(startDate, 'date') || !validateInput(endDate, 'date')) {
                    alert('正しい日付形式で入力してください');
                    return;
                }
                
                if (startTime && !validateInput(startTime, 'time')) {
                    alert('正しい時刻形式で入力してください');
                    return;
                }
                
                if (endTime && !validateInput(endTime, 'time')) {
                    alert('正しい時刻形式で入力してください');
                    return;
                }
                
                if (!validateInput(location, 'text', 255)) {
                    alert('場所に不正な文字が含まれているか、255文字を超えています');
                    return;
                }
                
                if (!validateInput(description, 'text', 1000)) {
                    alert('備考に不正な文字が含まれているか、1000文字を超えています');
                    return;
                }
                
                formData.append('start_date', startDate);
                formData.append('start_time', startTime);
                formData.append('end_date', endDate);
                formData.append('end_time', endTime);
                formData.append('location', sanitizeInput(location));
                formData.append('description', sanitizeInput(description));
                
                submitForm(<?= json_encode(Uri::create('schedule/create')) ?>, formData);
            } catch (e) {
                console.error('Save schedule error:', e);
                alert('予定の保存でエラーが発生しました');
            }
        }

        function saveTask(formData) {
            try {
                const dueDate = document.getElementById('taskDueDate').value;
                const dueTime = document.getElementById('taskDueTime').value;
                const description = document.getElementById('taskDescription').value.trim();
                
                if (!dueDate) {
                    alert('締切日を入力してください');
                    return;
                }
                
                // セキュリティ強化: 入力値検証
                if (!validateInput(dueDate, 'date')) {
                    alert('正しい日付形式で入力してください');
                    return;
                }
                
                if (dueTime && !validateInput(dueTime, 'time')) {
                    alert('正しい時刻形式で入力してください');
                    return;
                }
                
                if (!validateInput(description, 'text', 1000)) {
                    alert('備考に不正な文字が含まれているか、1000文字を超えています');
                    return;
                }
                
                formData.append('due_date', dueDate);
                formData.append('due_time', dueTime);
                formData.append('description', sanitizeInput(description));
                
                submitForm(<?= json_encode(Uri::create('task/create')) ?>, formData);
            } catch (e) {
                console.error('Save task error:', e);
                alert('タスクの保存でエラーが発生しました');
            }
        }

        function saveClass(formData) {
            try {
                const dayOfWeek = document.getElementById('classDayOfWeek').value;
                const period = document.getElementById('classPeriod').value;
                const classRoom = document.getElementById('classRoom').value.trim();
                const instructor = document.getElementById('classInstructor').value.trim();
                const year = document.getElementById('classYear').value;
                
                if (!dayOfWeek || !period || !year) {
                    alert('曜日、時限、年度は必須です');
                    return;
                }
                
                // セキュリティ強化: 入力値検証
                if (!validateInput(dayOfWeek, 'number') || !validateInput(period, 'number') || !validateInput(year, 'number')) {
                    alert('曜日、時限、年度は数値で入力してください');
                    return;
                }
                
                if (!validateInput(classRoom, 'text', 100)) {
                    alert('教室に不正な文字が含まれているか、100文字を超えています');
                    return;
                }
                
                if (!validateInput(instructor, 'text', 100)) {
                    alert('先生名に不正な文字が含まれているか、100文字を超えています');
                    return;
                }
                
                formData.append('day_of_week', dayOfWeek);
                formData.append('period', period);
                formData.append('class_room', sanitizeInput(classRoom));
                formData.append('instructor', sanitizeInput(instructor));
                formData.append('year', year);
                
                submitForm(<?= json_encode(Uri::create('class/create')) ?>, formData);
            } catch (e) {
                console.error('Save class error:', e);
                alert('授業の保存でエラーが発生しました');
            }
        }

        let currentEditItem = null;
        let currentEditType = null;

        function openEditModal(itemId, itemType) {
            try {
                if (!Number.isInteger(itemId) || itemId <= 0) {
                    throw new Error('Invalid item ID');
                }
                
                const allowedTypes = ['task', 'schedule', 'class'];
                if (!allowedTypes.includes(itemType)) {
                    throw new Error('Invalid item type');
                }
                
                currentEditItem = itemId;
                currentEditType = itemType;
                
                document.getElementById('editModal').classList.add('active');
                document.body.style.overflow = 'hidden';
                
                hideAllEditTabs();
                showEditTab(itemType);
                loadItemData(itemId, itemType);
            } catch (e) {
                console.error('Edit modal open error:', e);
                alert('編集モーダルを開くことができませんでした');
            }
        }

        function hideAllEditTabs() {
            const editTabs = ['editScheduleTab', 'editTaskTab', 'editClassTab'];
            const editContents = ['edit-schedule-tab', 'edit-task-tab', 'edit-class-tab'];
            
            editTabs.forEach(id => {
                const element = document.getElementById(id);
                if (element) element.style.display = 'none';
            });
            
            editContents.forEach(id => {
                const element = document.getElementById(id);
                if (element) element.style.display = 'none';
            });
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
            try {
                console.log('Loading data for:', itemType, itemId);
                
                if (itemType === 'task') {
                    <?php foreach ($tasks as $task): ?>
                        if (<?= (int)$task->id ?> === itemId) {
                            // セキュリティ強化: データをサニタイズして設定
                            document.getElementById('editModalTitle').value = <?= json_encode(Security::htmlentities($task->title)) ?>;
                            document.getElementById('editTaskDueDate').value = <?= json_encode($task->due_date) ?>;
                            document.getElementById('editTaskDueTime').value = <?= json_encode($task->due_time) ?>;
                            document.getElementById('editTaskStatus').value = <?= json_encode($task->status) ?>;
                            document.getElementById('editTaskDescription').value = <?= json_encode(Security::htmlentities($task->description)) ?>;
                        }
                    <?php endforeach; ?>
                } else if (itemType === 'schedule') {
                    <?php if (isset($schedules)): ?>
                        <?php foreach ($schedules as $schedule): ?>
                            if (<?= (int)$schedule->id ?> === itemId) {
                                // セキュリティ強化: データをサニタイズして設定
                                document.getElementById('editModalTitle').value = <?= json_encode(Security::htmlentities($schedule->title)) ?>;
                                const startDateTime = new Date(<?= json_encode($schedule->start_datetime) ?>);
                                const endDateTime = new Date(<?= json_encode($schedule->end_datetime) ?>);
                                
                                document.getElementById('editScheduleStartDate').value = startDateTime.toISOString().split('T')[0];
                                document.getElementById('editScheduleStartTime').value = startDateTime.toTimeString().slice(0, 5);
                                document.getElementById('editScheduleEndDate').value = endDateTime.toISOString().split('T')[0];
                                document.getElementById('editScheduleEndTime').value = endDateTime.toTimeString().slice(0, 5);
                                document.getElementById('editScheduleLocation').value = <?= json_encode(Security::htmlentities($schedule->location)) ?>;
                                document.getElementById('editScheduleDescription').value = <?= json_encode(Security::htmlentities($schedule->description)) ?>;
                            }
                        <?php endforeach; ?>
                    <?php endif; ?>
                }
            } catch (e) {
                console.error('Load item data error:', e);
                alert('データの読み込みでエラーが発生しました');
            }
        }

        function updateItem() {
            try {
                if (!currentEditItem || !currentEditType) return;
                
                const title = document.getElementById('editModalTitle').value.trim();
                if (!title) {
                    alert('タイトルを入力してください');
                    return;
                }
                
                // セキュリティ強化: 入力値検証
                if (!validateInput(title, 'text', 255)) {
                    alert('タイトルに不正な文字が含まれているか、255文字を超えています');
                    return;
                }
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `<?= Uri::create('') ?>${currentEditType}/edit/${currentEditItem}`;
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'fuel_csrf_token';
                csrfInput.value = window.CSRF_TOKEN;
                form.appendChild(csrfInput);
                
                const titleInput = document.createElement('input');
                titleInput.type = 'hidden';
                titleInput.name = 'title';
                titleInput.value = sanitizeInput(title);
                form.appendChild(titleInput);
                
                if (currentEditType === 'task') {
                    const dueDate = document.getElementById('editTaskDueDate').value;
                    const dueTime = document.getElementById('editTaskDueTime').value;
                    const status = document.getElementById('editTaskStatus').value;
                    const description = document.getElementById('editTaskDescription').value.trim();
                    
                    // セキュリティ強化: 入力値検証
                    if (!validateInput(dueDate, 'date')) {
                        alert('正しい日付形式で入力してください');
                        return;
                    }
                    
                    if (dueTime && !validateInput(dueTime, 'time')) {
                        alert('正しい時刻形式で入力してください');
                        return;
                    }
                    
                    if (!validateInput(status, 'number')) {
                        alert('ステータスは数値で入力してください');
                        return;
                    }
                    
                    if (!validateInput(description, 'text', 1000)) {
                        alert('備考に不正な文字が含まれているか、1000文字を超えています');
                        return;
                    }
                    
                    const fields = [
                        {name: 'due_date', value: dueDate},
                        {name: 'due_time', value: dueTime},
                        {name: 'status', value: status},
                        {name: 'description', value: sanitizeInput(description)}
                    ];
                    
                    fields.forEach(field => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = field.name;
                        input.value = field.value || '';
                        form.appendChild(input);
                    });
                    
                } else if (currentEditType === 'schedule') {
                    const startDate = document.getElementById('editScheduleStartDate').value;
                    const startTime = document.getElementById('editScheduleStartTime').value;
                    const endDate = document.getElementById('editScheduleEndDate').value;
                    const endTime = document.getElementById('editScheduleEndTime').value;
                    const location = document.getElementById('editScheduleLocation').value.trim();
                    const description = document.getElementById('editScheduleDescription').value.trim();
                    
                    // セキュリティ強化: 入力値検証
                    if (!validateInput(startDate, 'date') || !validateInput(endDate, 'date')) {
                        alert('正しい日付形式で入力してください');
                        return;
                    }
                    
                    if (startTime && !validateInput(startTime, 'time')) {
                        alert('正しい開始時刻形式で入力してください');
                        return;
                    }
                    
                    if (endTime && !validateInput(endTime, 'time')) {
                        alert('正しい終了時刻形式で入力してください');
                        return;
                    }
                    
                    if (!validateInput(location, 'text', 255)) {
                        alert('場所に不正な文字が含まれているか、255文字を超えています');
                        return;
                    }
                    
                    if (!validateInput(description, 'text', 1000)) {
                        alert('備考に不正な文字が含まれているか、1000文字を超えています');
                        return;
                    }
                    
                    const fields = [
                        {name: 'start_date', value: startDate},
                        {name: 'start_time', value: startTime},
                        {name: 'end_date', value: endDate},
                        {name: 'end_time', value: endTime},
                        {name: 'location', value: sanitizeInput(location)},
                        {name: 'description', value: sanitizeInput(description)}
                    ];
                    
                    fields.forEach(field => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = field.name;
                        input.value = field.value || '';
                        form.appendChild(input);
                    });
                }
                
                document.body.appendChild(form);
                form.submit();
            } catch (e) {
                console.error('Update item error:', e);
                alert('更新でエラーが発生しました');
            }
        }

        function deleteItem() {
            try {
                if (!currentEditItem || !currentEditType) return;
                
                if (!confirm('本当に削除しますか？この操作は取り消せません。')) {
                    return;
                }
                
                location.href = `<?= Uri::create('') ?>${currentEditType}/delete/${currentEditItem}`;
            } catch (e) {
                console.error('Delete item error:', e);
                alert('削除でエラーが発生しました');
            }
        }

        function closeEditModal() {
            try {
                document.getElementById('editModal').classList.remove('active');
                document.body.style.overflow = '';
                
                resetEditForm();
                currentEditItem = null;
                currentEditType = null;
            } catch (e) {
                console.error('Edit modal close error:', e);
            }
        }

        function resetEditForm() {
            try {
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
            } catch (e) {
                console.error('Edit form reset error:', e);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            try {
                updateCurrentTimeLine();

                const hamburgerMenu = document.querySelector('.hamburger-menu');
                if (hamburgerMenu) {
                    hamburgerMenu.addEventListener('click', function(e) {
                        e.preventDefault();
                        openMenu();
                    });
                }
                
                const checkboxes = document.querySelectorAll('.task-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const taskId = parseInt(this.dataset.taskId);
                        const taskItem = this.closest('.task-item');
                        
                        if (isNaN(taskId) || taskId <= 0) {
                            console.error('Invalid task ID');
                            return;
                        }
                        
                        if (this.checked) {
                            taskItem.classList.add('task-completed');
                        } else {
                            taskItem.classList.remove('task-completed');
                        }
                    });
                });
                
                document.querySelectorAll('#addModal .tab-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const tabName = this.dataset.tab;
                        if (tabName) {
                            switchTab(tabName);
                        }
                    });
                });
                
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
                
                const menuOverlay = document.getElementById('menuOverlay');
                if (menuOverlay) {
                    menuOverlay.addEventListener('click', closeMenu);
                }
                
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeAddModal();
                        closeEditModal();
                        closeMenu();
                    }
                });
            } catch (e) {
                console.error('DOM initialization error:', e);
            }
        });
        
        setInterval(updateCurrentTimeLine, 60000);
    </script>
                
    <script src="<?= Uri::create('assets/js/notification-manager.js') ?>"></script>
    <script src="<?= Uri::create('assets/js/notification-settings.js') ?>"></script>
    <?php include(APPPATH.'views/common/notification-settings-modal.php'); ?>
</body>
</html>