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
        
        function sanitizeInput(str) {
            if (typeof str !== 'string') return str;
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
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
                
                if (!/^\d{4}-\d{2}-\d{2}$/.test(newDate)) {
                    throw new Error('Invalid date format');
                }
                
                location.href = <?= json_encode(Uri::create('task/day')) ?> + '/' + encodeURIComponent(newDate);
            } catch (e) {
                console.error('Date change error:', e);
                alert('日付の変更でエラーが発生しました');
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

        function saveModal() {
            try {
                const activeTab = document.querySelector('#addModal .tab-btn.active').dataset.tab;
                const title = document.getElementById('modalTitle').value.trim();
                
                if (!title) {
                    alert('タイトルを入力してください');
                    return;
                }
                
                if (title.length > 255) {
                    alert('タイトルは255文字以下で入力してください');
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
                
                if (!/^\d{4}-\d{2}-\d{2}$/.test(startDate) || !/^\d{4}-\d{2}-\d{2}$/.test(endDate)) {
                    alert('正しい日付形式で入力してください');
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
                
                if (!/^\d{4}-\d{2}-\d{2}$/.test(dueDate)) {
                    alert('正しい日付形式で入力してください');
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

        function submitForm(action, formData) {
            try {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = action;
                
                for (let [key, value] of formData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value || '';
                    form.appendChild(input);
                }
                
                document.body.appendChild(form);
                form.submit();
            } catch (e) {
                console.error('Form submit error:', e);
                alert('フォームの送信でエラーが発生しました');
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
            try {
                console.log('Loading data for:', itemType, itemId);
                
                if (itemType === 'task') {
                    <?php foreach ($tasks as $task): ?>
                        if (<?= (int)$task->id ?> === itemId) {
                            document.getElementById('editModalTitle').value = <?= json_encode($task->title) ?>;
                            document.getElementById('editTaskDueDate').value = <?= json_encode($task->due_date) ?>;
                            document.getElementById('editTaskDueTime').value = <?= json_encode($task->due_time) ?>;
                            document.getElementById('editTaskStatus').value = <?= json_encode($task->status) ?>;
                            document.getElementById('editTaskDescription').value = <?= json_encode($task->description) ?>;
                        }
                    <?php endforeach; ?>
                } else if (itemType === 'schedule') {
                    <?php if (isset($schedules)): ?>
                        <?php foreach ($schedules as $schedule): ?>
                            if (<?= (int)$schedule->id ?> === itemId) {
                                document.getElementById('editModalTitle').value = <?= json_encode($schedule->title) ?>;
                                const startDateTime = new Date(<?= json_encode($schedule->start_datetime) ?>);
                                const endDateTime = new Date(<?= json_encode($schedule->end_datetime) ?>);
                                
                                document.getElementById('editScheduleStartDate').value = startDateTime.toISOString().split('T')[0];
                                document.getElementById('editScheduleStartTime').value = startDateTime.toTimeString().slice(0, 5);
                                document.getElementById('editScheduleEndDate').value = endDateTime.toISOString().split('T')[0];
                                document.getElementById('editScheduleEndTime').value = endDateTime.toTimeString().slice(0, 5);
                                document.getElementById('editScheduleLocation').value = <?= json_encode($schedule->location) ?>;
                                document.getElementById('editScheduleDescription').value = <?= json_encode($schedule->description) ?>;
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
                
                if (title.length > 255) {
                    alert('タイトルは255文字以下で入力してください');
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
                    const fields = ['due_date', 'due_time', 'status', 'description'];
                    fields.forEach(field => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = field;
                        const elementId = `editTask${field.charAt(0).toUpperCase() + field.slice(1).replace('_', '')}`;
                        const value = document.getElementById(elementId).value;
                        input.value = field === 'description' ? sanitizeInput(value) : value;
                        form.appendChild(input);
                    });
                } else if (currentEditType === 'schedule') {
                    const startDate = document.getElementById('editScheduleStartDate').value;
                    const startTime = document.getElementById('editScheduleStartTime').value;
                    const endDate = document.getElementById('editScheduleEndDate').value;
                    const endTime = document.getElementById('editScheduleEndTime').value;
                    const location = document.getElementById('editScheduleLocation').value.trim();
                    const description = document.getElementById('editScheduleDescription').value.trim();
                    
                    ['start_date', 'start_time', 'end_date', 'end_time', 'location', 'description'].forEach((field, index) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = field;
                        
                        let value;
                        switch(field) {
                            case 'start_date': value = startDate; break;
                            case 'start_time': value = startTime; break;
                            case 'end_date': value = endDate; break;
                            case 'end_time': value = endTime; break;
                            case 'location': value = sanitizeInput(location); break;
                            case 'description': value = sanitizeInput(description); break;
                        }
                        
                        input.value = value || '';
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

                document.querySelector('.hamburger-menu').addEventListener('click', function() {
                    openMenu();
                });
                
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
                
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeAddModal();
                        closeEditModal();
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