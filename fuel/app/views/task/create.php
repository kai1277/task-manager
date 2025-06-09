<h2>新しいタスクの作成</h2>

<?php if (!empty($errors)): ?>
  <ul style="color:red;">
    <?php foreach ($errors as $field_errors): ?>
      <?php foreach ((array) $field_errors as $error): ?>
        <li><?= $error ?></li>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>


<form method="post" action="/task-manager/public/task/create">
  <p>
    <label>タイトル: <input type="text" name="title"></label>
  </p>
  <p>
    <label>説明: <textarea name="description"></textarea></label>
  </p>
  <p>
    <label>締切日: <input type="date" name="due_date"></label>
  </p>
  <p>
    <label>締切時間: <input type="time" name="due_time"></label>
  </p>
  <p>
    <input type="submit" value="登録">
  </p>
</form>
