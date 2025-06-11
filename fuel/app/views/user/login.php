<h1>ログイン</h1>

<?php if (!empty($error)): ?>
  <p style="color: red;"><?= $error ?></p>
<?php endif; ?>

<form method="post" action="<?= Uri::create('user/login') ?>">
  <p>
    <label>メールアドレス: <input type="email" name="email" required></label>
  </p>
  <p>
    <label>パスワード: <input type="password" name="password" required></label>
  </p>
  <p>
    <input type="submit" value="ログイン">
  </p>
</form>

<p><a href="<?= Uri::create('user/register') ?>">新規登録はこちら</a></p>