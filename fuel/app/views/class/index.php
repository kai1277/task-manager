<h1>履修科目一覧</h1>

<p><a href="<?= Uri::create('class/create') ?>">＋ 新しい科目を追加</a></p>

<table border="1">
  <tr>
    <th>科目名</th>
    <th>年度</th>
    <th>期間</th>
    <th>曜日</th>
    <th>時限</th>
    <th>教室</th>
    <th>担当教員</th>
    <th>説明</th>
    <th>操作</th>
  </tr>

  <?php 
  $days = ['', '月', '火', '水', '木', '金', '土', '日'];
  foreach ($classes as $class): 
  ?>
    <tr>
      <td><?= $class->title ?></td>
      <td><?= $class->year ?>年度</td>
      <td><?= $class->start_month ?>月〜<?= $class->end_month ?>月</td>
      <td><?= $days[$class->day_of_week] ?>曜日</td>
      <td><?= $class->period ?>時限</td>
      <td><?= $class->class_room ?></td>
      <td><?= $class->instructor ?></td>
      <td><?= $class->description ?></td>
      <td>
        <a href="<?= Uri::create('class/edit/' . $class->id) ?>">編集</a>
        <a href="<?= Uri::create('class/delete/' . $class->id) ?>" onclick="return confirm('本当に削除しますか？');">削除</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<p><a href="<?= Uri::create('task') ?>">タスク一覧に戻る</a></p>