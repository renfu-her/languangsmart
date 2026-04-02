## 1. 後端：強制重新計算 payment_amount

- [x] 1.1 修改 `OrderController::update()`，移除對前端傳入 `payment_amount` 的使用，改為一律呼叫 `calculateOrderAmount()` 重新計算
- [x] 1.2 在 `update()` 中加入費率資料缺失的保護邏輯：若 `calculateOrderAmount()` 回傳 0，保留原有金額並在回應中加入警告訊息
- [x] 1.3 確認 `store()` 中的 `calculateOrderAmount()` 呼叫邏輯正確（目前若前端傳入非零值會略過計算，需一致化）
- [x] 1.4 確保 `update()` API 回應中包含計算後的完整訂單物件（含更新後的 `payment_amount`）

## 2. 後端：新增 Artisan 修正 Command

- [x] 2.1 建立 `app/Console/Commands/FixOrderAmounts.php`，實作 `orders:fix-amounts` command
- [x] 2.2 實作 dry-run 邏輯：遍歷所有訂單，比對 DB `payment_amount` 與 `calculateOrderAmount()` 計算值，輸出差異清單
- [x] 2.3 實作 `--apply` 選項：將差異訂單的 `payment_amount` 更新為計算值
- [x] 2.4 加入計算結果為 0 的跳過邏輯，並輸出警告訊息
- [x] 2.5 在 `app/Console/Kernel.php`（或 `routes/console.php`）中註冊此 command（Laravel 12 自動探索，無需手動）

## 3. 前端：修正訂單列表 State 更新

- [x] 3.1 在 `OrdersPage.tsx` 的訂單更新成功 callback 中，使用後端回傳的完整訂單物件取代 state 中的對應項目（`setOrders(prev => prev.map(o => o.id === updated.id ? updated : o))`）
- [x] 3.2 確認編輯 modal 關閉後不再依賴 `window.location.reload()` 來更新金額顯示（若有其他理由需要 reload 則保留，但金額應在 reload 前即正確）
- [x] 3.3 確認合作夥伴月統計的 modal 在訂單更新後重新 fetch 統計資料

## 4. 驗證與資料修正

- [ ] 4.1 在開發環境執行 `php artisan orders:fix-amounts`（dry-run）確認受影響訂單清單（需 Laragon MySQL 啟動）
- [ ] 4.2 確認蔡宗哲訂單（$600 → $3,600）在差異清單中
- [ ] 4.3 執行 `php artisan orders:fix-amounts --apply` 修正資料
- [ ] 4.4 驗證訂單列表顯示正確金額
- [ ] 4.5 驗證合作夥伴月統計（神秘沙灘）顯示 $14,700
