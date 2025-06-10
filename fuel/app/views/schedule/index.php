<h1>予定一覧</h1>

<p><a href="<?= Uri::create('schedule/create') ?>">＋ 新しい予定を作成</a></p>

<table border="1">
  <tr>
    <th>予定名</th>
    <th>場所</th>
    <th>開始日時</th>
    <th>終了日時</th>
    <th>終日</th>
    <th>説明</th>
    <th>操作</th>
  </tr>

  <?php foreach ($schedules as $schedule): ?>
    <tr>
      <td><?= $schedule->title ?></td>
      <td><?= $schedule->location ?></td>
      <td><?= $schedule->start_datetime ?></td>
      <td><?= $schedule->end_datetime ?></td>
      <td><?= $schedule->all_day ? '終日' : '' ?></td>
      <td><?= $schedule->description ?></td>
      <td>
        <a href="<?= Uri::create('schedule/edit/' . $schedule->id) ?>">編集</a>
        <a href="<?= Uri::create('schedule/delete/' . $schedule->id) ?>" onclick="return confirm('本当に削除しますか？');">削除</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<p><a href="<?= Uri::create('task') ?>">タスク一覧に戻る</a></p>