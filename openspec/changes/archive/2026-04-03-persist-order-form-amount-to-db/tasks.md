## 1. 後端金額持久化規則

- [x] 1.1 調整 `app/Http/Controllers/Api/OrderController.php` 的 store 流程：若 payload 已有有效 `payment_amount` 則直接採用，否則才 fallback 到後端計算
- [x] 1.2 調整 `app/Http/Controllers/Api/OrderController.php` 的 update 流程：以提交的 `payment_amount` 作為最終寫入值，不再額外覆蓋
- [x] 1.3 建立或更新失敗時，回傳明確錯誤，避免寫入與表單顯示不同的金額

## 2. 前端表單與列表一致性

- [x] 2.1 檢查 `system/backend/components/AddOrderModal.tsx`，確保自動計算、恢復原計算與人工覆寫最終都更新到同一個 `payment_amount` 欄位後再提交
- [x] 2.2 確認新增與編輯儲存後，`system/backend/pages/OrdersPage.tsx` 持續使用後端回傳的完整訂單物件更新列表 state
- [x] 2.3 補充必要提示，讓使用者清楚知道提交時會以目前表單中的總金額為準

## 3. 驗證與回歸測試

- [ ] 3.1 驗證新增訂單時，modal 顯示金額與 DB 寫入金額一致
- [ ] 3.2 驗證編輯訂單時，modal 提交金額與列表更新後顯示金額一致
- [ ] 3.3 驗證手動覆寫總金額後儲存，DB 與列表皆使用手動輸入值
- [ ] 3.4 驗證恢復原計算後儲存，DB 與列表皆使用恢復後的計算值
