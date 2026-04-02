## MODIFIED Requirements

### Requirement: 後端強制重新計算 payment_amount
在建立訂單時，系統 SHALL 由後端 `calculateOrderAmount()` 計算 `payment_amount`。在更新既有訂單時，系統 SHALL 僅在 `scooter_ids`、`start_time`、`end_time` 任一影響租借費用的欄位變動時重新計算 `payment_amount`；若這些欄位都未變動，系統 SHALL 保留原有的 `payment_amount`，不得因其他欄位更新而改價。若使用者明確以人工方式覆寫總金額，系統 SHALL 採用此次人工輸入值。

#### Scenario: 建立訂單時後端計算金額
- **WHEN** 前端送出建立訂單請求（`POST /api/orders`）
- **THEN** 系統 SHALL 由後端 `calculateOrderAmount()` 計算 `payment_amount` 並存入 DB

#### Scenario: 編輯訂單時機車有變動則重算金額
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），且 `scooter_ids` 與原訂單不同
- **THEN** 系統 SHALL 依新的機車組合與既有時間資料重新計算 `payment_amount`，並將計算結果存入 DB

#### Scenario: 編輯訂單時租借時間有變動則重算金額
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），且 `start_time` 或 `end_time` 與原訂單不同
- **THEN** 系統 SHALL 依新的時間條件與既有機車組合重新計算 `payment_amount`，並將計算結果存入 DB

#### Scenario: 僅修改非費用欄位時保留原金額
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），且 `scooter_ids`、`start_time`、`end_time` 皆與原訂單相同，但備註、付款方式、狀態或聯絡資訊有更新
- **THEN** 系統 SHALL 保留原有的 `payment_amount` 不變

#### Scenario: 編輯訂單時人工覆寫總金額
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），並明確標示此次修改為人工覆寫 `payment_amount`
- **THEN** 系統 SHALL 採用前端提交的 `payment_amount`，不再套用此次更新的自動重算結果

#### Scenario: 相同機車僅順序不同時不得誤判為變更
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），`scooter_ids` 的集合與原訂單相同，只有陣列順序不同
- **THEN** 系統 SHALL 將其視為未變更，並保留原有的 `payment_amount`

#### Scenario: 需要重算但費率資料不完整時保留舊值
- **WHEN** 系統判定此次更新需重新計算 `payment_amount`，但找不到對應車型或時間條件的有效費率
- **THEN** 系統 SHALL 保留原有的 `payment_amount` 不覆蓋，並在 API 回應中包含警告訊息
