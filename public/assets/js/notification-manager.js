// 通知管理クラス
class TaskNotificationManager {
  constructor() {
    this.checkInterval = 60000; // 1分ごとにチェック
    this.notificationPermission = false;
    this.intervalId = null;
    this.init();
  }

  // 初期化
  async init() {
    await this.requestNotificationPermission();
    this.startNotificationCheck();
    this.loadNotificationSettings();
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
      icon: "/task-manager/public/assets/img/icon-192.png",
      tag: "welcome",
    });
  }

  // 通知設定を読み込み
  loadNotificationSettings() {
    const settings = JSON.parse(
      localStorage.getItem("notificationSettings") || "{}"
    );
    this.settings = {
      taskReminder: settings.taskReminder !== false, // デフォルト有効
      scheduleReminder: settings.scheduleReminder !== false,
      reminderMinutes: settings.reminderMinutes || 30, // 30分前
      dailyReminder: settings.dailyReminder !== false,
      dailyReminderTime: settings.dailyReminderTime || "09:00",
    };
  }

  // 通知設定を保存
  saveNotificationSettings(settings) {
    this.settings = { ...this.settings, ...settings };
    localStorage.setItem("notificationSettings", JSON.stringify(this.settings));
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
    if (!this.settings.taskReminder || !this.notificationPermission) return;

    try {
      const response = await fetch(
        "/task-manager/public/api/tasks/upcoming?" +
          new URLSearchParams({
            minutes: this.settings.reminderMinutes,
          })
      );

      if (!response.ok) return;

      const data = await response.json();

      if (data.success && data.tasks) {
        data.tasks.forEach((task) => {
          this.showTaskReminder(task);
        });
      }
    } catch (error) {
      console.error("タスク通知チェックエラー:", error);
    }
  }

  // 近づいている予定をチェック
  async checkUpcomingSchedules() {
    if (!this.settings.scheduleReminder || !this.notificationPermission) return;

    try {
      const response = await fetch(
        "/task-manager/public/api/schedules/upcoming?" +
          new URLSearchParams({
            minutes: this.settings.reminderMinutes,
          })
      );

      if (!response.ok) return;

      const data = await response.json();

      if (data.success && data.schedules) {
        data.schedules.forEach((schedule) => {
          this.showScheduleReminder(schedule);
        });
      }
    } catch (error) {
      console.error("予定通知チェックエラー:", error);
    }
  }

  // 日次リマインダーをチェック
  checkDailyReminder() {
    if (!this.settings.dailyReminder || !this.notificationPermission) return;

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
        icon: "/task-manager/public/assets/img/task-icon.png",
        tag: notificationId,
        requireInteraction: true,
        actions: [
          { action: "complete", title: "完了にする" },
          { action: "view", title: "詳細を見る" },
        ],
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
        icon: "/task-manager/public/assets/img/schedule-icon.png",
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
      // 今日のタスク数を取得
      const today = new Date().toISOString().split("T")[0];
      const response = await fetch(
        `/task-manager/public/api/tasks?start_date=${today}&end_date=${today}`
      );

      if (response.ok) {
        const data = await response.json();
        const taskCount = data.data
          ? data.data.filter((task) => task.status === 0).length
          : 0;

        let message = "今日も一日頑張りましょう！";
        if (taskCount > 0) {
          message = `今日は${taskCount}件のタスクがあります。頑張りましょう！`;
        }

        this.createNotification(
          "🌅 おはようございます",
          {
            body: message,
            icon: "/task-manager/public/assets/img/daily-icon.png",
            tag: notificationId,
          },
          () => {
            window.focus();
            window.location.href = "/task-manager/public/task/day";
          }
        );
      }
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
    if (newSettings.reminderMinutes !== undefined) {
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
