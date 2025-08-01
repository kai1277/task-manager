<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>タスク作成 - タスク管理</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
    <!-- セキュリティヘッダー -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
</head>
<body>
    <div class="container">
        <h1>新しいタスクの作成</h1>

        <!-- エラーメッセージ表示（XSS対策済み） -->
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul style="color:red;">
                    <?php foreach ($errors as $field_errors): ?>
                        <?php foreach ((array) $field_errors as $error): ?>
                            <li><?= Security::htmlentities(is_object($error) ? $error->get_message() : $error) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- フォーム（CSRF対策済み） -->
        <form method="post" action="<?= Uri::create('task/create') ?>" id="taskCreateForm">
            <!-- CSRFトークン -->
            <input type="hidden" name="fuel_csrf_token" value="<?= Security::htmlentities($csrf_token) ?>">
            
            <div class="form-group">
                <label for="title">タイトル <span class="required">*</span></label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="form-control" 
                       value="<?= isset($old_input['title']) ? Security::htmlentities($old_input['title']) : '' ?>" 
                       required 
                       maxlength="255"
                       placeholder="例: レポート提出">
            </div>
            
            <div class="form-group">
                <label for="description">説明・メモ</label>
                <textarea id="description" 
                          name="description" 
                          class="form-control" 
                          rows="4" 
                          maxlength="1000"
                          placeholder="タスクの詳細や備考を入力してください"><?= isset($old_input['description']) ? Security::htmlentities($old_input['description']) : '' ?></textarea>
                <small class="form-text">1000文字以内</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="due_date">締切日 <span class="required">*</span></label>
                    <input type="date" 
                           id="due_date" 
                           name="due_date" 
                           class="form-control" 
                           value="<?= isset($old_input['due_date']) ? Security::htmlentities($old_input['due_date']) : date('Y-m-d') ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="due_time">締切時間</label>
                    <input type="time" 
                           id="due_time" 
                           name="due_time" 
                           class="form-control" 
                           value="<?= isset($old_input['due_time']) ? Security::htmlentities($old_input['due_time']) : '' ?>">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span class="btn-text">作成</span>
                    <span class="btn-loading" style="display: none;">作成中...</span>
                </button>
                <a href="<?= Uri::create('task') ?>" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>

    <script>
        // セキュリティ設定
        window.CSRF_TOKEN = <?= json_encode($csrf_token) ?>;
        
        // 入力値サニタイズ関数
        function sanitizeInput(str) {
            if (typeof str !== 'string') return str;
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
        
        // フォームバリデーション
        function validateForm() {
            const title = document.getElementById('title').value.trim();
            const dueDate = document.getElementById('due_date').value;
            const dueTime = document.getElementById('due_time').value;
            const description = document.getElementById('description').value.trim();
            
            // タイトルの検証
            if (!title) {
                alert('タイトルを入力してください');
                document.getElementById('title').focus();
                return false;
            }
            
            if (title.length > 255) {
                alert('タイトルは255文字以下で入力してください');
                document.getElementById('title').focus();
                return false;
            }
            
            // 締切日の検証
            if (!dueDate) {
                alert('締切日を入力してください');
                document.getElementById('due_date').focus();
                return false;
            }
            
            // 日付形式の検証
            if (!/^\d{4}-\d{2}-\d{2}$/.test(dueDate)) {
                alert('正しい日付形式で入力してください');
                document.getElementById('due_date').focus();
                return false;
            }
            
            // 日付の妥当性チェック
            const dateObj = new Date(dueDate);
            if (isNaN(dateObj.getTime()) || dateObj.toISOString().split('T')[0] !== dueDate) {
                alert('有効な日付を入力してください');
                document.getElementById('due_date').focus();
                return false;
            }
            
            // 時刻の検証（オプション）
            if (dueTime && !/^\d{2}:\d{2}$/.test(dueTime)) {
                alert('正しい時刻形式（HH:MM）で入力してください');
                document.getElementById('due_time').focus();
                return false;
            }
            
            // 説明の文字数チェック
            if (description.length > 1000) {
                alert('説明は1000文字以下で入力してください');
                document.getElementById('description').focus();
                return false;
            }
            
            // 過去の日付チェック（警告のみ）
            const today = new Date().toISOString().split('T')[0];
            if (dueDate < today) {
                if (!confirm('過去の日付が設定されています。このまま作成しますか？')) {
                    document.getElementById('due_date').focus();
                    return false;
                }
            }
            
            return true;
        }
        
        // リアルタイムバリデーション
        function setupRealTimeValidation() {
            const titleInput = document.getElementById('title');
            const descriptionInput = document.getElementById('description');
            const dueDateInput = document.getElementById('due_date');
            const dueTimeInput = document.getElementById('due_time');
            
            // タイトルの文字数制限
            titleInput.addEventListener('input', function() {
                const length = this.value.length;
                if (length > 255) {
                    this.style.borderColor = '#e74c3c';
                    this.title = '255文字を超えています';
                } else {
                    this.style.borderColor = '';
                    this.title = '';
                }
            });
            
            // 説明の文字数カウンター
            descriptionInput.addEventListener('input', function() {
                const length = this.value.length;
                const counter = document.getElementById('descriptionCounter');
                
                if (!counter) {
                    const counterElement = document.createElement('small');
                    counterElement.id = 'descriptionCounter';
                    counterElement.className = 'form-text';
                    this.parentNode.appendChild(counterElement);
                }
                
                document.getElementById('descriptionCounter').textContent = `${length}/1000文字`;
                
                if (length > 1000) {
                    this.style.borderColor = '#e74c3c';
                    document.getElementById('descriptionCounter').style.color = '#e74c3c';
                } else {
                    this.style.borderColor = '';
                    document.getElementById('descriptionCounter').style.color = '';
                }
            });
            
            // 日付の妥当性チェック
            dueDateInput.addEventListener('change', function() {
                const dateValue = this.value;
                if (dateValue) {
                    const dateObj = new Date(dateValue);
                    if (isNaN(dateObj.getTime()) || dateObj.toISOString().split('T')[0] !== dateValue) {
                        this.style.borderColor = '#e74c3c';
                        this.title = '有効な日付を入力してください';
                    } else {
                        this.style.borderColor = '';
                        this.title = '';
                        
                        // 過去の日付の警告
                        const today = new Date().toISOString().split('T')[0];
                        if (dateValue < today) {
                            this.style.borderColor = '#f39c12';
                            this.title = '過去の日付が設定されています';
                        }
                    }
                }
            });
            
            // 時刻の妥当性チェック
            dueTimeInput.addEventListener('change', function() {
                const timeValue = this.value;
                if (timeValue && !/^\d{2}:\d{2}$/.test(timeValue)) {
                    this.style.borderColor = '#e74c3c';
                    this.title = '正しい時刻形式で入力してください';
                } else {
                    this.style.borderColor = '';
                    this.title = '';
                }
            });
        }
        
        // フォーム送信処理
        function handleFormSubmit() {
            const form = document.getElementById('taskCreateForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            form.addEventListener('submit', function(e) {
                // バリデーション実行
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
                
                // 重複送信防止
                if (submitBtn.disabled) {
                    e.preventDefault();
                    return false;
                }
                
                // ボタンを無効化
                submitBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                
                // 入力値をサニタイズ（フォーム送信前の最終チェック）
                const inputs = form.querySelectorAll('input[type="text"], textarea');
                inputs.forEach(input => {
                    if (input.type === 'text' || input.tagName === 'TEXTAREA') {
                        input.value = input.value.trim();
                    }
                });
                
                // 3秒後にボタンを再有効化（エラー時の対策）
                setTimeout(() => {
                    submitBtn.disabled = false;
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }, 3000);
            });
        }
        
        // キーボードショートカット
        function setupKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Ctrl+Enter で送信
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('taskCreateForm').dispatchEvent(new Event('submit'));
                }
                
                // Escapeでキャンセル
                if (e.key === 'Escape') {
                    if (confirm('作成をキャンセルしますか？入力した内容は失われます。')) {
                        window.location.href = <?= json_encode(Uri::create('task')) ?>;
                    }
                }
            });
        }
        
        // オートセーブ機能（ローカルストレージ）
        function setupAutoSave() {
            const form = document.getElementById('taskCreateForm');
            const inputs = form.querySelectorAll('input, textarea');
            const storageKey = 'task_create_autosave';
            
            // 保存されたデータを復元
            function restoreData() {
                try {
                    const savedData = localStorage.getItem(storageKey);
                    if (savedData) {
                        const data = JSON.parse(savedData);
                        Object.keys(data).forEach(key => {
                            const element = document.getElementById(key);
                            if (element && !element.value) {
                                element.value = data[key];
                            }
                        });
                    }
                } catch (e) {
                    console.error('Auto-restore error:', e);
                }
            }
            
            // データを保存
            function saveData() {
                try {
                    const data = {};
                    inputs.forEach(input => {
                        if (input.id && input.value) {
                            data[input.id] = input.value;
                        }
                    });
                    localStorage.setItem(storageKey, JSON.stringify(data));
                } catch (e) {
                    console.error('Auto-save error:', e);
                }
            }
            
            // データをクリア
            function clearData() {
                try {
                    localStorage.removeItem(storageKey);
                } catch (e) {
                    console.error('Auto-clear error:', e);
                }
            }
            
            // 入力時に自動保存
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    clearTimeout(this.saveTimeout);
                    this.saveTimeout = setTimeout(saveData, 1000);
                });
            });
            
            // フォーム送信時にデータをクリア
            form.addEventListener('submit', clearData);
            
            // ページ読み込み時にデータを復元
            restoreData();
            
            // ページ離脱時の警告
            let formChanged = false;
            inputs.forEach(input => {
                input.addEventListener('input', () => formChanged = true);
            });
            
            window.addEventListener('beforeunload', function(e) {
                if (formChanged && !form.submitted) {
                    e.preventDefault();
                    e.returnValue = '入力した内容が失われます。本当にページを離れますか？';
                }
            });
            
            form.addEventListener('submit', () => form.submitted = true);
        }
        
        // 初期化
        document.addEventListener('DOMContentLoaded', function() {
            try {
                setupRealTimeValidation();
                handleFormSubmit();
                setupKeyboardShortcuts();
                setupAutoSave();
                
                // フォーカスをタイトルに設定
                document.getElementById('title').focus();
                
                // 説明の初期文字数カウンターを設定
                const descriptionInput = document.getElementById('description');
                if (descriptionInput) {
                    descriptionInput.dispatchEvent(new Event('input'));
                }
                
            } catch (e) {
                console.error('Initialization error:', e);
            }
        });
    </script>

    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        .form-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .required {
            color: #e74c3c;
        }
        
        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background-color: #0056b3;
        }
        
        .btn-primary:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
            color: white;
            text-decoration: none;
        }
        
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .error-message ul {
            margin: 0;
            padding-left: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .container {
                padding: 10px;
            }
        }
    </style>
</body>
</html>