<?php
// fuel/app/views/common/notification-settings-modal.php
?>

<!-- 通知設定モーダル -->
<div class="modal-overlay" id="notificationSettingsModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">🔔 通知設定</h2>
            <button class="modal-close" onclick="closeNotificationSettings()">×</button>
        </div>
        
        <div class="modal-body">
            <!-- 通知許可状況 -->
            <div class="notification-status">
                <div class="status-item">
                    <span class="status-label">ブラウザ通知:</span>
                    <span class="status-value" id="notificationStatus">確認中...</span>
                    <button class="btn btn-small" id="enableNotificationBtn" onclick="enableNotifications()" style="display: none;">
                        有効にする
                    </button>
                </div>
            </div>

            <!-- 基本設定 -->
            <div class="settings-section">
                <h3>📝 タスク通知</h3>
                
                <div class="setting-item">
                    <label class="setting-label">
                        <input type="checkbox" id="taskReminderEnabled" checked>
                        タスクの締切前に通知する
                    </label>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">通知タイミング</label>
                    <select id="taskReminderMinutes" class="form-control">
                        <option value="5">5分前</option>
                        <option value="10">10分前</option>
                        <option value="15">15分前</option>
                        <option value="30" selected>30分前</option>
                        <option value="60">1時間前</option>
                        <option value="120">2時間前</option>
                        <option value="1440">1日前</option>
                    </select>
                </div>

                <div class="setting-item">
                    <label class="setting-label">
                        <input type="checkbox" id="overdueReminderEnabled" checked>
                        期限切れタスクを毎日通知する
                    </label>
                </div>
            </div>

            <!-- 予定通知 -->
            <div class="settings-section">
                <h3>📅 予定通知</h3>
                
                <div class="setting-item">
                    <label class="setting-label">
                        <input type="checkbox" id="scheduleReminderEnabled" checked>
                        予定の開始前に通知する
                    </label>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">通知タイミング</label>
                    <select id="scheduleReminderMinutes" class="form-control">
                        <option value="5">5分前</option>
                        <option value="10">10分前</option>
                        <option value="15">15分前</option>
                        <option value="30" selected>30分前</option>
                        <option value="60">1時間前</option>
                    </select>
                </div>
            </div>

            <!-- 日次リマインダー -->
            <div class="settings-section">
                <h3>🌅 日次リマインダー</h3>
                
                <div class="setting-item">
                    <label class="setting-label">
                        <input type="checkbox" id="dailyReminderEnabled" checked>
                        毎日決まった時刻に今日の予定を通知する
                    </label>
                </div>
                
                <div class="setting-item">
                    <label class="setting-label">通知時刻</label>
                    <input type="time" id="dailyReminderTime" class="form-control" value="09:00">
                </div>
            </div>

            <!-- 詳細設定 -->
            <div class="settings-section">
                <h3>⚙️ 詳細設定</h3>
                
                <div class="setting-item">
                    <label class="setting-label">
                        <input type="checkbox" id="soundEnabled" checked>
                        通知音を再生する
                    </label>
                </div>

                <div class="setting-item">
                    <label class="setting-label">
                        <input type="checkbox" id="persistentNotification">
                        重要な通知を手動で閉じるまで表示する
                    </label>
                </div>

                <div class="setting-item">
                    <label class="setting-label">通知チェック間隔</label>
                    <select id="checkInterval" class="form-control">
                        <option value="30000">30秒</option>
                        <option value="60000" selected>1分</option>
                        <option value="300000">5分</option>
                    </select>
                </div>
            </div>

            <!-- テスト通知 -->
            <div class="settings-section">
                <h3>🧪 テスト</h3>
                
                <div class="setting-item">
                    <button class="btn btn-test" onclick="testNotification()">
                        テスト通知を送信
                    </button>
                    <small>通知が正常に動作するかテストできます</small>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-cancel" onclick="closeNotificationSettings()">キャンセル</button>
            <button class="btn btn-save" onclick="saveNotificationSettings()">保存</button>
        </div>
    </div>
</div>
