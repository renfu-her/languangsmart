## 1. AddOrderModal — 編輯模式金額唯讀

- [x] 1.1 在 `AddOrderModal.tsx` 的 `payment_amount` input，根據 `editingOrder` 是否存在，加上 `readOnly` 屬性與灰底樣式（`bg-gray-100 dark:bg-gray-700 cursor-not-allowed opacity-70`）
- [x] 1.2 移除 `onChange` 中的 `setIsAmountManuallyEdited(true)` 呼叫（編輯模式下不允許手動修改）
- [x] 1.3 在金額欄位下方加上提示文字，條件為 `editingOrder` 存在時顯示「金額由系統自動計算，編輯模式下不可修改」

## 2. OrdersPage — 關閉 modal 改以 API 重新取得資料

- [x] 2.1 在 `OrdersPage.tsx` 確認現有 `fetchOrders` function（或等效的 `ordersApi.list()` + `setOrders()` 邏輯）可獨立呼叫
- [x] 2.2 移除 `AddOrderModal` 的 `onClose` callback 中的 `setTimeout(() => window.location.reload(), 100)`
- [x] 2.3 在 `onClose` callback 改呼叫 `fetchOrders()`（或等效函式）重新取得最新訂單列表並更新 state

## 3. ScootersPage — 列表新增顏色欄

- [x] 3.1 在 `ScootersPage.tsx` 的列表 `<thead>` 中，於「車款類型」欄之後新增「顏色」欄 `<th>`
- [x] 3.2 在 `<tbody>` 的每列 `<tr>` 中，對應位置新增 `<td>` 顯示 `scooter.color`，空值顯示「-」

## 4. 驗證

- [x] 4.1 測試新增訂單：金額欄位可輸入，自動計算正常
- [x] 4.2 測試編輯訂單：金額欄位呈現唯讀灰底，無法輸入，提示文字顯示
- [x] 4.3 測試關閉 modal（儲存與取消）：頁面不閃爍，列表資料正確更新
- [x] 4.4 測試 ScootersPage 列表：顏色欄正常顯示，空值顯示「-」
