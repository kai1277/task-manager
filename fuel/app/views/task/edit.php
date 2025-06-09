<h1>タスク編集</h1>

<form method="post">
  <label>タイトル: <input type="text" name="title" value="<?= $task->title ?>"></label><br>
  <label>説明: <textarea name="description"><?= $task->description ?></textarea></label><br>
  <label>期限日: <input type="date" name="due_date" value="<?= $task->due_date ?>"></label><br>
  <label>期限時刻: <input type="time" name="due_time" value="<?= $task->due_time ?>"></label><br>
  <input type="submit" value="更新">
</form>
