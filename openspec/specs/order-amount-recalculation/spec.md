## ADDED Requirements

### Requirement: 後端管理 payment_amount 計算與人工覆寫
在建立訂單時，系統 SHALL 由後端 `calculateOrderAmount()` 計算 `payment_amount`。在更新訂單時，系統 SHALL 以前後端約定的變更規則決定是否重算或保留原金額；若使用者明確執行人工覆寫，系統 SHALL 採用前端提交的 `payment_amount` 作為最終金額。

#### Scenario: 建立訂單時後端計算金額
- **WHEN** 前端送出建立訂單請求（`POST /api/orders`）
- **THEN** 系統 SHALL 由後端 `calculateOrderAmount()` 計算 `payment_amount` 並存入 DB

#### Scenario: 編輯訂單時機車有變動則重算金額
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），且此次更新未標示為人工覆寫金額，並且 `scooter_ids` 與原訂單不同
- **THEN** 系統 SHALL 依新的機車組合與既有時間資料重新計算 `payment_amount`，並將計算結果存入 DB

#### Scenario: 編輯訂單時租借時間有變動則重算金額
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），且此次更新未標示為人工覆寫金額，並且 `start_time` 或 `end_time` 與原訂單不同
- **THEN** 系統 SHALL 依新的時間條件與既有機車組合重新計算 `payment_amount`，並將計算結果存入 DB

#### Scenario: 僅修改非費用欄位時保留原金額
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），且此次更新未標示為人工覆寫金額，並且 `scooter_ids`、`start_time`、`end_time` 皆與原訂單相同，但備註、付款方式、狀態或聯絡資訊有更新
- **THEN** 系統 SHALL 保留原有的 `payment_amount` 不變

#### Scenario: 更新訂單時人工覆寫金額
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），明確標示此次更新為人工覆寫總金額，且包含有效的 `payment_amount`
- **THEN** 系統 SHALL 採用前端提交的 `payment_amount` 作為最終金額

#### Scenario: 相同機車僅順序不同時不得誤判為變更
- **WHEN** 前端送出更新訂單請求（`PUT /api/orders/{id}`），且此次更新未標示為人工覆寫金額，`scooter_ids` 的集合與原訂單相同，只有陣列順序不同
- **THEN** 系統 SHALL 將其視為未變更，並保留原有的 `payment_amount`

#### Scenario: 費率資料不完整時的處理
- **WHEN** 計算訂單金額時，找不到對應車型的費率（`PartnerScooterModelTransferFee`）
- **THEN** 系統 SHALL 保留原有的 `payment_amount` 不覆蓋，並在 API 回應中包含警告訊息

---

### Requirement: 前端列表 state 與 DB 同步
訂單更新成功後，前端 React 列表 state SHALL 使用後端 API 回傳的完整訂單物件更新，確保列表顯示的 `payment_amount` 與 DB 一致。

#### Scenario: 編輯訂單後列表即時更新
- **WHEN** 使用者在編輯 modal 中儲存訂單
- **THEN** 後端回傳更新後的完整訂單物件，前端 SHALL 用此物件取代列表中對應的訂單 state，不需要 `window.location.reload()`

#### Scenario: 列表金額與統計金額一致
- **WHEN** 使用者儲存訂單後查看合作夥伴月統計
- **THEN** 統計金額 SHALL 反映最新的 `payment_amount`（不需重新整理頁面）

---

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
