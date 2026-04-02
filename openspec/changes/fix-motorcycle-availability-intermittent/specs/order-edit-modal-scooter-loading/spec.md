## ADDED Requirements

### Requirement: 編輯模式下機車選取區塊可靠初始化
當使用者開啟編輯訂單 modal 時，系統 SHALL 每次都能正確顯示訂單已關聯的機車為已選中狀態，不受上一次開啟的 stale state 影響。

#### Scenario: 開啟編輯 modal 時機車正確顯示
- **WHEN** 使用者點擊編輯訂單，modal 開啟且 `editingOrder.scooter_ids` 包含至少一個 ID
- **THEN** 系統 SHALL 在「租借機車選取」區塊顯示訂單已關聯的機車為已選中
- **THEN** 系統 SHALL 同時在可選列表中顯示狀態為「待出租」的其他機車

#### Scenario: 連續開啟不同訂單的編輯 modal
- **WHEN** 使用者先開啟訂單 A（關聯機車 ID=5），關閉後再開啟訂單 B（關聯機車 ID=7）
- **THEN** 系統 SHALL 顯示機車 ID=7 為已選中
- **THEN** 系統 SHALL NOT 顯示機車 ID=5 為已選中或出現在選取區塊中

#### Scenario: scooter_ids 為空時不顯示選中機車
- **WHEN** 使用者開啟一筆訂單，且 `editingOrder.scooter_ids` 為空陣列
- **THEN** 系統 SHALL 顯示「目前尚未選擇任何機車」
- **THEN** 系統 SHALL 仍然載入並顯示狀態為「待出租」的可選機車列表

### Requirement: scooter_ids fallback 機制
當 `editingOrder.scooter_ids` 未提供或為空時，系統 SHALL fallback 嘗試從 `editingOrder.scooters` 陣列取得機車 ID。

#### Scenario: scooter_ids 空但 scooters 有資料時的 fallback
- **WHEN** `editingOrder.scooter_ids` 為空陣列，但 `editingOrder.scooters` 包含機車資料
- **THEN** 系統 SHALL 使用 `editingOrder.scooters` 中的 ID 作為已選機車 ID
- **THEN** 系統 SHALL 正確顯示這些機車為已選中狀態

### Requirement: 機車載入不受 race condition 影響
系統 SHALL 以確定的順序合併「可租借機車列表」與「訂單已關聯機車」，確保結果不因網路請求完成順序不同而有差異。

#### Scenario: 可租借列表與訂單機車合併結果一致
- **WHEN** 系統同時向後端請求可租借機車列表與訂單機車資料
- **THEN** 合併後的 `availableScooters` SHALL 包含所有「待出租」機車，以及訂單已關聯的機車（不論其狀態）
- **THEN** 結果 SHALL 不因兩個 API 回應的先後順序不同而改變
