<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 - タスク管理</title>
    <link rel="stylesheet" href="<?= Uri::create('assets/css/style.css') ?>">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1 class="login-title">新規登録</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($errors as $field => $error): ?>
                            <li><?= Security::htmlentities(is_object($error) ? $error->get_message() : $error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?= Uri::create('user/create') ?>">
                <input type="hidden" name="fuel_csrf_token" value="<?= Security::htmlentities($csrf_token) ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">ユーザー名</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-input" 
                           value="<?= isset($old_input['username']) ? Security::htmlentities($old_input['username']) : (Input::post('username') ? Security::htmlentities(Input::post('username')) : '') ?>"
                           required 
                           maxlength="50"
                           placeholder="山田太郎">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">メールアドレス</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           value="<?= isset($old_input['email']) ? Security::htmlentities($old_input['email']) : (Input::post('email') ? Security::htmlentities(Input::post('email')) : '') ?>"
                           required
                           placeholder="example@student.ac.jp">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">パスワード</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           required 
                           minlength="6"
                           placeholder="6文字以上">
                </div>
                
                <div class="form-group">
                    <label for="password_confirm" class="form-label">パスワード（確認）</label>
                    <input type="password" 
                           id="password_confirm" 
                           name="password_confirm" 
                           class="form-input" 
                           required 
                           minlength="6"
                           placeholder="もう一度入力してください">
                </div>
                
                <button type="submit" class="btn-primary" id="submit-btn">登録</button>
            </form>
            
            <div class="form-divider"></div>
            
            <div class="form-links">
                <span>すでにアカウントをお持ちの場合：</span>
                <br><br>
                <a href="<?= Uri::create('user/login') ?>" class="register-link">ログイン</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirm');
            const submitBtn = document.getElementById('submit-btn');
            const form = document.querySelector('form');
            
            function checkPasswordMatch() {
                if (passwordConfirm.value && password.value !== passwordConfirm.value) {
                    passwordConfirm.setCustomValidity('パスワードが一致しません');
                    passwordConfirm.style.borderColor = '#e74c3c';
                    submitBtn.disabled = true;
                } else {
                    passwordConfirm.setCustomValidity('');
                    passwordConfirm.style.borderColor = '';
                    submitBtn.disabled = false;
                }
            }
            
            function checkPasswordStrength() {
                const value = password.value;
                const strengthBar = document.querySelector('.password-strength-bar');
                
                if (!strengthBar) return;
                
                let strength = 0;
                if (value.length >= 6) strength++;
                if (value.match(/[a-z]/)) strength++;
                if (value.match(/[A-Z]/)) strength++;
                if (value.match(/[0-9]/)) strength++;
                if (value.match(/[^a-zA-Z0-9]/)) strength++;
                
                strengthBar.className = 'password-strength-bar';
                if (strength <= 2) {
                    strengthBar.classList.add('weak');
                } else if (strength <= 3) {
                    strengthBar.classList.add('medium');
                } else {
                    strengthBar.classList.add('strong');
                }
            }
            
            password.addEventListener('input', function() {
                checkPasswordMatch();
                checkPasswordStrength();
            });
            
            passwordConfirm.addEventListener('input', checkPasswordMatch);
            
            form.addEventListener('submit', function(e) {
                if (password.value !== passwordConfirm.value) {
                    e.preventDefault();
                    alert('パスワードが一致しません');
                    passwordConfirm.focus();
                    return false;
                }
                
                if (password.value.length < 6) {
                    e.preventDefault();
                    alert('パスワードは6文字以上で入力してください');
                    password.focus();
                    return false;
                }
                
                submitBtn.disabled = true;
                submitBtn.textContent = '登録中...';
            });
        });
    </script>
</body>
</html>