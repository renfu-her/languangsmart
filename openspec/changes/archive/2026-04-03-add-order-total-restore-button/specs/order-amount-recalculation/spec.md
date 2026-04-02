## ADDED Requirements

### Requirement: 編輯訂單可恢復原始總金額計算
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
