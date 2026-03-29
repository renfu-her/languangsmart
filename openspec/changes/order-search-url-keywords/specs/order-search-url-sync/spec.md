## ADDED Requirements

### Requirement: Search term initializes from URL on page load
訂單管理頁面 SHALL 在初始化時從 URL query string 的 `keywords` 參數讀取搜尋文字，並顯示於搜尋框中，同時以此條件發出 API 請求。

#### Scenario: Page loaded with keywords in URL
- **WHEN** 使用者直接開啟 `/backend/orders?keywords=ABC123`
- **THEN** 搜尋框顯示「ABC123」，頁面載入後顯示符合此關鍵字的訂單列表

#### Scenario: Page loaded without keywords in URL
- **WHEN** 使用者開啟 `/backend/orders`（無 keywords 參數）
- **THEN** 搜尋框為空，顯示當月所有訂單（現有行為不變）

### Requirement: Typing in search box updates URL in real time
使用者在搜尋框輸入文字時，URL SHALL 即時更新為 `?keywords=<輸入的文字>`，且使用 replace 模式（不新增瀏覽器歷史記錄）。

#### Scenario: User types in search box
- **WHEN** 使用者在搜尋框輸入「王小明」
- **THEN** 瀏覽器 URL 更新為 `/backend/orders?keywords=王小明`，不產生新的歷史記錄（replace）

#### Scenario: User clears search box
- **WHEN** 使用者清空搜尋框（輸入為空字串）
- **THEN** URL 更新為 `/backend/orders`（移除 keywords 參數），顯示全部訂單

### Requirement: Page reload restores search state
頁面重新整理後，系統 SHALL 自動還原搜尋條件，顯示與重整前相同的搜尋結果。

#### Scenario: User reloads page with active search
- **WHEN** URL 為 `/backend/orders?keywords=0912345678` 時使用者按下 F5 重新整理
- **THEN** 頁面載入後搜尋框仍顯示「0912345678」，並自動顯示符合的訂單

### Requirement: Backend accepts keywords parameter
`GET /api/orders` 端點 SHALL 接受 `keywords` 查詢參數，搜尋範圍涵蓋：承租人姓名、電話、訂單號碼、車牌號碼（與現有 `search` 參數相同邏輯）。

#### Scenario: API called with keywords parameter
- **WHEN** 前端發出 `GET /api/orders?keywords=ABC&month=2025-03`
- **THEN** 後端回傳承租人、電話、訂單號或車牌號碼符合「ABC」的訂單

#### Scenario: API called without keywords parameter
- **WHEN** 前端發出 `GET /api/orders?month=2025-03`（無 keywords）
- **THEN** 後端回傳當月所有訂單（現有行為不變）

#### Scenario: API called with both keywords and search parameters
- **WHEN** 前端發出同時包含 `keywords` 和 `search` 的請求
- **THEN** 後端優先使用 `keywords` 參數進行搜尋
