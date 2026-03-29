## 1. 後端：支援 keywords 參數

- [x] 1.1 修改 `app/Http/Controllers/Api/OrderController.php` 的 `index()` 方法：在現有 `search` 參數判斷（第 45 行附近）前加入 `keywords` 參數判斷，優先使用 `keywords`，fallback 至 `search`

## 2. 前端：搜尋框與 URL 雙向同步

- [x] 2.1 在 `system/backend/pages/OrdersPage.tsx` 中，import `useSearchParams` from `react-router-dom`
- [x] 2.2 在 `OrdersPage` 元件中加入 `const [searchParams, setSearchParams] = useSearchParams()`
- [x] 2.3 將 `searchTerm` 的 `useState` 初始值從 `''` 改為 `searchParams.get('keywords') ?? ''`
- [x] 2.4 修改搜尋框的 `onChange` handler：在 `setSearchTerm` 和 `setCurrentPage(1)` 之後，加入 `setSearchParams(prev => { if (e.target.value) { prev.set('keywords', e.target.value) } else { prev.delete('keywords') } return prev }, { replace: true })`
- [x] 2.5 修改 `ordersApi.list(...)` 呼叫：將 `search: searchTerm || undefined` 改為 `keywords: searchTerm || undefined`（對應後端新參數名稱）

## 3. 驗證

- [x] 3.1 手動測試：在搜尋框輸入文字，確認 URL 即時變化為 `?keywords=...`
- [x] 3.2 手動測試：重新整理頁面，確認搜尋框保留文字且訂單列表正確
- [x] 3.3 手動測試：清空搜尋框，確認 URL 移除 keywords 參數
- [x] 3.4 手動測試：複製含 keywords 的 URL 並在新分頁開啟，確認搜尋結果正確
