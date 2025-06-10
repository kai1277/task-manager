<!-- fuel/app/views/class/index.php -->
<h1>授業一覧</h1>

<p><a href="<?= Uri::create('class/create') ?>">＋ 新しい授業を作成</a></p>

<table border="1">
  <tr>
    <th>授業名</th>
    <th>説明</th>
    <th>曜日</th>
    <th>時限</th>
    <th>教室</th>
    <th>教員</th>
    <th>操作</th>
  </tr>
  <?php foreach ($classes as $class): ?>
    <tr>
      <td><?= $class->title ?></td>
      <td><?= $class->description ?></td>
      <td><?= $class->day_of_week ?></td>
      <td><?= $class->period ?></td>
      <td><?= $class->class_room ?></td>
      <td><?= $class->instructor ?></td>
      <td>
        <a href="<?= Uri::create('class/edit/' . $class->id) ?>">編集</a>
        <a href="<?= Uri::create('class/delete/' . $class->id) ?>" onclick="return confirm('本当に削除しますか？');">削除</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<p><a href="<?= Uri::create('task') ?>">タスク一覧に戻る</a></p>
