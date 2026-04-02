## 1. 編輯訂單總金額恢復按鈕

- [x] 1.1 更新 `system/backend/components/AddOrderModal.tsx` 的總金額欄位 UI，只在編輯模式顯示「恢復原計算」按鈕，並依合作廠商、車輛、開始時間、結束時間是否完整控制 disabled 狀態
- [x] 1.2 在 `AddOrderModal.tsx` 實作恢復按鈕 handler，重用既有 `calculateAmount()`，成功時回填 `payment_amount` 並將 `isAmountManuallyEdited` 切回 `false`
- [x] 1.3 補上失敗處理：當計算結果無效時保留目前 `payment_amount`，並提示使用者檢查費率或訂單條件

## 2. 驗證恢復行為

- [ ] 2.1 手動驗證編輯訂單流程，確認手動修改總金額後可用按鈕恢復為系統計算值
- [ ] 2.2 驗證恢復成功後再修改合作廠商、車輛或時間時，總金額會重新依既有計算邏輯更新
- [ ] 2.3 驗證條件不足或費率缺漏時，恢復按鈕不會錯誤覆蓋現有總金額
