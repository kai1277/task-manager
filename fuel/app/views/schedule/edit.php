<h1>予定の編集</h1>

<form method="post">
  <p>
    <label>予定名:
      <input type="text" name="title" value="<?= e($schedule->title) ?>" required>
    </label>
  </p>
  <p>
    <label>場所:
      <input type="text" name="location" value="<?= e($schedule->location) ?>">
    </label>
  </p>
  <p>
    <label>説明:
      <textarea name="description"><?= e($schedule->description) ?></textarea>
    </label>
  </p>
  <p>
    <label>開始日:
      <input type="date" name="start_date" value="<?= date('Y-m-d', strtotime($schedule->start_datetime)) ?>" required>
    </label>
    <label>開始時間:
      <input type="time" name="start_time" value="<?= date('H:i', strtotime($schedule->start_datetime)) ?>">
    </label>
  </p>
  <p>
    <label>終了日:
      <input type="date" name="end_date" value="<?= date('Y-m-d', strtotime($schedule->end_datetime)) ?>" required>
    </label>
    <label>終了時間:
      <input type="time" name="end_time" value="<?= date('H:i', strtotime($schedule->end_datetime)) ?>">
    </label>
  </p>
  <p>
    <label>
      <input type="checkbox" name="all_day" value="1" <?= $schedule->all_day ? 'checked' : '' ?>> 終日
    </label>
  </p>
  <p>
    <input type="submit" value="更新">
  </p>
</form>

<p><a href="<?= Uri::create('schedule') ?>">予定一覧に戻る</a></p>
