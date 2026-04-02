## Context

目前訂單金額有三個可能來源：前端自動計算、使用者在 modal 中手動修改、以及後端在 store/update 時重新決定是否覆蓋 `payment_amount`。這讓「使用者最後在表單裡看到並按下儲存的金額」不一定等於 DB 最終值，進而造成列表頁、統計資料與編輯 modal 顯示不一致。

這次需求要把金額持久化來源收斂成單一路徑：新增或編輯訂單時，最終寫入 DB 的 `payment_amount` 必須等於提交當下表單中的 `payment_amount`。自動計算、恢復原計算與人工覆寫仍可存在，但都必須先反映在表單值，再由後端原樣寫入。

## Goals / Non-Goals

**Goals:**
- 讓新增與編輯訂單時，表單提交的 `payment_amount` 與 DB 最終值一致。
- 消除 modal、列表頁與統計資料之間的金額不一致問題。
- 保留前端的自動計算、恢復原計算與人工覆寫能力，但收斂為單一持久化來源。
- 讓前端列表更新與後端回應物件維持一致，避免儲存後短暫顯示舊金額。

**Non-Goals:**
- 不重新定義調車費金額公式本身。
- 不新增新的資料表欄位記錄金額來源。
- 不處理既有歷史資料中已經不一致的訂單回補；本次只處理新提交的新增/編輯流程。

## Decisions

### 1. 以提交 payload 的 `payment_amount` 作為唯一持久化來源

新增與編輯訂單時，後端只負責驗證 `payment_amount` 為有效數值並寫入 DB，不再於 update 階段根據機車、時間或舊值自行改寫。

這樣可以保證使用者在提交前看到的最終金額，就是資料庫與列表後續看到的金額。

替代方案是維持後端 selective recalculation，但那會讓表單金額與 DB 仍可能分叉，無法解決這次需求。

### 2. 自動計算與人工覆寫都必須先回填到表單

前端 `AddOrderModal` 保留現有自動計算、恢復原計算與手動輸入能力，但無論來源是什麼，最後送出 API 的都必須是同一個 `formData.payment_amount`。

替代方案是在 API 額外傳 `calculated_amount`、`manual_amount` 等欄位，再由後端選擇其中之一，但這會重新引入多個金額來源，與本次目標相反。

### 3. 後端只在新增流程做「缺值時補算」防呆

若新增訂單時前端沒有成功帶出 `payment_amount`，後端仍可在 store 階段以現有計算邏輯作為 fallback，避免建立出空金額訂單；但只要 payload 已有有效 `payment_amount`，後端就直接採用。

替代方案是新增與編輯一律完全信任前端，不做任何 fallback，但這會讓資料缺漏時更容易寫入空值。

## Risks / Trade-offs

- [前端計算結果錯誤會被原樣寫進 DB] → 保留恢復原計算與人工覆寫的操作軌跡在 UI 層，並以測試覆蓋新增、編輯、自動計算與手動覆寫情境。
- [建立訂單 fallback 與編輯訂單直接持久化規則不同] → 在 spec 中明確區分 store 與 update 行為，避免再次產生理解落差。
- [列表雖使用後端回傳更新，但後端若沒載入最新 relation 仍可能顯示舊資料] → update/store 後一律回傳 fresh order resource。

## Migration Plan

1. 更新 `order-amount-recalculation` spec，明確定義表單 `payment_amount` 與 DB 最終值一致。
2. 修改 `OrderController` 的 store/update 邏輯，調整 `payment_amount` 的採用規則。
3. 檢查 `AddOrderModal` 的自動計算、恢復原計算與人工覆寫流程，確保都落到同一個表單欄位再提交。
4. 驗證新增、編輯、手動覆寫、恢復原計算後儲存、列表更新等情境。

## Open Questions

- 若前端提交的 `payment_amount` 與當前自動計算值不同，是否需要在 API response 額外標示這是人工覆寫結果？目前先假設不需要。
- 歷史資料修正 command 是否要同步從「重新計算」改為「只檢查顯示與 DB 是否一致」？本次先不處理。
