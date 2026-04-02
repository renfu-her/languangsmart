## Why

目前新增或編輯訂單時，畫面上顯示的總金額與最終寫入資料庫的 `payment_amount` 可能不一致，導致列表頁、統計資料與 modal 內看到的金額對不上。這會讓營運人員無法信任系統顯示結果，也增加對帳與修單成本。

## What Changes

- 明確定義新增與編輯訂單時，提交當下表單中的 `payment_amount` 必須作為最終寫入資料庫的金額。
- 後端更新訂單時不得再以額外重算或保留舊值覆蓋使用者在表單中確認後提交的總金額。
- 前端新增/編輯 modal 與列表更新流程需對齊相同資料來源，確保儲存後列表金額與 modal 提交金額一致。
- 若系統仍需提供自動計算、恢復原計算或人工覆寫，這些操作都必須先反映在表單 `payment_amount`，再由後端原樣持久化。

## Capabilities

### New Capabilities
- None.

### Modified Capabilities
- `order-amount-recalculation`: 訂單新增與編輯時的總金額來源改為以表單最終提交的 `payment_amount` 為準，避免列表、統計與 modal 顯示不一致。

## Impact

- `app/Http/Controllers/Api/OrderController.php`：需調整 store/update 的 `payment_amount` 持久化規則。
- `system/backend/components/AddOrderModal.tsx`：需確保自動計算、恢復原計算與人工覆寫最終都落在表單 `payment_amount` 後再提交。
- `system/backend/pages/OrdersPage.tsx`：儲存後列表更新需持續使用後端回傳資料，避免局部 state 與 DB 不一致。
- `openspec/specs/order-amount-recalculation/spec.md` 與相關 change delta specs：需同步調整金額來源定義。
