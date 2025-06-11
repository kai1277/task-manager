<h1>新規ユーザー登録</h1>

<?php if (!empty($errors)): ?>
  <ul style="color: red;">
    <?php foreach ($errors as $field => $error): ?>
      <li><?= $error->get_message() ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<form action="<?= Uri::create('user/create') ?>" method="post">
  <p>
    <label>ユーザー名: <input type="text" name="username" required></label>
  </p>
  <p>
    <label>メールアドレス: <input type="email" name="email" required></label>
  </p>
  <p>
    <label>パスワード: <input type="password" name="password" required></label>
  </p>
  <p>
    <input type="submit" value="登録">
  </p>
</form>

<p><a href="<?= Uri::create('user/login') ?>">既にアカウントをお持ちの方はこちら</a></p>