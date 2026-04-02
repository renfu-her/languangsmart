## Why

訂單管理頁面的搜尋狀態目前僅存於 React state，頁面重新整理後搜尋條件會消失，使用者必須重新輸入。將搜尋關鍵字同步至 URL query string（`?keywords=...`），可讓頁面在 reload、分享連結、瀏覽器上一頁/下一頁時，自動還原搜尋狀態。

## What Changes

- 前端 `OrdersPage.tsx`：`searchTerm` state 與 URL `?keywords=` 雙向同步
  - 初始化時從 URL 讀取 `keywords` 參數作為初始搜尋值
  - 使用者輸入時同步更新 URL（使用 `useSearchParams` 或手動 `navigate`）
  - 清空搜尋時移除 URL 參數
- 後端 `OrderController.php`：新增接受 `keywords` 參數作為搜尋條件的支援（與現有 `search` 參數並行，或統一改為 `keywords`）
- URL 結構：`/orders?keywords=ABC123`，同時保持其他篩選條件（年月、門市）可共存

## Capabilities

### New Capabilities
- `order-search-url-sync`: 訂單搜尋關鍵字與 URL query string 的雙向同步機制

### Modified Capabilities

## Impact

- **system/backend/pages/OrdersPage.tsx**: 搜尋 state 初始化邏輯、input onChange handler、useEffect dependencies
- **app/Http/Controllers/Api/OrderController.php**: 接受 `keywords` 參數（Lines 45-53 的搜尋邏輯）
- React Router `useSearchParams` 或 `useNavigate` 的使用方式
