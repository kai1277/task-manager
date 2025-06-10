<h1>新しい予定の作成</h1>

<form method="post">
  <p>
    <label>予定名: <input type="text" name="title" required></label>
  </p>
  <p>
    <label>場所: <input type="text" name="location"></label>
  </p>
  <p>
    <label>説明: <textarea name="description"></textarea></label>
  </p>
  <p>
    <label>開始日: <input type="date" name="start_date" required></label>
    <label>開始時間: <input type="time" name="start_time"></label>
  </p>
  <p>
    <label>終了日: <input type="date" name="end_date" required></label>
    <label>終了時間: <input type="time" name="end_time"></label>
  </p>
  <p>
    <label><input type="checkbox" name="all_day" value="1"> 終日</label>
  </p>
  <p>
    <input type="submit" value="作成">
  </p>
</form>

<p><a href="<?= Uri::create('schedule') ?>">予定一覧に戻る</a></p>