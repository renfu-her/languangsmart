## 1. 後端 selective recalculation

- [x] 1.0 確認建立訂單流程仍由後端依 `scooter_ids`、`start_time`、`end_time` 正常計算 `payment_amount`
- [x] 1.1 在 `app/Http/Controllers/Api/OrderController.php` 取得原始訂單的 `scooter_ids`、`start_time`、`end_time`，並建立正規化後的 baseline 比較邏輯
- [x] 1.2 修改更新訂單流程：只有在 `scooter_ids`、`start_time`、`end_time` 任一欄位變動時才重新計算 `payment_amount`
- [x] 1.3 補上重算失敗保留舊值與警告訊息的處理，避免有效金額被覆蓋成 0 或空值

## 2. 前端編輯表單對齊規則

- [x] 2.1 調整 `system/backend/components/AddOrderModal.tsx` 的編輯模式金額更新條件，只在機車或時間變動時更新金額預覽
- [x] 2.2 確保只修改備註、付款方式、狀態、聯絡資訊等非費用欄位時，不會改動表單中的 `payment_amount`
- [x] 2.3 儲存成功後一律以後端回傳的完整訂單資料覆蓋前端 state，確保畫面金額與 DB 一致

## 3. 驗證與回歸測試

- [ ] 3.0 驗證新增訂單時會依租借機車與時間正常計算總金額
- [ ] 3.1 驗證編輯訂單時變更機車會重新計算總金額
- [ ] 3.2 驗證編輯訂單時變更 `start_time` 或 `end_time` 會重新計算總金額
- [ ] 3.3 驗證僅修改非費用欄位時總金額維持不變，且相同機車僅順序不同不會誤觸發重算
