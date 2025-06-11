<h1>履修科目編集</h1>

<form method="post">
  <p>
    <label>科目名: <input type="text" name="title" value="<?= $class->title ?>" required></label>
  </p>
  <p>
    <label>説明: <textarea name="description"><?= $class->description ?></textarea></label>
  </p>
  <p>
    <label>年度: 
      <select name="year" required>
        <?php for($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
          <option value="<?= $y ?>" <?= $class->year == $y ? 'selected' : '' ?>><?= $y ?>年度</option>
        <?php endfor; ?>
      </select>
    </label>
  </p>
  <p>
    <label>開始月: 
      <select name="start_month" required>
        <?php for($m = 1; $m <= 12; $m++): ?>
          <option value="<?= $m ?>" <?= $class->start_month == $m ? 'selected' : '' ?>><?= $m ?>月</option>
        <?php endfor; ?>
      </select>
    </label>
  </p>
  <p>
    <label>終了月: 
      <select name="end_month" required>
        <?php for($m = 1; $m <= 12; $m++): ?>
          <option value="<?= $m ?>" <?= $class->end_month == $m ? 'selected' : '' ?>><?= $m ?>月</option>
        <?php endfor; ?>
      </select>
    </label>
  </p>
  <p>
    <label>曜日: 
      <select name="day_of_week" required>
        <option value="1" <?= $class->day_of_week == 1 ? 'selected' : '' ?>>月曜日</option>
        <option value="2" <?= $class->day_of_week == 2 ? 'selected' : '' ?>>火曜日</option>
        <option value="3" <?= $class->day_of_week == 3 ? 'selected' : '' ?>>水曜日</option>
        <option value="4" <?= $class->day_of_week == 4 ? 'selected' : '' ?>>木曜日</option>
        <option value="5" <?= $class->day_of_week == 5 ? 'selected' : '' ?>>金曜日</option>
        <option value="6" <?= $class->day_of_week == 6 ? 'selected' : '' ?>>土曜日</option>
        <option value="7" <?= $class->day_of_week == 7 ? 'selected' : '' ?>>日曜日</option>
      </select>
    </label>
  </p>
  <p>
    <label>時限: 
      <select name="period" required>
        <?php for($p = 1; $p <= 7; $p++): ?>
          <option value="<?= $p ?>" <?= $class->period == $p ? 'selected' : '' ?>><?= $p ?>時限</option>
        <?php endfor; ?>
      </select>
    </label>
  </p>
  <p>
    <label>教室: <input type="text" name="class_room" value="<?= $class->class_room ?>"></label>
  </p>
  <p>
    <label>担当教員: <input type="text" name="instructor" value="<?= $class->instructor ?>"></label>
  </p>
  <p>
    <input type="submit" value="更新">
  </p>
</form>

<p><a href="<?= Uri::create('class') ?>">履修科目一覧に戻る</a></p>