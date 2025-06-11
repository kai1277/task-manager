<h1>新しい履修科目の追加</h1>

<form method="post">
  <p>
    <label>科目名: <input type="text" name="title" required></label>
  </p>
  <p>
    <label>説明: <textarea name="description"></textarea></label>
  </p>
  <p>
    <label>年度: 
      <select name="year" required>
        <option value="">選択してください</option>
        <?php for($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
          <option value="<?= $y ?>"><?= $y ?>年度</option>
        <?php endfor; ?>
      </select>
    </label>
  </p>
  <p>
    <label>開始月: 
      <select name="start_month" required>
        <option value="">選択してください</option>
        <?php for($m = 1; $m <= 12; $m++): ?>
          <option value="<?= $m ?>"><?= $m ?>月</option>
        <?php endfor; ?>
      </select>
    </label>
  </p>
  <p>
    <label>終了月: 
      <select name="end_month" required>
        <option value="">選択してください</option>
        <?php for($m = 1; $m <= 12; $m++): ?>
          <option value="<?= $m ?>"><?= $m ?>月</option>
        <?php endfor; ?>
      </select>
    </label>
  </p>
  <p>
    <label>曜日: 
      <select name="day_of_week" required>
        <option value="">選択してください</option>
        <option value="1">月曜日</option>
        <option value="2">火曜日</option>
        <option value="3">水曜日</option>
        <option value="4">木曜日</option>
        <option value="5">金曜日</option>
        <option value="6">土曜日</option>
        <option value="7">日曜日</option>
      </select>
    </label>
  </p>
  <p>
    <label>時限: 
      <select name="period" required>
        <option value="">選択してください</option>
        <?php for($p = 1; $p <= 7; $p++): ?>
          <option value="<?= $p ?>"><?= $p ?>時限</option>
        <?php endfor; ?>
      </select>
    </label>
  </p>
  <p>
    <label>教室: <input type="text" name="class_room"></label>
  </p>
  <p>
    <label>担当教員: <input type="text" name="instructor"></label>
  </p>
  <p>
    <input type="submit" value="追加">
  </p>
</form>

<p><a href="<?= Uri::create('class') ?>">履修科目一覧に戻る</a></p>
