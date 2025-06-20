// 通知設定モーダルの管理
class NotificationSettingsManager {
  constructor() {
    this.loadSettings();
    this.updatePermissionStatus();
  }

  // 設定モーダルを開く
  open() {
    document
      .getElementById("notificationSettingsModal")
      .classList.add("active");
    document.body.style.overflow = "hidden";
    this.loadSettingsToForm();
    this.updatePermissionStatus();
  }

  // 設定モーダルを閉じる
  close() {
    document
      .getElementById("notificationSettingsModal")
      .classList.remove("active");
    document.body.style.overflow = "";
  }

  // 現在の設定をフォームに読み込み
  loadSettingsToForm() {
    const settings = this.getCurrentSettings();

    document.getElementById("taskReminderEnabled").checked =
      settings.taskReminder !== false;
    document.getElementById("taskReminderMinutes").value =
      settings.reminderMinutes || 30;
    document.getElementById("scheduleReminderEnabled").checked =
      settings.scheduleReminder !== false;
    document.getElementById("scheduleReminderMinutes").value =
      settings.scheduleReminderMinutes || settings.reminderMinutes || 30;
    document.getElementById("dailyReminderEnabled").checked =
      settings.dailyReminder !== false;
    document.getElementById("dailyReminderTime").value =
      settings.dailyReminderTime || "09:00";
    document.getElementById("overdueReminderEnabled").checked =
      settings.overdueReminder !== false;
    document.getElementById("soundEnabled").checked =
      settings.soundEnabled !== false;
    document.getElementById("persistentNotification").checked =
      settings.persistentNotification === true;
    document.getElementById("checkInterval").value =
      settings.checkInterval || 60000;
  }

  // フォームから設定を取得
  getSettingsFromForm() {
    return {
      taskReminder: document.getElementById("taskReminderEnabled").checked,
      reminderMinutes: parseInt(
        document.getElementById("taskReminderMinutes").value
      ),
      scheduleReminder: document.getElementById("scheduleReminderEnabled")
        .checked,
      scheduleReminderMinutes: parseInt(
        document.getElementById("scheduleReminderMinutes").value
      ),
      dailyReminder: document.getElementById("dailyReminderEnabled").checked,
      dailyReminderTime: document.getElementById("dailyReminderTime").value,
      overdueReminder: document.getElementById("overdueReminderEnabled")
        .checked,
      soundEnabled: document.getElementById("soundEnabled").checked,
      persistentNotification: document.getElementById("persistentNotification")
        .checked,
      checkInterval: parseInt(document.getElementById("checkInterval").value),
    };
  }

  // 現在の設定を取得
  getCurrentSettings() {
    if (window.taskNotificationManager) {
      return window.taskNotificationManager.getSettings();
    }

    return JSON.parse(localStorage.getItem("notificationSettings") || "{}");
  }

  // 設定を保存（サーバーAPIを使わないバージョン）
  async saveSettings() {
    const settings = this.getSettingsFromForm();

    try {
      // ローカルストレージに保存
      localStorage.setItem("notificationSettings", JSON.stringify(settings));

      // 通知マネージャーに設定を反映
      if (window.taskNotificationManager) {
        window.taskNotificationManager.updateSettings(settings);
      }

      this.showSuccessMessage("通知設定を保存しました");
      this.close();
    } catch (error) {
      console.error("設定保存エラー:", error);
      this.showErrorMessage("設定の保存に失敗しました");
    }
  }

  // サーバーに設定を保存（オプション - APIが実装されている場合のみ）
  async saveSettingsToServer(settings) {
    // APIが実装されていない場合はスキップ
    try {
      const response = await fetch(
        "/task-manager/public/api/notifications/settings",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(settings),
        }
      );

      if (!response.ok) {
        console.log("サーバー保存はスキップ（APIが未実装）");
        return { success: true }; // エラーとせずに続行
      }

      return await response.json();
    } catch (error) {
      console.log("サーバー保存はスキップ（APIが未実装）");
      return { success: true }; // エラーとせずに続行
    }
  }

  // 設定を読み込み（オプション）
  async loadSettings() {
    try {
      const response = await fetch(
        "/task-manager/public/api/notifications/settings"
      );
      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          localStorage.setItem(
            "notificationSettings",
            JSON.stringify(data.settings)
          );
        }
      }
    } catch (error) {
      console.log("サーバー設定読み込みはスキップ（APIが未実装）");
      // エラーとしない - ローカル設定を使用
    }
  }

  // 通知許可状況を更新
  updatePermissionStatus() {
    const statusElement = document.getElementById("notificationStatus");
    const enableBtn = document.getElementById("enableNotificationBtn");

    if (!statusElement || !enableBtn) return;

    if (!("Notification" in window)) {
      statusElement.textContent = "サポートされていません";
      statusElement.className = "status-value disabled";
      enableBtn.style.display = "none";
      return;
    }

    switch (Notification.permission) {
      case "granted":
        statusElement.textContent = "有効";
        statusElement.className = "status-value enabled";
        enableBtn.style.display = "none";
        break;
      case "denied":
        statusElement.textContent = "拒否されています";
        statusElement.className = "status-value denied";
        enableBtn.style.display = "none";
        break;
      default:
        statusElement.textContent = "未設定";
        statusElement.className = "status-value disabled";
        enableBtn.style.display = "inline-block";
    }
  }

  // 通知を有効にする
  async enableNotifications() {
    try {
      const permission = await Notification.requestPermission();
      this.updatePermissionStatus();

      if (permission === "granted") {
        this.showSuccessMessage("通知が有効になりました");

        if (window.taskNotificationManager) {
          window.taskNotificationManager.notificationPermission = true;
          window.taskNotificationManager.showWelcomeNotification();
        }
      } else {
        this.showErrorMessage(
          "通知が拒否されました。ブラウザの設定から手動で有効にしてください。"
        );
      }
    } catch (error) {
      console.error("通知許可エラー:", error);
      this.showErrorMessage("通知の有効化に失敗しました");
    }
  }

  // テスト通知
  testNotification() {
    if (window.taskNotificationManager) {
      window.taskNotificationManager.testNotification();
    } else {
      if (Notification.permission === "granted") {
        new Notification("🔔 テスト通知", {
          body: "通知機能が正常に動作しています",
          // icon: "/task-manager/public/assets/img/icon-192.png", // 一時的にコメントアウト
        });
      } else {
        alert("通知が有効になっていません。まず通知を有効にしてください。");
      }
    }
  }

  // メッセージ表示
  showMessage(message, type) {
    const messageDiv = document.createElement("div");
    messageDiv.className = `flash-message ${type}-message`;
    messageDiv.textContent = message;
    messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10001;
            cursor: pointer;
            max-width: 300px;
            animation: slideInDown 0.3s ease;
        `;

    document.body.appendChild(messageDiv);

    setTimeout(() => {
      if (messageDiv.parentNode) {
        messageDiv.style.animation = "fadeOut 0.3s ease forwards";
        setTimeout(() => {
          if (messageDiv.parentNode) {
            messageDiv.parentNode.removeChild(messageDiv);
          }
        }, 300);
      }
    }, 3000);

    messageDiv.onclick = () => {
      if (messageDiv.parentNode) {
        messageDiv.style.animation = "fadeOut 0.3s ease forwards";
        setTimeout(() => {
          if (messageDiv.parentNode) {
            messageDiv.parentNode.removeChild(messageDiv);
          }
        }, 300);
      }
    };
  }

  showSuccessMessage(message) {
    this.showMessage(message, "success");
  }

  showErrorMessage(message) {
    this.showMessage(message, "error");
  }
}

// グローバルインスタンス
let notificationSettingsManager;

// グローバル関数
function openNotificationSettings() {
  if (!notificationSettingsManager) {
    notificationSettingsManager = new NotificationSettingsManager();
  }
  notificationSettingsManager.open();
}

function closeNotificationSettings() {
  if (notificationSettingsManager) {
    notificationSettingsManager.close();
  }
}

function saveNotificationSettings() {
  if (notificationSettingsManager) {
    notificationSettingsManager.saveSettings();
  }
}

function enableNotifications() {
  if (notificationSettingsManager) {
    notificationSettingsManager.enableNotifications();
  }
}

function testNotification() {
  if (notificationSettingsManager) {
    notificationSettingsManager.testNotification();
  }
}

// 初期化
document.addEventListener("DOMContentLoaded", function () {
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeNotificationSettings();
    }
  });

  const modal = document.getElementById("notificationSettingsModal");
  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === this) {
        closeNotificationSettings();
      }
    });
  }
});
