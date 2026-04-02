## MODIFIED Requirements

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

---

## ADDED Requirements

### Requirement: 編輯模式金額欄位唯讀
在編輯訂單時，前端 `AddOrderModal` SHALL 將 `payment_amount` 欄位設為唯讀（`readOnly`），防止使用者手動修改金額，並顯示提示說明金額由系統自動計算。

#### Scenario: 編輯模式金額欄位不可修改
- **WHEN** 使用者開啟編輯訂單 modal（`editingOrder` 不為 null）
- **THEN** `payment_amount` input SHALL 呈現唯讀狀態（`readOnly` 屬性），套用灰底樣式，且下方 SHALL 顯示說明文字「金額由系統自動計算，編輯模式下不可修改」

#### Scenario: 新增模式金額欄位可輸入
- **WHEN** 使用者開啟新增訂單 modal（`editingOrder` 為 null）
- **THEN** `payment_amount` input SHALL 保持可編輯狀態，且自動計算邏輯正常運作

### Requirement: 機車管理列表顯示顏色欄位
`ScootersPage` 列表 SHALL 顯示「顏色」欄位，與新增/編輯 modal 表單的 `color` 欄位對應，確保列表與表單資訊一致。

#### Scenario: 列表顯示機車顏色
- **WHEN** 使用者進入機車管理頁面
- **THEN** 列表 SHALL 在「車款類型」欄之後顯示「顏色」欄，呈現 `scooter.color` 值

#### Scenario: 顏色為空時的顯示
- **WHEN** 機車的 `color` 欄位為 null 或空字串
- **THEN** 列表 SHALL 顯示「-」作為佔位符
