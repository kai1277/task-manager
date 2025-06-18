<!-- fuel/app/views/user/mypage.php -->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ - タスク管理</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
</head>
<body>
    <div class="mypage-container">
        <!-- ヘッダー -->
        <div class="mypage-header">
            <button class="hamburger-menu" onclick="openMenu()">☰</button>
            <h1 class="mypage-title">マイページ</h1>
        </div>

        <!-- フラッシュメッセージ -->
        <?php if (Session::get_flash('success')): ?>
            <div class="flash-message success-message" onclick="this.style.display='none'">
                <?= Session::get_flash('success') ?>
            </div>
        <?php endif; ?>
        
        <?php if (Session::get_flash('error')): ?>
            <div class="flash-message error-message" onclick="this.style.display='none'">
                <?= Session::get_flash('error') ?>
            </div>
        <?php endif; ?>

        <div class="mypage-content">
            <!-- データ管理セクション -->
            <div class="mypage-section">
                <div class="mypage-grid">
                    <a href="<?= Uri::create('task') ?>" class="mypage-card task-card">
                        <span class="card-text">タスク</span>
                    </a>
                    <a href="<?= Uri::create('schedule') ?>" class="mypage-card schedule-card">
                        <span class="card-text">予定</span>
                    </a>
                    <a href="<?= Uri::create('class') ?>" class="mypage-card class-card">
                        <span class="card-text">授業</span>
                    </a>
                </div>
            </div>

            <!-- 表示切り替えセクション -->
            <div class="mypage-section">
                <div class="mypage-grid">
                    <a href="<?= Uri::create('task') ?>" class="mypage-card view-card day-card">
                        <span class="card-text">日</span>
                    </a>
                    <a href="#" class="mypage-card view-card week-card" onclick="alert('週表示は開発中です'); return false;">
                        <span class="card-text">週</span>
                    </a>
                    <a href="#" class="mypage-card view-card month-card" onclick="alert('月表示は開発中です'); return false;">
                        <span class="card-text">月</span>
                    </a>
                </div>
            </div>

            <!-- ユーザー情報セクション -->
            <div class="mypage-section">
                <div class="user-info">
                    <div class="user-info-item">
                        <span class="info-label">メールアドレス：</span>
                        <span class="info-value" id="userEmail"><?= Session::get('user_email', 'ユーザー') ?></span>
                    </div>
                    <div class="user-info-item">
                        <span class="info-label">ユーザー名：</span>
                        <span class="info-value" id="userName"><?= Session::get('name', 'ユーザー') ?></span>
                    </div>
                </div>
            </div>

            <!-- アカウント設定セクション -->
            <div class="mypage-section">
                <button class="settings-btn" onclick="openProfileEditModal()">
                    <span>⚙️ プロフィール編集</span>
                </button>
                <button class="settings-btn" onclick="openPasswordChangeModal()">
                    <span>🔐 パスワード変更</span>
                </button>
            </div>

            <!-- ログアウトボタン -->
            <div class="mypage-section">
                <button class="logout-btn" onclick="confirmLogout()">
                    ログアウト
                </button>
            </div>
        </div>
    </div>

    <!-- プロフィール編集モーダル -->
    <div class="modal-overlay" id="profileEditModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">プロフィール編集</h2>
                <button class="modal-close" onclick="closeProfileEditModal()">×</button>
            </div>
            
            <div class="modal-body">
                <form id="profileEditForm" method="post" action="<?= Uri::create('user/update_profile') ?>">
                    <div class="form-group">
                        <label for="editUserName" class="form-label">ユーザー名</label>
                        <input type="text" 
                               id="editUserName" 
                               name="username" 
                               class="form-input" 
                               value="<?= Session::get('name', '') ?>"
                               required 
                               maxlength="50">
                    </div>
                    
                    <div class="form-group">
                        <label for="editEmail" class="form-label">メールアドレス</label>
                        <input type="email" 
                               id="editEmail" 
                               name="email" 
                               class="form-input" 
                               value="<?= Session::get('user_email', '') ?>"
                               required>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeProfileEditModal()">キャンセル</button>
                <button class="btn btn-save" onclick="saveProfile()">保存</button>
            </div>
        </div>
    </div>

    <!-- パスワード変更モーダル -->
    <div class="modal-overlay" id="passwordChangeModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">パスワード変更</h2>
                <button class="modal-close" onclick="closePasswordChangeModal()">×</button>
            </div>
            
            <div class="modal-body">
                <form id="passwordChangeForm" method="post" action="<?= Uri::create('user/change_password') ?>">
                    <div class="form-group">
                        <label for="currentPassword" class="form-label">現在のパスワード</label>
                        <input type="password" 
                               id="currentPassword" 
                               name="current_password" 
                               class="form-input" 
                               required 
                               minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="newPassword" class="form-label">新しいパスワード</label>
                        <input type="password" 
                               id="newPassword" 
                               name="new_password" 
                               class="form-input" 
                               required 
                               minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword" class="form-label">新しいパスワード（確認）</label>
                        <input type="password" 
                               id="confirmPassword" 
                               name="confirm_password" 
                               class="form-input" 
                               required 
                               minlength="6">
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closePasswordChangeModal()">キャンセル</button>
                <button class="btn btn-save" onclick="changePassword()">変更</button>
            </div>
        </div>
    </div>

    <?php include(APPPATH.'views/common/menu.php'); ?>

    <script>
        // プロフィール編集モーダル
        function openProfileEditModal() {
            document.getElementById('profileEditModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeProfileEditModal() {
            document.getElementById('profileEditModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function saveProfile() {
            const form = document.getElementById('profileEditForm');
            const userName = document.getElementById('editUserName').value;
            const email = document.getElementById('editEmail').value;
            
            if (!userName.trim() || !email.trim()) {
                alert('すべての項目を入力してください');
                return;
            }
            
            if (!email.includes('@')) {
                alert('正しいメールアドレスを入力してください');
                return;
            }
            
            form.submit();
        }

        // パスワード変更モーダル
        function openPasswordChangeModal() {
            document.getElementById('passwordChangeModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePasswordChangeModal() {
            document.getElementById('passwordChangeModal').classList.remove('active');
            document.body.style.overflow = '';
            
            // フォームをリセット
            document.getElementById('passwordChangeForm').reset();
        }

        function changePassword() {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                alert('すべての項目を入力してください');
                return;
            }
            
            if (newPassword.length < 6) {
                alert('新しいパスワードは6文字以上で入力してください');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                alert('新しいパスワードが一致しません');
                return;
            }
            
            if (currentPassword === newPassword) {
                alert('新しいパスワードは現在のパスワードと異なるものを設定してください');
                return;
            }
            
            document.getElementById('passwordChangeForm').submit();
        }

        // ログアウト確認
        function confirmLogout() {
            if (confirm('ログアウトしますか？')) {
                location.href = '<?= Uri::create('user/logout') ?>';
            }
        }

        // イベントリスナー
        document.addEventListener('DOMContentLoaded', function() {
            // モーダル外側クリックで閉じる
            document.getElementById('profileEditModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeProfileEditModal();
                }
            });
            
            document.getElementById('passwordChangeModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closePasswordChangeModal();
                }
            });
            
            // ESCキーで閉じる
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeProfileEditModal();
                    closePasswordChangeModal();
                }
            });
            
            // パスワード確認のリアルタイムチェック
            document.getElementById('confirmPassword').addEventListener('input', function() {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = this.value;
                
                if (confirmPassword && newPassword !== confirmPassword) {
                    this.setCustomValidity('パスワードが一致しません');
                } else {
                    this.setCustomValidity('');
                }
            });
        });
    </script>
</body>
</html>