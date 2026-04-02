## Why

訂單管理介面存在三個 UX 問題：編輯訂單時金額欄位仍可手動修改（應由後端計算）、關閉 modal 後觸發 `window.location.reload()` 導致頁面閃爍且體驗不佳、以及機車管理列表欄位與新增/編輯 modal 表單欄位不一致，缺少顏色欄位的對應顯示。

## What Changes

- **訂單編輯 modal 金額欄位改為唯讀**：`AddOrderModal` 在 `editingOrder` 存在時，`payment_amount` input 設為 `readOnly` + 視覺樣式標示，移除手動修改邏輯
- **關閉 modal 改以 API 重新取回資料**：`OrdersPage` 的 `onClose` callback 移除 `window.location.reload()`，改呼叫 `ordersApi.list()` 重新取得最新列表，並更新 `orders` state
- **機車管理列表新增顏色欄**：`ScootersPage` 列表加入「顏色」欄位，與新增/編輯 modal 的 `color` 欄位對應，使列表呈現與表單一致

## Capabilities

### New Capabilities
- 無

### Modified Capabilities
- `order-amount-recalculation`: 前端金額欄位在編輯模式改為唯讀，確保不傳送手動修改的金額至後端；關閉 modal 後改用 API 重新取得資料取代 reload

## Impact

- `system/backend/components/AddOrderModal.tsx`：`payment_amount` input 在編輯模式加上 `readOnly`/`disabled`，移除 `setIsAmountManuallyEdited(true)` 的 onChange
- `system/backend/pages/OrdersPage.tsx`：`onClose` callback 移除 `setTimeout(() => window.location.reload(), 100)`，改呼叫 `fetchOrders()` 或內嵌 `ordersApi.list()` 更新 state
- `system/backend/pages/ScootersPage.tsx`：列表 `<thead>` 及 `<tbody>` 新增顏色欄位，確保與 modal 表單欄位一致
