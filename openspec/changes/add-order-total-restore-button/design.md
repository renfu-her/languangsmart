## Context

`AddOrderModal.tsx` 目前已經有 `calculateAmount()` 與 `isAmountManuallyEdited` 兩個核心機制：
- 當合作廠商、車輛與起訖時間完整，且 `isAmountManuallyEdited` 為 `false` 時，畫面會自動回填 `payment_amount`。
- 編輯既有訂單時，只要現有 `payment_amount` 不為 0，就會被視為已手動修改，後續不再自動重算。

這代表現有系統其實已經保留原始計算邏輯，但缺少一個明確的 UI 入口，讓使用者在編輯訂單時把總金額恢復成系統計算值。

## Goals / Non-Goals

**Goals:**
- 在編輯訂單 modal 的總金額欄位旁提供一個明確的恢復操作。
- 恢復操作必須重用現有 `calculateAmount()`，避免前後不一致的第二套公式。
- 使用者恢復後，表單應回到「可自動跟隨條件變化重算」的狀態。
- 若無法產生有效金額，畫面不得覆蓋目前手動輸入值。

**Non-Goals:**
- 不修改後端 `OrderController` 或 `OrderAmountCalculator` 的 API 與計算公式。
- 不新增資料表欄位或新的訂單金額來源。
- 不改動新增訂單流程的既有自動計算策略，僅補齊編輯訂單的恢復入口。

## Decisions

### 1. 按鈕只出現在編輯模式

新增訂單模式本來就會在未手動覆蓋時自動計算，因此這次按鈕只在 `editingOrder` 存在時顯示，避免在新增流程中增加重複入口與 UI 雜訊。

替代方案是新增與編輯都顯示同一個按鈕，但新增模式已具備持續自動回填，價值低，還會讓表單操作意圖變得不清楚。

### 2. 恢復按鈕直接重用 `calculateAmount()`，並把 `isAmountManuallyEdited` 設回 `false`

按鈕事件會先用目前表單資料呼叫 `calculateAmount()`。若結果大於 0，便把 `payment_amount` 更新為該值，並將 `isAmountManuallyEdited` 設為 `false`。這樣不只會回填當下金額，也會讓後續修改合作商、車輛或時間時重新進入既有的自動重算路徑。

替代方案是只覆寫一次 `payment_amount`、但保留 `isAmountManuallyEdited = true`。這樣雖能回填當下金額，卻不符合「恢復原有計算方式」的期待，因為之後條件再變動時仍不會自動更新。

### 3. 無效計算不覆蓋欄位，並提供使用者回饋

若必要條件不足，按鈕應顯示為 disabled；若條件完整但 `calculateAmount()` 仍算出 0，代表費率資料不足或當前條件無法得出有效總價，系統應保留目前 `payment_amount`，並以既有 alert 或相同層級提示告知使用者未能恢復。

替代方案是即使算出 0 也直接覆蓋欄位，但這會把原本可用的人工修正值洗掉，風險過高。

## Risks / Trade-offs

- [恢復後重新啟用自動計算，可能改變使用者對手動值鎖定的預期] → 按鈕文案需明確表達是「恢復原計算」，讓使用者知道之後條件變更會再次自動重算。
- [按鈕可用性條件與實際計算條件不一致] → 直接以 `calculateAmount()` 相依的必要欄位作為 enabled 條件，避免 UI 顯示可按但實際永遠失敗。
- [費率缺漏導致使用者不知道為何無法恢復] → 失敗提示需指出需檢查合作商費率、車輛或時間資料。

## Migration Plan

1. 在 `AddOrderModal.tsx` 為編輯模式的總金額欄位加入恢復按鈕與 disabled 條件。
2. 新增按鈕 click handler，重用 `calculateAmount()`，成功時同步更新 `payment_amount` 與 `isAmountManuallyEdited`。
3. 手動驗證編輯訂單流程：手改金額、點恢復、再改合作商或時間，確認總金額會重新跟隨計算。
4. 若需要回滾，只需移除按鈕與 handler，不涉及資料遷移。

## Open Questions

- 按鈕文案先以「恢復原計算」定義；若產品端有既定用語，可在實作時調整文案，不影響需求本體。
