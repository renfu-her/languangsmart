## MODIFIED Requirements

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
