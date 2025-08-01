<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - タスク管理</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
    <!-- セキュリティヘッダー -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1 class="login-title">ログイン</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?= Security::htmlentities($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?= Uri::create('user/login') ?>">
                <!-- CSRFトークン -->
                <input type="hidden" name="fuel_csrf_token" value="<?= Security::htmlentities($csrf_token) ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">メールアドレス</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           value="<?= isset($old_email) ? Security::htmlentities($old_email) : '' ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">パスワード</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           required>
                </div>
                
                <button type="submit" class="btn-primary">ログイン</button>
            </form>
            
            <div class="form-divider"></div>
            
            <div class="form-links">
                <a href="#">パスワードをお忘れの場合</a>
                <br><br>
                <a href="<?= Uri::create('user/register') ?>" class="register-link">新規登録</a>
            </div>
        </div>
    </div>
</body>
</html>