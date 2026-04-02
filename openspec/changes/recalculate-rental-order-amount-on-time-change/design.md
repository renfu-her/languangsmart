## Context

目前訂單編輯流程同時牽涉前端 `AddOrderModal.tsx` 與後端 `OrderController::update()`：
- 前端已有 `calculateAmount()` 可依合作商、機車與起訖時間計算金額，但編輯模式下是否自動重算曾多次調整，規則容易漂移。
- 後端更新 API 目前接受前端傳來的 `payment_amount`，未以「哪些欄位真的影響費用」為準來決定是否重算。
- 現有需求希望把規則固定為：新增訂單時照常計算總金額；編輯既有訂單時只有機車或租借時間變動才重算，其餘欄位異動不得改動既有金額。

這是一個前後端都需要對齊的規則型變更。若只改單邊，會再次出現畫面顯示與 DB 實際儲存結果不一致的問題。

## Goals / Non-Goals

**Goals:**
- 明確區分新增訂單與編輯訂單的 `payment_amount` 計算規則。
- 在編輯訂單時，以明確且可測試的規則判斷是否需要重算 `payment_amount`。
- 將重算觸發條件限定為 `scooter_ids`、`start_time`、`end_time` 的變動。
- 讓前端編輯畫面與後端儲存邏輯採用相同的 change detection 規則。
- 在需要重算但無法得出有效金額時，避免覆蓋原有訂單金額。

**Non-Goals:**
- 不改動建立訂單時的既有自動計算流程。
- 不新增新的資料表欄位來記錄金額來源或版本。
- 不重新定義金額公式本身，仍沿用既有 `calculateAmount()` / `calculateOrderAmount()` 的計算邏輯。

## Decisions

### 1. 以「編輯前 baseline」對比「提交前表單值」決定是否重算

編輯 modal 開啟時，系統保留原始訂單的 `scooter_ids`、`start_time`、`end_time` 作為 baseline。提交時以正規化後的值比較：
- `scooter_ids` 以排序後陣列比較，避免同組機車只因順序不同被誤判為變更。
- 時間欄位以後端實際儲存格式或等價時間值比較，避免字串格式差異造成誤判。

替代方案是以前端的 `isAmountManuallyEdited` 或是否傳入 `payment_amount` 作為判斷依據，但這兩者都無法準確表示「影響費用的核心條件是否真的改變」。

### 2. 後端作為最終金額決策者，但僅在命中觸發條件時才重算

前端可以在新增或編輯表單中即時預覽金額，但真正寫入 DB 前，仍由後端依操作情境決定是否呼叫金額計算邏輯：
- 建立訂單時，後端一律依提交的機車與時間計算 `payment_amount`。
- 若 `scooter_ids`、`start_time`、`end_time` 任一欄位變動，後端重算並覆蓋 `payment_amount`。
- 若這三類欄位都未變動，後端保留原本的 `payment_amount`，忽略不必要的重算。

替代方案是完全信任前端提交的 `payment_amount`，但這會讓規則分散在 UI state，且無法保證 API 層資料一致性。

### 3. 前端自動重算效果只綁定在費用相關欄位

`AddOrderModal` 在編輯模式下的金額更新策略應與需求對齊：
- 機車選擇改變時，可即時更新表單中的金額預覽。
- `start_time` 或 `end_time` 改變時，可即時更新表單中的金額預覽。
- 其他欄位如 `remark`、`payment_method`、`status`、`phone` 改變時，不得觸發金額變動。

替代方案是維持目前廣義的表單同步策略，但會讓無關欄位編輯時也可能帶出金額副作用，違反本次需求。

### 4. 無法計算有效金額時保留舊值

若已命中重算條件，但計算結果為 0 或缺少必要費率資料，系統保留既有 `payment_amount`，並回傳可供前端提示的訊息。這可避免因資料缺漏把原本可用的金額覆蓋掉。

替代方案是直接將 `payment_amount` 寫成 0，但這會放大資料缺漏的破壞性，且不利於營運修單。

## Risks / Trade-offs

- [partner 變更是否也應視為影響費用] → 本次依需求只以機車與時間作為觸發條件，並在 spec 中明確寫出；若日後要把合作商納入，需另開 change。
- [時間格式正規化不一致導致誤判] → 前後端皆以統一格式比對，後端在比較前先走既有 datetime normalize 流程。
- [前端預覽與後端落庫結果不同步] → 後端保留最終決策權，前端儲存後一律以 API 回傳的完整訂單物件回寫 state。
- [重算失敗後使用者不清楚原因] → API 與前端需提供明確訊息，指出費率、機車或時間資料不足。

## Migration Plan

1. 更新 `order-amount-recalculation` spec，明確定義編輯訂單的 selective recalculation 規則。
2. 實作後端更新邏輯：比較編輯前後的 `scooter_ids`、`start_time`、`end_time`，決定是否重算。
3. 調整前端 `AddOrderModal` 的自動計算觸發條件與提交流程，避免無關欄位影響金額。
4. 手動驗證以下情境：只改備註不變價、改機車變價、改時間變價、機車順序不同但內容相同不變價、重算失敗時保留舊值。
5. 補充驗證新增訂單時，系統會依送出的機車與租借時間正常計算總金額。
6. 若需回滾，可先回復舊 spec 與前後端 change detection 邏輯，無資料庫 migration 需求。

## Open Questions

- `expected_return_time` 是否應被視為影響費用的時間欄位？目前依現有計算邏輯假設只有 `start_time` 與 `end_time` 影響金額。
- 若合作商 `partner_id` 被修改但機車與時間不變，是否仍應維持原金額？目前依需求假設不重算。
