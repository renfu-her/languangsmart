## Why

訂單列表中的 `payment_amount` 欄位可能與實際依車型/天數計算出的金額不同步，導致列表顯示錯誤金額（如：顯示 $600 而非正確的 $3,600），進而影響合作夥伴月統計數字。Excel 匯出因為重新計算詳細費用而顯示正確值，但 API 回傳的 `payment_amount` 是直接讀取資料庫欄位，造成兩者不一致。

## What Changes

- **修正 `payment_amount` 計算邏輯**：在 `OrderController` 的 `index()`（列表）和 `statistics()` 中，確保回傳的 `payment_amount` 與實際費用結構一致。
- **同步計算來源**：統一 `payment_amount` 的計算方式，對齊 `PartnerMonthlyReportExport` 使用的 `same_day_amount` + `overnight_amount` 計算邏輯。
- **修正現有資料**：提供一次性資料修正機制，將歷史資料中 `payment_amount` 與實際計算值不符的訂單更新正確。
- **加強資料一致性**：在訂單儲存時（建立/更新）自動重新計算並同步 `payment_amount`。

## Capabilities

### New Capabilities

- `order-amount-recalculation`: 訂單金額自動重新計算與同步機制，確保 `payment_amount` 永遠等於依車型與天數計算的實際費用總和。

### Modified Capabilities

（無 spec-level 行為變更，為 bug fix）

## Impact

- `app/Http/Controllers/Api/OrderController.php`：`index()`、`store()`、`update()`、`statistics()`
- `app/Http/Resources/OrderResource.php`：可能需調整 `payment_amount` 的計算方式
- `app/Models/Order.php`：可加入 `recalculateAmount()` 方法
- `database/migrations/`：不需要 schema 變更
- 合作夥伴月統計 API（`/api/orders/statistics`）：統計數字將自動正確
