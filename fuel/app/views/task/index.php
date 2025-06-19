<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>タスク管理 - タスク一覧</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
    <!-- Knockout.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.min.js"></script>
</head>
<body>
    <div class="daily-view-container" data-bind="with: taskManager">
        <!-- ヘッダー -->
        <div class="daily-header">
            <button class="hamburger-menu" onclick="openMenu()">☰</button>
            
            <div class="header-top">
                <div class="date-display">
                    タスク管理
                    <span data-bind="text: '(' + tasks().length + '件)'"></span>
                </div>
                
                <div class="view-switcher">
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/day') ?>'">日</button>
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/week') ?>'">週</button>
                    <button class="view-btn" onclick="location.href='<?= Uri::create('task/month') ?>'">月</button>
                </div>
            </div>
        </div>

        <!-- ローディング表示 -->
        <div data-bind="visible: isLoading" class="loading-overlay">
            <div class="loading-spinner">読み込み中...</div>
        </div>

        <!-- エラーメッセージ -->
        <div data-bind="visible: errorMessage().length > 0, text: errorMessage" class="error-message"></div>

        <!-- 成功メッセージ -->
        <div data-bind="visible: successMessage().length > 0, text: successMessage" class="success-message"></div>

        <!-- タスクリスト -->
        <div class="tasks-section">
            <h3 style="padding: 15px 0; margin: 0; color: var(--text-color);">
                タスク一覧
                <span data-bind="text: '(完了: ' + completedCount() + '/' + tasks().length + ')'"></span>
            </h3>
            
            <!-- フィルター -->
            <div class="filter-section">
                <select data-bind="value: statusFilter">
                    <option value="all">すべて</option>
                    <option value="pending">未完了のみ</option>
                    <option value="completed">完了済みのみ</option>
                </select>
            </div>
            
            <!-- タスクがない場合 -->
            <div data-bind="visible: filteredTasks().length === 0" style="padding: 20px; text-align: center; color: var(--dark-gray);">
                <span data-bind="text: statusFilter() === 'all' ? 'タスクがありません' : '該当するタスクがありません'"></span>
            </div>

            <!-- タスク一覧 -->
            <div data-bind="foreach: filteredTasks">
                <div class="task-item" data-bind="css: { 'task-updating': isUpdating }">
                    <div class="task-content" style="flex-direction: column; align-items: flex-start;">
                        <div style="display: flex; justify-content: space-between; width: 100%; margin-bottom: 5px; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" 
                                       data-bind="checked: status, click: $parent.toggleTaskStatus, disable: isUpdating"
                                       style="width: 18px; height: 18px; cursor: pointer;">
                                <span data-bind="text: title, css: { 'task-completed': status }" class="task-title"></span>
                            </div>
                            <span class="task-time" data-bind="text: formattedDateTime"></span>
                        </div>
                        
                        <div data-bind="visible: description().length > 0" style="font-size: 12px; color: var(--dark-gray); margin-bottom: 2px; margin-left: 28px;">
                            📝 <span data-bind="text: description"></span>
                        </div>
                        
                        <div style="font-size: 11px; margin-left: 28px;" data-bind="css: { 'text-success': status, 'text-warning': !status() }">
                            ステータス: <span data-bind="text: status() ? '完了' : '未完了'"></span>
                        </div>
                        
                        <div style="margin-top: 8px; margin-left: 28px;">
                            <button data-bind="click: $parent.editTask, disable: isUpdating" 
                                    style="color: var(--primary-blue); background: none; border: none; font-size: 12px; margin-right: 15px; cursor: pointer;">編集</button>
                            <button data-bind="click: $parent.deleteTask, disable: isUpdating" 
                                    style="color: #e74c3c; background: none; border: none; font-size: 12px; cursor: pointer;">削除</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 追加ボタン -->
        <div class="add-task-section">
            <button class="add-task-btn" data-bind="click: showAddModal, disable: isLoading">
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

    <!-- 追加/編集モーダル -->
    <!-- ko with: taskManager -->
    <div class="modal-overlay" data-bind="visible: showModal, click: function(data, event) { if (event.target === event.currentTarget) closeModal(); }">
        <div class="modal-container" data-bind="with: currentTask, event: { click: function() { return true; } }">
            <!-- モーダルヘッダー -->
            <div class="modal-header">
                <input type="text" class="modal-title-input" data-bind="value: title" placeholder="タスクのタイトル">
                <button class="modal-close" data-bind="click: $parent.closeModal">×</button>
            </div>
            
            <!-- モーダルボディ -->
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>締め切り日</label>
                        <input type="date" class="form-control" data-bind="value: dueDate">
                    </div>
                    <div class="form-group">
                        <label>時間</label>
                        <input type="time" class="form-control" data-bind="value: dueTime">
                    </div>
                </div>
                
                <div class="form-group" data-bind="visible: $parent.isEditMode">
                    <label>ステータス</label>
                    <select class="form-control" data-bind="value: status">
                        <option value="false">未完了</option>
                        <option value="true">完了</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>説明・メモ</label>
                    <textarea class="form-control" data-bind="value: description" placeholder="タスクの詳細や備考"></textarea>
                </div>
            </div>
            
            <!-- モーダルフッター -->
            <div class="modal-footer">
                <button class="btn btn-cancel" data-bind="click: $parent.closeModal, disable: $parent.isProcessing">キャンセル</button>
                <button class="btn btn-save" data-bind="click: $parent.saveTask, disable: $parent.isProcessing">
                    <span data-bind="text: $parent.isEditMode() ? '更新' : '保存'"></span>
                </button>
            </div>
        </div>
    </div>
    <!-- /ko -->

    <!-- 削除確認モーダル -->
    <!-- ko with: taskManager -->
    <div class="modal-overlay" data-bind="visible: showDeleteModal, click: closeDeleteModalOnOverlay">
        <div class="modal-container" data-bind="click: stopPropagation">
            <div class="modal-header">
                <h3>削除確認</h3>
                <button class="modal-close" data-bind="click: closeDeleteModal">×</button>
            </div>
            <div class="modal-body">
                <p>「<span data-bind="text: taskToDelete() ? taskToDelete().title() : ''"></span>」を削除しますか？</p>
                <p>この操作は取り消せません。</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" data-bind="click: closeDeleteModal, disable: isProcessing">キャンセル</button>
                <button class="btn btn-delete" data-bind="click: confirmDelete, disable: isProcessing">削除</button>
            </div>
        </div>
    </div>
    <!-- /ko -->

    <!-- サイドメニューを含める -->
    <?php include(APPPATH.'views/common/menu.php'); ?>

    <script>
        // メニュー関連の関数を追加
        function openMenu() {
            document.getElementById('sideMenu').classList.add('active');
            document.getElementById('menuOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            document.getElementById('sideMenu').classList.remove('active');
            document.getElementById('menuOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Knockout.js ViewModel
        function TaskViewModel(data) {
            var self = this;
            
            // プロパティ
            self.id = ko.observable(data.id || null);
            self.title = ko.observable(data.title || '');
            self.description = ko.observable(data.description || '');
            self.dueDate = ko.observable(data.due_date || '');
            self.dueTime = ko.observable(data.due_time || '');
            self.status = ko.observable(data.status === 1 || data.status === '1' || data.status === true);
            self.isUpdating = ko.observable(false);
            
            // 計算プロパティ
            self.formattedDateTime = ko.computed(function() {
                var date = self.dueDate();
                var time = self.dueTime();
                if (!date) return '';
                
                var dateObj = new Date(date);
                var month = dateObj.getMonth() + 1;
                var day = dateObj.getDate();
                var result = month + '/' + day;
                
                if (time) {
                    result += ' ' + time;
                }
                return result;
            });
        }

        // メインViewModel
        function TaskManagerViewModel() {
            var self = this;
            
            // 状態管理
            self.tasks = ko.observableArray([]);
            self.isLoading = ko.observable(false);
            self.isProcessing = ko.observable(false);
            self.errorMessage = ko.observable('');
            self.successMessage = ko.observable('');
            
            // モーダル関連
            self.showModal = ko.observable(false);
            self.showDeleteModal = ko.observable(false);
            self.currentTask = ko.observable(new TaskViewModel({}));
            self.taskToDelete = ko.observable(null);
            self.isEditMode = ko.observable(false);
            
            // フィルター
            self.statusFilter = ko.observable('all');
            
            // 計算プロパティ
            self.completedCount = ko.computed(function() {
                return self.tasks().filter(function(task) {
                    return task.status();
                }).length;
            });
            
            self.filteredTasks = ko.computed(function() {
                var filter = self.statusFilter();
                return self.tasks().filter(function(task) {
                    switch(filter) {
                        case 'pending': return !task.status();
                        case 'completed': return task.status();
                        default: return true;
                    }
                });
            });
            
            // メソッド
            self.loadTasks = function() {
                self.isLoading(true);
                self.clearMessages();
                
                fetch('<?= Uri::create('api/tasks') ?>')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            var taskModels = data.tasks.map(task => new TaskViewModel(task));
                            self.tasks(taskModels);
                        } else {
                            self.showError(data.message || 'タスクの読み込みに失敗しました');
                        }
                    })
                    .catch(error => {
                        self.showError('通信エラーが発生しました');
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        self.isLoading(false);
                    });
            };
            
            self.toggleTaskStatus = function(task) {
                if (task.isUpdating()) return;
                
                task.isUpdating(true);
                var newStatus = task.status();
                
                fetch('<?= Uri::create('api/tasks') ?>/' + task.id() + '/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus ? 1 : 0 })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        self.showSuccess('タスクを更新しました');
                    } else {
                        task.status(!newStatus); // 元に戻す
                        self.showError(data.message || '更新に失敗しました');
                    }
                })
                .catch(error => {
                    task.status(!newStatus); // 元に戻す
                    self.showError('通信エラーが発生しました');
                    console.error('Error:', error);
                })
                .finally(() => {
                    task.isUpdating(false);
                });
            };
            
            self.showAddModal = function() {
                self.currentTask(new TaskViewModel({
                    dueDate: new Date().toISOString().split('T')[0]
                }));
                self.isEditMode(false);
                self.showModal(true);
            };
            
            self.editTask = function(task) {
                self.currentTask(new TaskViewModel({
                    id: task.id(),
                    title: task.title(),
                    description: task.description(),
                    due_date: task.dueDate(),
                    due_time: task.dueTime(),
                    status: task.status()
                }));
                self.isEditMode(true);
                self.showModal(true);
            };
            
            self.closeModal = function() {
                self.showModal(false);
                self.clearMessages();
            };
            
            self.saveTask = function() {
                var task = self.currentTask();
                
                if (!task.title().trim()) {
                    self.showError('タイトルを入力してください');
                    return;
                }
                
                self.isProcessing(true);
                self.clearMessages();
                
                var url = self.isEditMode() 
                    ? '<?= Uri::create('api/tasks') ?>/' + task.id()
                    : '<?= Uri::create('api/tasks') ?>';
                
                var method = self.isEditMode() ? 'PUT' : 'POST';
                
                fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        title: task.title(),
                        description: task.description(),
                        due_date: task.dueDate(),
                        due_time: task.dueTime(),
                        status: task.status() ? 1 : 0
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        self.showSuccess(self.isEditMode() ? '更新しました' : '追加しました');
                        self.loadTasks();
                        self.closeModal();
                    } else {
                        self.showError(data.message || '保存に失敗しました');
                    }
                })
                .catch(error => {
                    self.showError('通信エラーが発生しました');
                    console.error('Error:', error);
                })
                .finally(() => {
                    self.isProcessing(false);
                });
            };
            
            self.deleteTask = function(task) {
                self.taskToDelete(task);
                self.showDeleteModal(true);
            };
            
            self.closeDeleteModal = function() {
                self.showDeleteModal(false);
                self.taskToDelete(null);
                self.clearMessages();
            };
            
            // モーダルオーバーレイクリック用のヘルパー関数
            self.closeDeleteModalOnOverlay = function(data, event) {
                if (event.target === event.currentTarget) {
                    self.closeDeleteModal();
                }
                return true;
            };
            
            // クリック伝播停止用のヘルパー関数
            self.stopPropagation = function(data, event) {
                return true;
            };
            
            self.confirmDelete = function() {
                var task = self.taskToDelete();
                if (!task) return;
                
                self.isProcessing(true);
                
                fetch('<?= Uri::create('api/tasks') ?>/' + task.id(), {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        self.showSuccess('削除しました');
                        self.loadTasks();
                        self.closeDeleteModal();
                    } else {
                        self.showError(data.message || '削除に失敗しました');
                    }
                })
                .catch(error => {
                    self.showError('通信エラーが発生しました');
                    console.error('Error:', error);
                })
                .finally(() => {
                    self.isProcessing(false);
                });
            };
            
            // ユーティリティメソッド
            self.showError = function(message) {
                self.errorMessage(message);
                setTimeout(() => self.errorMessage(''), 5000);
            };
            
            self.showSuccess = function(message) {
                self.successMessage(message);
                setTimeout(() => self.successMessage(''), 3000);
            };
            
            self.clearMessages = function() {
                self.errorMessage('');
                self.successMessage('');
            };
            
            // 初期化
            self.loadTasks();
        }

        // アプリケーション初期化
        var appViewModel = {
            taskManager: new TaskManagerViewModel()
        };

        // Knockout.js バインディング
        document.addEventListener('DOMContentLoaded', function() {
            ko.applyBindings(appViewModel);
            
            // メニューオーバーレイクリックで閉じる
            var menuOverlay = document.getElementById('menuOverlay');
            if (menuOverlay) {
                menuOverlay.addEventListener('click', closeMenu);
            }
            
            // ESCキーでモーダルを閉じる
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    // モーダルが開いている場合はモーダルを閉じる
                    if (appViewModel.taskManager.showModal()) {
                        appViewModel.taskManager.closeModal();
                    } else if (appViewModel.taskManager.showDeleteModal()) {
                        appViewModel.taskManager.closeDeleteModal();
                    } else {
                        // それ以外の場合はメニューを閉じる
                        closeMenu();
                    }
                }
            });
        });
    </script>
</body>
</html>