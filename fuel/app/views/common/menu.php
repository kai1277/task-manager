<!-- サイドメニュー -->
<div class="menu-overlay" id="menuOverlay"></div>
<div class="side-menu" id="sideMenu">
    <!-- メニューヘッダー -->
    <div class="menu-header">
        <button class="menu-close" onclick="closeMenu()">×</button>
        <div class="menu-user-info">
            <div class="menu-user-name">
                <?= Session::get('name', 'ユーザー') ?>
            </div>
            <div class="menu-user-email">
                大学生タスク管理
            </div>
        </div>
    </div>
    
    <!-- メニューボディ -->
    <div class="menu-body">
        <!-- 表示切り替え -->
        <div class="menu-section">
            <div class="menu-section-title">表示</div>
            <a href="<?= Uri::create('task/day') ?>" class="menu-item">
                <span class="menu-item-icon">📅</span>
                <span class="menu-item-text">日表示</span>
            </a>
            <a href="<?= Uri::create('task/week') ?>" class="menu-item">
                <span class="menu-item-icon">📊</span>
                <span class="menu-item-text">週表示</span>
            </a>
            <a href="<?= Uri::create('task/month') ?>" class="menu-item">
                <span class="menu-item-icon">🗓️</span>
                <span class="menu-item-text">月表示</span>
            </a>
        </div>
        
        <!-- データ管理 -->
        <div class="menu-section">
            <div class="menu-section-title">データ管理</div>
            <a href="<?= Uri::create('task') ?>" class="menu-item">
                <span class="menu-item-icon">✅</span>
                <span class="menu-item-text">タスク管理</span>
            </a>
            <a href="<?= Uri::create('schedule') ?>" class="menu-item">
                <span class="menu-item-icon">📍</span>
                <span class="menu-item-text">予定管理</span>
            </a>
            <a href="<?= Uri::create('class') ?>" class="menu-item">
                <span class="menu-item-icon">🎓</span>
                <span class="menu-item-text">履修科目管理</span>
            </a>
        </div>
        
        <!-- 設定 -->
        <div class="menu-section">
            <div class="menu-section-title">設定</div>
            <a href="<?= Uri::create('user/mypage') ?>" class="menu-item">
                <span class="menu-item-icon">👤</span>
                <span class="menu-item-text">マイページ</span>
                <span class="menu-item-arrow">›</span>
            </a>
            <button class="menu-item" onclick="alert('通知設定は開発中です')">
                <span class="menu-item-icon">🔔</span>
                <span class="menu-item-text">通知設定</span>
                <span class="menu-item-arrow">›</span>
            </button>
            <button class="menu-item" onclick="alert('アプリ設定は開発中です')">
                <span class="menu-item-icon">⚙️</span>
                <span class="menu-item-text">アプリ設定</span>
                <span class="menu-item-arrow">›</span>
            </button>
        </div>
        
        <!-- ヘルプ・その他 -->
        <div class="menu-section">
            <div class="menu-section-title">ヘルプ</div>
            <button class="menu-item" onclick="alert('使い方ガイドは開発中です')">
                <span class="menu-item-icon">❓</span>
                <span class="menu-item-text">使い方ガイド</span>
                <span class="menu-item-arrow">›</span>
            </button>
            <button class="menu-item" onclick="alert('お問い合わせは開発中です')">
                <span class="menu-item-icon">📧</span>
                <span class="menu-item-text">お問い合わせ</span>
                <span class="menu-item-arrow">›</span>
            </button>
        </div>
        
        <!-- ログアウト -->
        <div class="menu-section">
            <a href="<?= Uri::create('user/logout') ?>" class="menu-item logout" onclick="return confirm('ログアウトしますか？')">
                <span class="menu-item-icon">🚪</span>
                <span class="menu-item-text">ログアウト</span>
            </a>
        </div>
    </div>
    
    <!-- メニューフッター -->
    <div class="menu-footer">
        タスク管理アプリ v1.0<br>
        © 2025 Student Task Manager
    </div>
</div>

<script>
// メニュー関連のJavaScript
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

// オーバーレイクリックで閉じる
document.getElementById('menuOverlay').addEventListener('click', closeMenu);

// ESCキーで閉じる
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMenu();
    }
});
</script>