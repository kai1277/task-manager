// 通知管理クラス
class TaskNotificationManager {
  constructor() {
    this.checkInterval = 60000; // 1分ごとにチェック
    this.notificationPermission = false;
    this.intervalId = null;
    this.settings = {}; // 初期化を追加
    this.init();
  }

  // 初期化
  async init() {
    this.loadNotificationSettings(); // 順序を変更：先に設定をロード
    await this.requestNotificationPermission();
    this.startNotificationCheck();
  }

  // 通知許可を要求
  async requestNotificationPermission() {
    if ("Notification" in window) {
      const permission = await Notification.requestPermission();
      this.notificationPermission = permission === "granted";

      if (this.notificationPermission) {
        console.log("通知が許可されました");
        this.showWelcomeNotification();
      } else {
        console.log("通知が拒否されました");
      }
    } else {
      console.log("このブラウザは通知をサポートしていません");
    }
  }

  // ウェルカム通知
  showWelcomeNotification() {
    this.createNotification("タスク管理", {
      body: "通知機能が有効になりました！",
      // アイコンパスを修正（存在しない場合はコメントアウト）
      // icon: "/task-manager/public/assets/img/icon-192.png",
      tag: "welcome",
    });
  }

  // 通知設定を読み込み
  loadNotificationSettings() {
    const settings = JSON.parse(
      localStorage.getItem("notificationSettings") || "{}"
    );

    // デフォルト値を確実に設定
    this.settings = {
      taskReminder: settings.taskReminder !== false, // デフォルト有効
      scheduleReminder: settings.scheduleReminder !== false,
      reminderMinutes: settings.reminderMinutes || 30, // 30分前
      scheduleReminderMinutes: settings.scheduleReminderMinutes || 30,
      dailyReminder: settings.dailyReminder !== false,
      dailyReminderTime: settings.dailyReminderTime || "09:00",
      overdueReminder: settings.overdueReminder !== false,
      soundEnabled: settings.soundEnabled !== false,
      persistentNotification: settings.persistentNotification === true,
      checkInterval: settings.checkInterval || 60000,
    };

    console.log("通知設定をロードしました:", this.settings);
  }

  // 通知設定を保存
  saveNotificationSettings(settings) {
    this.settings = { ...this.settings, ...settings };
    localStorage.setItem("notificationSettings", JSON.stringify(this.settings));
    console.log("通知設定を保存しました:", this.settings);
  }

  // 定期チェックを開始
  startNotificationCheck() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
    }

    this.intervalId = setInterval(() => {
      this.checkUpcomingTasks();
      this.checkUpcomingSchedules();
      this.checkDailyReminder();
    }, this.checkInterval);

    // 初回実行
    this.checkUpcomingTasks();
    this.checkUpcomingSchedules();
  }

  // 停止
  stop() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
      this.intervalId = null;
    }
  }

  // 近づいているタスクをチェック
  async checkUpcomingTasks() {
    // 設定チェックを修正
    if (
      !this.settings ||
      !this.settings.taskReminder ||
      !this.notificationPermission
    ) {
      console.log("タスクリマインダーが無効、またはthis.settingsが未定義");
      return;
    }

    try {
      // APIエンドポイントを修正（実際に存在するエンドポイントに変更）
      const response = await fetch(
        "/task-manager/public/api/notifications/upcoming-tasks?" +
          new URLSearchParams({
            minutes: this.settings.reminderMinutes,
          })
      );

      if (!response.ok) {
        console.log("タスクAPI応答エラー:", response.status);
        return;
      }

      const data = await response.json();

      if (data.success && data.tasks) {
        data.tasks.forEach((task) => {
          this.showTaskReminder(task);
        });
      }
    } catch (error) {
      console.error("タスク通知チェックエラー:", error);
      // エラーが発生してもアプリを停止させない
    }
  }

  // 近づいている予定をチェック
  async checkUpcomingSchedules() {
    // 設定チェックを修正
    if (
      !this.settings ||
      !this.settings.scheduleReminder ||
      !this.notificationPermission
    ) {
      console.log("予定リマインダーが無効、またはthis.settingsが未定義");
      return;
    }

    try {
      // APIエンドポイントを修正
      const response = await fetch(
        "/task-manager/public/api/notifications/upcoming-schedules?" +
          new URLSearchParams({
            minutes:
              this.settings.scheduleReminderMinutes ||
              this.settings.reminderMinutes,
          })
      );

      if (!response.ok) {
        console.log("予定API応答エラー:", response.status);
        return;
      }

      const data = await response.json();

      if (data.success && data.schedules) {
        data.schedules.forEach((schedule) => {
          this.showScheduleReminder(schedule);
        });
      }
    } catch (error) {
      console.error("予定通知チェックエラー:", error);
      // エラーが発生してもアプリを停止させない
    }
  }

  // 日次リマインダーをチェック
  checkDailyReminder() {
    if (
      !this.settings ||
      !this.settings.dailyReminder ||
      !this.notificationPermission
    )
      return;

    const now = new Date();
    const currentTime =
      now.getHours().toString().padStart(2, "0") +
      ":" +
      now.getMinutes().toString().padStart(2, "0");

    // 設定時刻と現在時刻が一致する場合（±1分の誤差を許容）
    if (this.isTimeMatch(currentTime, this.settings.dailyReminderTime)) {
      this.showDailyReminder();
    }
  }

  // 時刻マッチング（±1分）
  isTimeMatch(current, target) {
    const currentMinutes = this.timeToMinutes(current);
    const targetMinutes = this.timeToMinutes(target);
    return Math.abs(currentMinutes - targetMinutes) <= 1;
  }

  // 時刻を分に変換
  timeToMinutes(timeStr) {
    const [hours, minutes] = timeStr.split(":").map(Number);
    return hours * 60 + minutes;
  }

  // タスクリマインダー通知
  showTaskReminder(task) {
    const notificationId = `task-${task.id}-${Date.now()}`;

    // 既に通知済みかチェック
    if (this.isAlreadyNotified(notificationId)) return;

    const timeStr = task.due_time
      ? ` (${new Date("2000-01-01 " + task.due_time).toLocaleTimeString(
          "ja-JP",
          { hour: "2-digit", minute: "2-digit" }
        )})`
      : "";

    this.createNotification(
      `📝 タスクの締切が近づいています`,
      {
        body: `${task.title}${timeStr}`,
        // icon: "/task-manager/public/assets/img/task-icon.png", // 一時的にコメントアウト
        tag: notificationId,
        requireInteraction: true,
      },
      () => {
        // クリック時の動作
        window.focus();
        window.location.href = "/task-manager/public/task/day/" + task.due_date;
      }
    );

    this.markAsNotified(notificationId);
  }

  // 予定リマインダー通知
  showScheduleReminder(schedule) {
    const notificationId = `schedule-${schedule.id}-${Date.now()}`;

    if (this.isAlreadyNotified(notificationId)) return;

    const startTime = new Date(schedule.start_datetime).toLocaleTimeString(
      "ja-JP",
      {
        hour: "2-digit",
        minute: "2-digit",
      }
    );

    this.createNotification(
      `📅 予定の時間が近づいています`,
      {
        body: `${schedule.title} (${startTime}〜)`,
        // icon: "/task-manager/public/assets/img/schedule-icon.png", // 一時的にコメントアウト
        tag: notificationId,
        requireInteraction: true,
      },
      () => {
        window.focus();
        const date = schedule.start_datetime.split(" ")[0];
        window.location.href = "/task-manager/public/task/day/" + date;
      }
    );

    this.markAsNotified(notificationId);
  }

  // 日次リマインダー
  async showDailyReminder() {
    const notificationId = `daily-${new Date().toDateString()}`;

    if (this.isAlreadyNotified(notificationId)) return;

    try {
      // 今日のタスク数を取得（シンプル版 - APIが無い場合のフォールバック）
      let message = "今日も一日頑張りましょう！";

      this.createNotification(
        "🌅 おはようございます",
        {
          body: message,
          // icon: "/task-manager/public/assets/img/daily-icon.png", // 一時的にコメントアウト
          tag: notificationId,
        },
        () => {
          window.focus();
          window.location.href = "/task-manager/public/task/day";
        }
      );
    } catch (error) {
      console.error("日次リマインダーエラー:", error);
    }

    this.markAsNotified(notificationId);
  }

  // 通知作成
  createNotification(title, options, clickHandler) {
    if (!this.notificationPermission) return;

    const notification = new Notification(title, options);

    if (clickHandler) {
      notification.onclick = clickHandler;
    }

    // 5秒後に自動で閉じる
    setTimeout(() => {
      notification.close();
    }, 5000);

    return notification;
  }

  // 通知済みかチェック
  isAlreadyNotified(notificationId) {
    const notified = JSON.parse(localStorage.getItem("notifiedItems") || "[]");
    return notified.includes(notificationId);
  }

  // 通知済みとしてマーク
  markAsNotified(notificationId) {
    const notified = JSON.parse(localStorage.getItem("notifiedItems") || "[]");
    notified.push(notificationId);

    // 古い通知記録を削除（24時間以上古いもの）
    const oneDayAgo = Date.now() - 24 * 60 * 60 * 1000;
    const filtered = notified.filter((id) => {
      const timestamp = id.split("-").pop();
      return timestamp && parseInt(timestamp) > oneDayAgo;
    });

    localStorage.setItem("notifiedItems", JSON.stringify(filtered));
  }

  // 設定画面用のメソッド
  getSettings() {
    return { ...this.settings };
  }

  updateSettings(newSettings) {
    this.saveNotificationSettings(newSettings);

    // チェック間隔が変更された場合は再開
    if (newSettings.checkInterval !== undefined) {
      this.checkInterval = newSettings.checkInterval;
      this.startNotificationCheck();
    }
  }

  // 手動で通知をテスト
  testNotification() {
    this.createNotification("🔔 テスト通知", {
      body: "通知機能が正常に動作しています",
      tag: "test-notification",
    });
  }
}

// グローバルに初期化
let taskNotificationManager;

// ページ読み込み時に開始
document.addEventListener("DOMContentLoaded", function () {
  // ログインしている場合のみ通知機能を開始
  if (
    document.body.classList.contains("logged-in") ||
    !window.location.pathname.includes("/user/login")
  ) {
    taskNotificationManager = new TaskNotificationManager();

    // ページを離れる時に停止
    window.addEventListener("beforeunload", function () {
      if (taskNotificationManager) {
        taskNotificationManager.stop();
      }
    });
  }
});

// 設定画面での使用例
function openNotificationSettings() {
  if (!taskNotificationManager) return;

  const settings = taskNotificationManager.getSettings();

  // モーダルを表示して設定を編集
  showNotificationSettingsModal(settings);
}

function saveNotificationSettings(newSettings) {
  if (taskNotificationManager) {
    taskNotificationManager.updateSettings(newSettings);
    alert("通知設定を保存しました");
  }
}
