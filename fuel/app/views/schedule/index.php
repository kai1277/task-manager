<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予定一覧 - タスク管理</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
</head>
<body>
    <div class="daily-view-container">
        <!-- ヘッダー -->
        <div class="daily-header">
            <button class="hamburger-menu">☰</button>
            
            <div class="header-top">
                <div class="date-display">
                    予定一覧
                </div>
                
                <div class="view-switcher">
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task') ?>'">日</button>
                    <button class="view-btn">週</button>
                    <button class="view-btn">月</button>
                </div>
            </div>
        </div>

        <!-- 予定リスト -->
        <div class="tasks-section">
            <h3 style="padding: 15px 0; margin: 0; color: var(--text-color);">予定一覧</h3>
            
            <?php if (empty($schedules)): ?>
                <div style="padding: 20px; text-align: center; color: var(--dark-gray);">
                    予定がありません
                </div>
            <?php else: ?>
                <?php foreach ($schedules as $schedule): ?>
                    <div class="task-item">
                        <div class="task-content" style="flex-direction: column; align-items: flex-start;">
                            <div style="display: flex; justify-content: space-between; width: 100%; margin-bottom: 5px;">
                                <span class="task-title"><?= $schedule->title ?></span>
                                <span class="task-time">
                                    <?= date('m/d H:i', strtotime($schedule->start_datetime)) ?>
                                    〜
                                    <?= date('H:i', strtotime($schedule->end_datetime)) ?>
                                </span>
                            </div>
                            
                            <?php if ($schedule->location): ?>
                                <div style="font-size: 12px; color: var(--dark-gray); margin-bottom: 2px;">
                                    📍 <?= $schedule->location ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($schedule->description): ?>
                                <div style="font-size: 12px; color: var(--dark-gray);">
                                    <?= $schedule->description ?>
                                </div>
                            <?php endif; ?>
                            
                            <div style="margin-top: 8px;">
                                <a href="<?= Uri::create('schedule/edit/' . $schedule->id) ?>" 
                                   style="color: var(--primary-blue); text-decoration: none; font-size: 12px; margin-right: 15px;">編集</a>
                                <a href="<?= Uri::create('schedule/delete/' . $schedule->id) ?>" 
                                   style="color: #e74c3c; text-decoration: none; font-size: 12px;"
                                   onclick="return confirm('本当に削除しますか？');">削除</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 追加ボタン -->
        <div class="add-task-section">
            <button class="add-task-btn" onclick="openAddModal()">
                ＋ 予定を追加
            </button>
        </div>

        <!-- タスク一覧に戻るボタン -->
        <div style="padding: 20px; text-align: center;">
            <a href="<?= Uri::create('task') ?>" 
               style="color: var(--primary-blue); text-decoration: none; font-weight: 500;">
               ← 日表示に戻る
            </a>
        </div>
    </div>

    <!-- 追加モーダル（予定用に調整） -->
    <div class="modal-overlay" id="addModal">
        <div class="modal-container">
            <!-- モーダルヘッダー -->
            <div class="modal-header">
                <input type="text" class="modal-title-input" id="modalTitle" placeholder="タイトル">
                
                <!-- タブ切り替え -->
                <div class="modal-tabs">
                    <button class="tab-btn active" data-tab="schedule">予定</button>
                    <button class="tab-btn" data-tab="task">タスク</button>
                    <button class="tab-btn" data-tab="class">授業</button>
                </div>
            </div>
            
            <!-- モーダルボディ -->
            <div class="modal-body">
                <!-- 予定タブ -->
                <div class="tab-content active" id="schedule-tab">
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
                <div class="tab-content" id="task-tab">
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

    <?php include(APPPATH.'views/common/menu.php'); ?>

    <script>
        // モーダル関連のJavaScript（タスク一覧と同じ）
        function openAddModal() {
            const modal = document.getElementById('addModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
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

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.hamburger-menu').addEventListener('click', function() {
                openMenu();
            });


            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    switchTab(this.dataset.tab);
                });
            });
            
            document.getElementById('addModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAddModal();
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAddModal();
                }
            });
        });
    </script>
</body>
</html>