## Why

編輯租借訂單時，「租借機車選取」區塊有時顯示已選機車、有時顯示空白（「目前尚未選擇任何機車」），行為不一致。根源在於前端載入機車列表的邏輯有 race condition，加上 `scooter_ids` 在部分 API 路徑下可能為空陣列，導致 `fetchScootersByIds` 完全不被呼叫。

## What Changes

- **修正前端 `AddOrderModal`**：
  - `fetchAvailableScooters` 與 `fetchScootersByIds` 目前同時並發執行且都修改同一個 state，導致結果取決於哪個先完成（race condition）。改為依序執行，確保順序一致。
  - 當 `editingOrder.scooter_ids` 為空但 `editingOrder.scooters` 有資料時，應 fallback 從 `scooters` 陣列取得 ID，避免因 `scooter_ids` 未回傳而跳過整個載入流程。
- **後端確認**：確認所有會回傳訂單的 API endpoint（list、show、update、delete）都有 eager load `scooters` 關係，確保 `scooter_ids` 永遠不為空陣列（除非訂單確實沒有機車）。

## Capabilities

### New Capabilities
<!-- 無新功能 -->

### Modified Capabilities
- `order-edit-modal-scooter-loading`: 編輯訂單 modal 中，機車選取區塊的初始化載入邏輯。需確保在各種 timing 下都能正確顯示並選中訂單已關聯的機車。

## Impact

- `system/backend/components/AddOrderModal.tsx`：`fetchAvailableScooters`、`fetchScootersByIds`、useEffect 依賴與執行順序
- `app/Http/Controllers/Api/OrderController.php`：確認 `show` endpoint 是否有 eager load `scooters`
- `app/Http/Resources/OrderResource.php`：`scooter_ids` 的 fallback 行為
