<!-- fuel/app/views/class/create.php -->
<h1>新しい授業の作成</h1>

<form method="post">
  <p><label>授業名: <input type="text" name="title" required></label></p>
  <p><label>説明: <textarea name="description"></textarea></label></p>
  <p><label>年度: <input type="number" name="year" value="<?= date('Y') ?>"></label></p>
  <p><label>開始月: <input type="number" name="start_month" min="1" max="12"></label></p>
  <p><label>終了月: <input type="number" name="end_month" min="1" max="12"></label></p>
  <p><label>曜日: <input type="text" name="day_of_week"></label></p>
  <p><label>時限: <input type="text" name="period"></label></p>
  <p><label>教室: <input type="text" name="class_room"></label></p>
  <p><label>教員: <input type="text" name="instructor"></label></p>
  <p><input type="submit" value="作成"></p>
</form>

<p><a href="<?= Uri::create('class') ?>">授業一覧に戻る</a></p>
