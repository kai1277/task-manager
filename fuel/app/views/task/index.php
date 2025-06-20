<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>タスク管理 - タスク一覧</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
</head>
<body>
    <div class="daily-view-container">
        <!-- ヘッダー -->
        <div class="daily-header">
            <button class="hamburger-menu" onclick="openMenu()">☰</button>
            
            <div class="header-top">
                <div class="date-display">
                    タスク管理 (<?= count($tasks) ?>件)
                </div>
                
                <div class="view-switcher">
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/day') ?>'">日</button>
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/week') ?>'">週</button>
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/month') ?>'">月</button>
                </div>
            </div>
        </div>

        <!-- タスクリスト -->
        <div class="tasks-section">
            <h3 style="padding: 15px 0; margin: 0; color: var(--text-color);">タスク一覧</h3>
            
            <?php if (empty($tasks)): ?>
                <div style="padding: 20px; text-align: center; color: var(--dark-gray);">
                    タスクがありません
                </div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item <?= $task->status == 1 ? 'task-completed' : '' ?>">
                        <input type="checkbox" class="task-checkbox" 
                               <?= $task->status == 1 ? 'checked' : '' ?>
                               data-task-id="<?= $task->id ?>"
                               onchange="toggleTaskStatus(<?= $task->id ?>, this.checked)">
                        
                        <div class="task-content">
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                    <span class="task-title"><?= $task->title ?></span>
                                    <span class="task-time">
                                        <?= $task->due_date ? date('m/d', strtotime($task->due_date)) : '' ?>
                                        <?= $task->due_time ? date('H:i', strtotime($task->due_time)) : '' ?>
                                    </span>
                                </div>
                                
                                <?php if ($task->description): ?>
                                    <div style="font-size: 12px; color: var(--dark-gray); margin-bottom: 8px;">
                                        <?= nl2br(htmlspecialchars($task->description)) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="font-size: 11px; color: var(--dark-gray);">
                                    ステータス: <?= $task->status ? '完了' : '未完了' ?>
                                </div>
                                
                                <div style="margin-top: 8px;">
                                    <a href="<?= Uri::create('task/edit/' . $task->id) ?>" 
                                       style="color: var(--primary-blue); text-decoration: none; font-size: 12px; margin-right: 15px;">編集</a>
                                    <a href="<?= Uri::create('task/delete/' . $task->id) ?>" 
                                       style="color: #e74c3c; text-decoration: none; font-size: 12px;"
                                       onclick="return confirm('本当に削除しますか？');">削除</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 追加ボタン -->
        <div class="add-task-section">
            <button class="add-task-btn" onclick="openAddModal()">
                ＋ タスクを追加
            </button>
        </div>

        <!-- 他の機能へのリンク -->
        <div style="padding: 20px; text-align: center;">
            <a href="<?= Uri::create('schedule') ?>" 
               style="color: var(--primary-blue); text-decoration: none; font-weight: 500; margin-right: 20px;">
               予定管理
            </a>
            <a href="<?= Uri::create('class') ?>" 
               style="color: var(--primary-blue); text-decoration: none; font-weight: 500;">
               履修科目管理
            </a>
        </div>
    </div>

    <!-- タスク追加モーダル -->
    <div class="modal-overlay" id="addModal">
        <div class="modal-container">
            <!-- モーダルヘッダー -->
            <div class="modal-header">
                <input type="text" class="modal-title-input" id="modalTitle" placeholder="タスクのタイトル">
                
                <!-- タブ切り替え -->
                <div class="modal-tabs">
                    <button class="tab-btn active" data-tab="task" type="button">タスク</button>
                </div>
            </div>
            
            <!-- モーダルボディ -->
            <div class="modal-body">
                <!-- タスクタブ -->
                <div class="tab-content active" id="task-tab">
                    <div class="form-row">
                        <div class="form-group">
                            <label>締め切り日</label>
                            <input type="date" class="form-control" id="taskDueDate">
                        </div>
                        <div class="form-group">
                            <label>時間</label>
                            <input type="time" class="form-control" id="taskDueTime" value="12:00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>説明・メモ</label>
                        <textarea class="form-control" id="taskDescription" placeholder="タスクの詳細や備考"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- モーダルフッター -->
            <div class="modal-footer">
                <button class="btn btn-cancel" type="button" onclick="closeAddModal()">キャンセル</button>
                <button class="btn btn-save" type="button" onclick="saveTask()">保存</button>
            </div>
        </div>
    </div>

    <!-- サイドメニューを含める -->
    <?php include(APPPATH.'views/common/menu.php'); ?>

    <script>
        // デバッグログ
        console.log('JavaScript loaded');

        // モーダル関連の関数
        function openAddModal() {
            console.log('openAddModal called');
            
            const modal = document.getElementById('addModal');
            if (!modal) {
                console.error('Modal element not found');
                return;
            }
            
            modal.style.display = 'block';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // 今日の日付をデフォルトに設定
            const today = new Date().toISOString().split('T')[0];
            const taskDueDate = document.getElementById('taskDueDate');
            if (taskDueDate) {
                taskDueDate.value = today;
            }
        }

        function closeAddModal() {
            console.log('closeAddModal called');
            
            const modal = document.getElementById('addModal');
            if (!modal) {
                console.error('Modal element not found');
                return;
            }
            
            modal.style.display = 'none';
            modal.classList.remove('active');
            document.body.style.overflow = '';
            
            resetModalForm();
        }

        function resetModalForm() {
            document.getElementById('modalTitle').value = '';
            document.getElementById('taskDescription').value = '';
        }

        function saveTask() {
            console.log('saveTask called');
            
            const title = document.getElementById('modalTitle').value;
            const dueDate = document.getElementById('taskDueDate').value;
            const dueTime = document.getElementById('taskDueTime').value;
            const description = document.getElementById('taskDescription').value;
            
            if (!title.trim()) {
                alert('タイトルを入力してください');
                return;
            }
            
            if (!dueDate) {
                alert('期限日を入力してください');
                return;
            }
            
            // フォームを作成して送信
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= Uri::create('task/create') ?>';
            
            // フィールドを追加
            const fields = {
                'title': title,
                'due_date': dueDate,
                'due_time': dueTime,
                'description': description
            };
            
            for (const [key, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value || '';
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }

        // タスクステータス切り替え（シンプル版）
        function toggleTaskStatus(taskId, isChecked) {
            console.log('Toggle task status:', taskId, isChecked);
            
            // ページリロードで更新（シンプル版）
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= Uri::create('task/toggle_status') ?>/' + taskId;
            
            document.body.appendChild(form);
            form.submit();
        }

        // メニュー関数（基本的なもの）
        function openMenu() {
            const sideMenu = document.getElementById('sideMenu');
            const menuOverlay = document.getElementById('menuOverlay');
            
            if (sideMenu) {
                sideMenu.classList.add('active');
            }
            if (menuOverlay) {
                menuOverlay.classList.add('active');
            }
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            const sideMenu = document.getElementById('sideMenu');
            const menuOverlay = document.getElementById('menuOverlay');
            
            if (sideMenu) {
                sideMenu.classList.remove('active');
            }
            if (menuOverlay) {
                menuOverlay.classList.remove('active');
            }
            document.body.style.overflow = '';
        }

        // ページ読み込み完了後に実行
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            
            // モーダル外側クリックで閉じる
            const modal = document.getElementById('addModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeAddModal();
                    }
                });
            }
            
            // ESCキーで閉じる
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAddModal();
                }
            });

            // メニューオーバーレイクリックで閉じる
            const menuOverlay = document.getElementById('menuOverlay');
            if (menuOverlay) {
                menuOverlay.addEventListener('click', closeMenu);
            }
        });
    </script>

    <style>
        /* モーダル用追加スタイル */
        .modal-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background: rgba(0, 0, 0, 0.5) !important;
            z-index: 1000 !important;
            display: none !important;
            justify-content: center !important;
            align-items: center !important;
        }

        .modal-overlay.active {
            display: flex !important;
        }

        .modal-container {
            background: white !important;
            border-radius: 12px !important;
            width: 90% !important;
            max-width: 500px !important;
            max-height: 80vh !important;
            overflow-y: auto !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
        }

        .task-completed {
            opacity: 0.6;
        }

        .task-completed .task-title {
            text-decoration: line-through;
        }
    </style>

    <!-- 通知機能関連ファイルの読み込み -->
    <script src="<?= Uri::create('assets/js/notification-manager.js') ?>"></script>
    <script src="<?= Uri::create('assets/js/notification-settings.js') ?>"></script>
    <?php include(APPPATH.'views/common/notification-settings-modal.php'); ?>
</body>
</html>