<form action="/task-manager/public/user/create" method="post">
  <label>ユーザー名: <input type="text" name="username"></label><br>
  <label>メールアドレス: <input type="email" name="email"></label><br>
  <label>パスワード: <input type="password" name="password"></label><br>
  <input type="submit" value="登録">
</form>

<?php if (!empty($errors)): ?>
  <ul>
    <?php foreach ($errors as $field => $error): ?>
      <li><?= $error->get_message() ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
