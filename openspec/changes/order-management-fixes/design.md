## Context

後台訂單管理系統使用 `AddOrderModal` 元件處理新增與編輯訂單。目前問題有三：

1. **金額欄位可手動修改**：`AddOrderModal` 的 `payment_amount` input 在編輯模式下未鎖定，使用者可任意改值，但後端 `calculateOrderAmount()` 會忽略此值重算，造成前端顯示金額與後端儲存值不一致的混淆
2. **關閉 modal 觸發 `window.location.reload()`**：`OrdersPage` `onClose` callback 使用 `setTimeout(() => window.location.reload(), 100)` 強制重整頁面，導致頁面閃爍、捲軸位置跳回頂部、使用者體驗不佳
3. **ScootersPage 列表缺少顏色欄**：`ScootersPage` 的 modal 表單有 `color` 欄位，但列表只顯示「機車照片、車牌號碼、機車型號、車款類型、所屬商店、狀態」，缺少「顏色」欄，資訊不對稱

## Goals / Non-Goals

**Goals:**
- 編輯訂單時 `payment_amount` 欄位改為唯讀並有視覺提示（灰底 + 說明文字）
- 關閉 `AddOrderModal` 後以 `ordersApi.list()` 重新拉取資料更新 state，不 reload 頁面
- `ScootersPage` 列表新增「顏色」欄，與 modal 表單對應

**Non-Goals:**
- 不修改後端 API 邏輯
- 不調整 `payment_method` 或其他欄位的行為
- 不重構 ScootersPage modal 的其他欄位

## Decisions

### 1. payment_amount 在編輯模式改為唯讀

**決定**：在 `AddOrderModal` 中，當 `editingOrder` 不為 null 時，對 `payment_amount` input 加上 `readOnly` 屬性，並套用灰底樣式（`bg-gray-100 cursor-not-allowed opacity-70`），移除 `onChange` 的 `setIsAmountManuallyEdited(true)` 呼叫。同時在欄位下方加上提示文字「金額由系統自動計算，編輯模式下不可修改」。

**替代方案考慮**：
- 完全移除 `payment_amount` 欄位（不顯示）→ 否決，使用者需要看到目前金額以確認
- 保留可編輯但送出時丟棄前端值 → 現況即如此但造成混淆，不如直接視覺鎖定

### 2. 關閉 modal 後以 API 重新取得資料

**決定**：`OrdersPage` 的 `onClose` callback 移除 `window.location.reload()`，改呼叫現有的 `fetchOrders()` function（或等效的 `ordersApi.list()` + `setOrders()`），確保資料從後端取回。`handleOrderSaved` 已有即時更新 state 的邏輯，`onClose` 只需額外 re-fetch 以處理「取消」後的邊界情況（如外部資料已變動）。

**替代方案考慮**：
- 維持 `window.location.reload()` → 頁面閃爍、體驗差，排除
- 只依賴 `handleOrderSaved` 的 optimistic update → 取消時無法同步伺服器最新狀態

### 3. ScootersPage 列表顏色欄

**決定**：在 `<thead>` 新增「顏色」欄，在 `<tbody>` 對應顯示 `scooter.color`（空值顯示「-」），欄位插入位置放在「車款類型」之後，與 modal 表單欄位順序一致。使用 `modelColorMap` 或直接取 `scooter.color` 顯示。

## Risks / Trade-offs

- **[Risk] re-fetch 造成短暫 loading 閃爍** → Mitigation：可保留現有 `handleOrderSaved` 的即時 state 更新，`onClose` 的 re-fetch 僅在「取消」時觸發，或改為靜默背景更新不顯示 loading
- **[Risk] ScootersPage 表格欄位增多導致排版過窄** → Mitigation：「顏色」欄內容短（通常 2-4 字），影響有限；若需要可在小螢幕 hidden

## Migration Plan

1. 修改 `AddOrderModal.tsx`：`payment_amount` input 加 `readOnly`/樣式條件
2. 修改 `OrdersPage.tsx`：`onClose` 移除 `window.location.reload()`，加入 re-fetch
3. 修改 `ScootersPage.tsx`：列表新增顏色欄
4. 手動測試：新增訂單 → 確認金額可輸入；編輯訂單 → 金額唯讀；關閉 modal → 資料更新不閃爍；ScootersPage 列表顏色正常顯示

**Rollback**：所有改動都在前端，直接 revert commit 即可。
