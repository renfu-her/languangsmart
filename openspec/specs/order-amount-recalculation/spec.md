## ADDED Requirements

### Requirement: 後端管理 payment_amount 計算與人工覆寫
在建立訂單時，系統 SHALL 由後端 `calculateOrderAmount()` 計算 `payment_amount` 作為缺值時的 fallback；若前端提交了有效的 `payment_amount`，系統 SHALL 直接採用該值並存入 DB。在更新訂單時，系統 SHALL 以表單最終提交的 `payment_amount` 作為唯一寫入資料庫的金額，不得再以機車、時間、舊值或其他後端規則覆蓋。若使用者明確執行人工覆寫，系統 SHALL 採用前端提交的 `payment_amount` 作為最終金額。

#### Scenario: 建立訂單時前端已提供有效金額
- **WHEN** 前端送出建立訂單請求（`POST /api/orders`），且 payload 已包含有效的 `payment_amount`
- **THEN** 系統 SHALL 直接採用該 `payment_amount` 並存入 DB

#### Scenario: 建立訂單時缺少金額則後端補算
- **WHEN** 前端送出建立訂單請求（`POST /api/orders`），但 payload 未提供有效的 `payment_amount`
- **THEN** 系統 SHALL 由後端 `calculateOrderAmount()` 計算 `payment_amount` 並存入 DB

#### Scenario: 編輯訂單時以表單提交金額為準
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），且 payload 已包含有效的 `payment_amount`
- **THEN** 系統 SHALL 直接採用該 `payment_amount` 並存入 DB，不得再以機車、時間或舊值覆蓋

#### Scenario: 編輯訂單時人工覆寫金額
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），明確標示此次更新為人工覆寫總金額，且包含有效的 `payment_amount`
- **THEN** 系統 SHALL 採用前端提交的 `payment_amount` 作為最終金額

#### Scenario: 列表與 modal 金額保持一致
- **WHEN** 使用者在新增或編輯 modal 中儲存訂單，且提交的 `payment_amount` 為 X
- **THEN** 後端回傳的訂單物件、列表頁顯示與資料庫中的 `payment_amount` SHALL 皆為 X

#### Scenario: 費率資料不完整時的處理
- **WHEN** 建立訂單時未提供有效 `payment_amount`，且系統也找不到對應車型的費率可計算金額
- **THEN** 系統 SHALL 拒絕此次建立請求或回傳明確錯誤，不得默默寫入與畫面不一致的金額

### Requirement: 前端列表 state 與 DB 同步
訂單更新成功後，前端 React 列表 state SHALL 使用後端 API 回傳的完整訂單物件更新，確保列表顯示的 `payment_amount` 與 DB 一致。關閉 modal 後 SHALL 重新呼叫 `ordersApi.list()` 取得最新資料，不得使用 `window.location.reload()`。

#### Scenario: 編輯訂單後列表即時更新
- **WHEN** 使用者在編輯 modal 中儲存訂單
- **THEN** 後端回傳更新後的完整訂單物件，前端 SHALL 用此物件取代列表中對應的訂單 state，不需要 `window.location.reload()`

#### Scenario: 關閉 modal 後重新取得資料
- **WHEN** 使用者關閉 AddOrderModal（無論儲存或取消）
- **THEN** 前端 SHALL 呼叫 `ordersApi.list()` 重新取回最新訂單列表並更新 state，不得觸發 `window.location.reload()`

#### Scenario: 列表金額與統計金額一致
- **WHEN** 使用者儲存訂單後查看合作夥伴月統計
- **THEN** 統計金額 SHALL 反映最新的 `payment_amount`（不需重新整理頁面）

### Requirement: 編輯模式可恢復原始總金額計算
系統 SHALL 在編輯訂單 modal 的總金額欄位旁提供一個明確的恢復操作，讓使用者可使用既有的訂單金額計算邏輯重新取得 `payment_amount`，而不必手動重算輸入。

#### Scenario: 編輯模式顯示恢復按鈕
- **WHEN** 使用者開啟既有訂單的編輯 modal
- **THEN** 系統 SHALL 在 `payment_amount` 欄位旁顯示恢復原始計算的按鈕，且不得移除原本手動輸入總金額的能力

#### Scenario: 恢復後重新套用自動計算
- **WHEN** 使用者在編輯 modal 中點擊恢復按鈕，且合作廠商、車輛、開始時間與結束時間足以算出有效金額
- **THEN** 系統 SHALL 以既有計算邏輯覆寫 `payment_amount`，並 SHALL 將表單狀態切回可隨條件變動自動重算的模式

#### Scenario: 條件不足時不得覆蓋手動金額
- **WHEN** 使用者尚未選滿恢復所需的合作廠商、車輛、開始時間或結束時間
- **THEN** 系統 SHALL 將恢復按鈕維持為不可用，且 SHALL 保留目前 `payment_amount` 欄位值不變

#### Scenario: 無法得出有效金額時保留現值
- **WHEN** 使用者點擊恢復按鈕，但既有計算邏輯回傳 0 或其他無效金額
- **THEN** 系統 SHALL 不覆蓋目前的 `payment_amount`，並 SHALL 提示使用者檢查費率資料或訂單條件

### Requirement: 編輯模式可手動覆寫金額
在編輯訂單時，前端 `AddOrderModal` SHALL 保留 `payment_amount` 欄位的手動輸入能力，並顯示提示說明使用者可人工覆寫金額或恢復系統計算。

#### Scenario: 編輯模式金額欄位可修改
- **WHEN** 使用者開啟編輯訂單 modal（`editingOrder` 不為 null）
- **THEN** `payment_amount` input SHALL 保持可編輯狀態，且下方 SHALL 顯示說明文字，指出使用者可手動覆寫或恢復系統計算

#### Scenario: 新增模式金額欄位可輸入
- **WHEN** 使用者開啟新增訂單 modal（`editingOrder` 為 null）
- **THEN** `payment_amount` input SHALL 保持可編輯狀態，且自動計算邏輯正常運作

### Requirement: 歷史資料修正 Artisan Command
系統 SHALL 提供 `php artisan orders:fix-amounts` command，掃描並修正 DB 中 `payment_amount` 與計算值不符的訂單。

#### Scenario: Dry-run 模式輸出差異
- **WHEN** 執行 `php artisan orders:fix-amounts`（不加 `--apply`）
- **THEN** 系統 SHALL 輸出所有差異訂單的 ID、目前金額、計算金額，但 SHALL NOT 修改任何資料

#### Scenario: Apply 模式修正資料
- **WHEN** 執行 `php artisan orders:fix-amounts --apply`
- **THEN** 系統 SHALL 將所有差異訂單的 `payment_amount` 更新為計算值，並輸出修正筆數

#### Scenario: 計算結果為 0 時跳過
- **WHEN** 某訂單的 `calculateOrderAmount()` 計算結果為 0（費率資料缺失）
- **THEN** 系統 SHALL 跳過該訂單並輸出警告，不修改其 `payment_amount`
