<!-- fuel/app/views/class/edit.php -->
<h1>授業の編集</h1>

<form method="post">
  <p><label>授業名: <input type="text" name="title" value="<?= $class->title ?>"></label></p>
  <p><label>説明: <textarea name="description"><?= $class->description ?></textarea></label></p>
  <p><label>年度: <input type="number" name="year" value="<?= $class->year ?>"></label></p>
  <p><label>開始月: <input type="number" name="start_month" value="<?= $class->start_month ?>"></label></p>
  <p><label>終了月: <input type="number" name="end_month" value="<?= $class->end_month ?>"></label></p>
  <p><label>曜日: <input type="text" name="day_of_week" value="<?= $class->day_of_week ?>"></label></p>
  <p><label>時限: <input type="text" name="period" value="<?= $class->period ?>"></label></p>
  <p><label>教室: <input type="text" name="class_room" value="<?= $class->class_room ?>"></label></p>
  <p><label>教員: <input type="text" name="instructor" value="<?= $class->instructor ?>"></label></p>
  <p><input type="submit" value="更新"></p>
</form>

<p><a href="<?= Uri::create('class') ?>">授業一覧に戻る</a></p>
