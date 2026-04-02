## 1. 前端 AddOrderModal 修正

- [x] 1.1 在 Effect 2（`[fixedStoreId, isOpen, editingOrder]`）開頭，先 reset `availableScooters([])` 與 `setSelectedScooterIds([])`，確保每次開啟都是乾淨狀態
- [x] 1.2 將 `fetchAvailableScooters` 與 `fetchScootersByIds` 改為依序執行：先 await `fetchAvailableScooters`，再 await `fetchScootersByIds`，最後做一次性 merge 並 set state
- [x] 1.3 加入 `scooter_ids` fallback：`const orderScooterIds = editingOrder?.scooter_ids?.length ? editingOrder.scooter_ids : (editingOrder?.scooters?.map((s: any) => s.id) ?? [])`
- [x] 1.4 將合併邏輯集中：`fetchScootersByIds` 回傳結果後，用 Set 去重合併 available scooters 與 order scooters，一次性呼叫 `setAvailableScooters`

## 2. 驗證

- [x] 2.1 測試：連續開啟兩筆不同訂單的編輯 modal，確認第二次開啟時顯示的是第二筆訂單的機車（非第一筆）
- [x] 2.2 測試：開啟有機車的訂單，確認機車顯示為已選中
- [x] 2.3 測試：開啟沒有機車的訂單，確認顯示「目前尚未選擇任何機車」，且可租借列表正常顯示
