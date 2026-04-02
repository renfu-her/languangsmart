## Context

訂單列表中偶爾出現 `payment_amount` 顯示與實際不符的問題（例：列表顯示 $600，但打開編輯表單顯示 $3,600，重新整理後列表也變回正確的 $3,600）。

**問題診斷：**

目前 `payment_amount` 的生命週期如下：
1. 建立訂單時：若前端未傳入或傳入 0，後端呼叫 `calculateOrderAmount()` 計算並存入 DB
2. 更新訂單時：相同邏輯，但若前端傳入非零的 `payment_amount`，直接使用前端值
3. 列表顯示：直接讀取 DB 的 `payment_amount` 欄位
4. 編輯表單：透過 API 取得訂單詳情（也是讀 DB）
5. 統計 API：直接 sum DB 的 `payment_amount`

根據觀察到的現象推斷：**DB 中已有正確的 3600，但 React 前端的 list state 仍保留舊值 600**。原因是某次訂單更新後，`setOrders` state update 邏輯用錯誤的值更新了記憶體中的訂單，而 DB 已正確儲存。編輯表單因為重新從 API fetch 詳情所以顯示正確，關閉時觸發 `window.location.reload()` 使列表重新 fetch 才恢復正確。

**次要問題：** `update()` 接受前端傳入的任意 `payment_amount`，若前端傳入錯誤值會直接存入 DB，缺乏驗證。

## Goals / Non-Goals

**Goals:**
- 修正 React 前端訂單列表在編輯後 state 更新不正確的問題
- 在 `OrderController::update()` 加入 `payment_amount` 後端重新計算，不信任前端傳入值
- 提供 Artisan command 掃描並修正現有資料中不一致的 `payment_amount`

**Non-Goals:**
- 不改變 `payment_amount` 的資料庫欄位結構
- 不重寫訂單列表的 fetch/pagination 架構
- 不影響前端手動輸入金額的功能（若有合理的人工調整情境，可保留 override 欄位）

## Decisions

### 決策 1：後端重新計算 payment_amount，不使用前端傳入值

**選擇**：在 `update()` 中，忽略前端傳入的 `payment_amount`，一律由後端 `calculateOrderAmount()` 重新計算。

**替代方案**：保留前端可傳入 `payment_amount` 的彈性（用於人工調整）。

**理由**：目前前端編輯表單本來就會在選擇車輛/日期後由 UI 自動帶出計算值，沒有場景需要人工覆蓋。且現有 bug 的根源就是前端傳入了錯誤值。若未來有人工調整需求，應加入獨立的「備注金額」欄位而非覆蓋計算值。

---

### 決策 2：前端 state 更新使用 API 回傳的完整訂單物件

**選擇**：在訂單更新成功後，使用後端 API 回傳的完整訂單物件更新 React state，而非在前端自行組合更新後的訂單物件。

**替代方案**：更新後直接呼叫 `window.location.reload()`（目前部分情況已如此）。

**理由**：`window.location.reload()` 雖然保險但 UX 差（頁面閃爍、捲軸跳回頂部）。更佳做法是 API 回傳完整訂單，前端用 `setOrders(prev => prev.map(o => o.id === updated.id ? updated : o))` 更新 state，確保列表值與 DB 值一致。

---

### 決策 3：提供 Artisan 修正 command

**選擇**：新增 `php artisan orders:fix-amounts` command，遍歷所有訂單，比對 `calculateOrderAmount()` 與 DB 值，差異者更新。

**理由**：解決歷史資料不一致問題，且方便未來監控。

## Risks / Trade-offs

| 風險 | 緩解措施 |
|------|----------|
| 後端強制重算可能覆蓋掉合理的人工調整金額 | 確認現有資料後執行；若有人工調整需求，未來可加 `manual_amount_override` 欄位 |
| Artisan command 大量更新資料 | 預設 dry-run 模式，需加 `--apply` 才真正寫入；先在測試環境執行 |
| `calculateOrderAmount()` 若費率表（PartnerScooterModelTransferFee）資料不完整會計算出 0 | command 應跳過計算結果為 0 的訂單，並輸出警告 |

## Migration Plan

1. 部署後端修正（`update()` 強制重算）
2. 部署前端 state 更新修正
3. 執行 `php artisan orders:fix-amounts --dry-run` 確認受影響筆數
4. 確認無誤後執行 `php artisan orders:fix-amounts --apply`
5. 驗證合作夥伴統計數字正確

**Rollback**：後端修改可直接 revert；資料修改可透過 git log 找出受影響訂單的舊值手動還原（或備份快照）。

## Open Questions

- 是否有任何情境下需要人工覆蓋 `payment_amount`？（目前假設無）
- 費率表（PartnerScooterModelTransferFee）是否完整？若有訂單找不到費率，`calculateOrderAmount()` 回傳 0，需要特別處理。
