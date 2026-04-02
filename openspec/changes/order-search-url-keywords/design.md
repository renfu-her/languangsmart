## Context

`OrdersPage.tsx` 目前用 `useState('')` 管理搜尋關鍵字，API 呼叫時透過 `search: searchTerm` 傳給後端（`OrderController@index` 第 45-53 行）。React Router 已使用 `BrowserRouter`（basename `/backend`），`useNavigate` 和 `useLocation` 已在 OrdersPage 中 import。後端 API 已支援 `search` 參數搜尋承租人、電話、訂單號及車牌號碼。

## Goals / Non-Goals

**Goals:**
- 搜尋關鍵字與 URL `?keywords=` 雙向同步
- 頁面 reload 後自動還原搜尋狀態
- 複製 URL 分享後，對方開啟即看到同樣搜尋結果
- 清空搜尋時移除 URL 中的 `keywords` 參數（保持 URL 乾淨）
- 後端接受 `keywords` 參數（與現有 `search` 相容，或統一改名）

**Non-Goals:**
- 將其他篩選條件（年月、門市）也同步至 URL
- 搜尋歷史記錄
- Debounce 優化（現有行為維持不變）

## Decisions

### 1. 使用 `useSearchParams`（React Router v6）而非手動 `navigate`

**決策**：改用 React Router 的 `useSearchParams` hook 取代現有的 `useNavigate` + `useLocation`（後者主要用於處理 `/bookings?detail=` 跳轉，保留不動）。

**原因**：`useSearchParams` 是 React Router v6 的標準方式，setter 自動處理 URL 更新，getter 自動解析 query string，且不影響路由本身的 pathname。

**替代方案**：手動用 `navigate({ search: '?keywords=...' })` — 可行，但需自行處理 URLSearchParams 序列化，較繁瑣。

### 2. 初始化 searchTerm 從 URL 讀取

**決策**：`useState` 的初始值改為 `searchParams.get('keywords') ?? ''`，而非固定 `''`。

**原因**：確保直接開啟含 keywords 的 URL 時，搜尋框顯示正確文字，且 API 請求立即以正確條件發出。

### 3. 後端參數名稱：新增 `keywords` 並保留 `search` 相容

**決策**：`OrderController@index` 中新增判斷 `keywords` 參數，優先使用 `keywords`，若無則 fallback 至 `search`（向後相容）。

**原因**：其他頁面或 API 客戶端可能仍使用 `search` 參數，不破壞現有行為。

### 4. URL 更新時機：onChange 即時更新（不 debounce URL）

**決策**：使用者每次 keystroke 都同步更新 URL，與現有的即時搜尋行為一致。

**原因**：避免引入額外的 debounce 邏輯複雜度。現有 API 呼叫已透過 `useEffect` 依賴 `searchTerm`，行為不變。

## Risks / Trade-offs

- **瀏覽器歷史堆疊**：每次鍵入都 push 新的歷史記錄，導致「上一頁」需多次點擊才能離開訂單頁
  → 使用 `setSearchParams(..., { replace: true })` 取代 push，避免歷史堆疊膨脹

- **與其他 URL 參數共存**：若其他地方也用 `navigate` 帶 query string，可能互相覆蓋
  → 使用 `setSearchParams` 的 functional updater 形式：`setSearchParams(prev => { prev.set('keywords', val); return prev; })` 確保不覆蓋其他參數

## Migration Plan

1. 修改前端 `OrdersPage.tsx`（約 3 處改動）
2. 修改後端 `OrderController.php`（加入 keywords 參數支援）
3. 無需資料庫 migration，無 breaking change
