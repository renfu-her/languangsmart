## Why

目前訂單總金額的重算規則不夠明確。新增訂單時，系統應依當下租借條件正常計算金額；但在編輯既有訂單時，若只是修改不影響租借費用的欄位，卻仍可能意外改價。相反地，當租借機車或租借時間真的變動時，又必須確保金額會同步更新。這次需要把規則明確收斂，避免新增與編輯流程出現金額不一致。

## What Changes

- 明確定義新增訂單時由後端依 `scooter_ids` 與租借時間計算 `payment_amount`。
- 明確定義編輯訂單時 `payment_amount` 的重算條件：只有 `scooter_ids` 或租借時間欄位改變時才重新計算。
- 若使用者僅修改不影響租借費用的欄位，例如備註、付款方式、狀態或聯絡資訊，系統必須保留原本的 `payment_amount`。
- 前後端需對齊相同的變更判斷規則，避免前端顯示金額與後端實際儲存結果不一致。
- 編輯流程需保留既有訂單金額作為 comparison baseline，用來判斷此次更新是否需要觸發重算。

## Capabilities

### New Capabilities
- None.

### Modified Capabilities
- `order-amount-recalculation`: 新增訂單時仍由後端計算總金額；編輯訂單時僅在機車或租借時間變動時觸發重算，其他欄位更新不得改動既有總金額。

## Impact

- `system/backend/components/AddOrderModal.tsx`：需調整編輯模式下的金額重算觸發條件與提交資料策略。
- `app/Http/Controllers/Api/OrderController.php`：需在更新訂單時依機車與時間是否變動決定是否重算 `payment_amount`。
- 既有訂單金額相關 spec 與編輯訂單流程測試案例需同步更新。
