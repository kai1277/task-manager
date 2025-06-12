<h1>タスク一覧</h1>

<nav style="margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;">
  <a href="<?= Uri::create('task') ?>">タスク管理</a> | 
  <a href="<?= Uri::create('schedule') ?>">予定管理</a> | 
  <a href="<?= Uri::create('class') ?>">履修科目管理</a>
</nav>

<p><a href="<?= Uri::create('task/create') ?>">＋ 新しいタスクを作成</a></p>

<table border="1">
  <tr>
    <th>タイトル</th>
    <th>説明</th>
    <th>期限日</th>
    <th>期限時刻</th>
    <th>ステータス</th>
    <th>作成日</th>
  </tr>

  <?php foreach ($tasks as $task): ?>
    <tr>
      <td><?= $task->title ?></td>
      <td><?= $task->description ?></td>
      <td><?= $task->due_date ?></td>
      <td><?= $task->due_time ?></td>
      <td>
        <a href="<?= Uri::create('task/toggle_status/' . $task->id) ?>">
            <?= $task->status == 0 ? '未完了' : '完了' ?>
        </a>
      </td>
      <td><?= $task->created_at ?></td>
      <td>
        <a href="<?= Uri::create('task/edit/' . $task->id) ?>">編集</a>
      </td>
      <td>
        <a href="<?= Uri::create('task/delete/' . $task->id) ?>" onclick="return confirm('本当に削除しますか？');">削除</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
