## Context

`AddOrderModal` 在編輯模式開啟時，需要同時做兩件事：
1. 載入「可租借」機車列表（`status=待出租`）供使用者選擇
2. 把訂單已關聯的機車（狀態可能是「出租中」）顯示為已選中

目前實作用兩個並發 async call（`fetchAvailableScooters` + `fetchScootersByIds`）分別完成這兩件事，並都透過 functional update 修改同一個 `availableScooters` state。`selectedScooterIds` 在 modal 關閉/開啟時也沒有主動 reset，造成 stale state 問題。

## Goals / Non-Goals

**Goals:**
- 編輯模式下，每次開啟 modal 都能可靠地顯示訂單已選的機車
- 消除 `fetchAvailableScooters` 與 `fetchScootersByIds` 的 race condition
- 確保開啟新訂單時 stale state 不干擾顯示

**Non-Goals:**
- 不改變 UI layout 或功能邏輯
- 不改動後端 API
- 不做新增模式的修改（問題僅出現在編輯模式）

## Decisions

### 決策 1：改為依序執行，不並發

**選擇**：在 `useEffect` 中改用一個 async function，先 await `fetchAvailableScooters`，再 await `fetchScootersByIds` 並 merge 結果，最後一次性 set state。

**替代方案**：用 `Promise.all` 等待兩個並發請求都完成後一起 set → 比較複雜，且兩個請求的 payload 重疊（都要考慮同一批 scooter ID），依序更清晰。

**理由**：兩個 functional update 的結果取決於執行順序，改為 sequential 消除不確定性，且可在一個地方統一處理 merge 邏輯。

### 決策 2：modal 開啟時明確 reset availableScooters 與 selectedScooterIds

**選擇**：在 Effect 2（或統一的 init effect）開頭先 `setAvailableScooters([])` 與 `setSelectedScooterIds([])`，再開始 fetch。

**理由**：component 不 unmount 時，`useState` 保留上一次的值。Reset 確保不論前一次 modal 狀態為何，每次開啟都是乾淨狀態。

### 決策 3：`scooter_ids` 加 fallback

**選擇**：`const orderScooterIds = editingOrder?.scooter_ids?.length ? editingOrder.scooter_ids : (editingOrder?.scooters?.map(s => s.id) ?? [])`

**理由**：`scooter_ids` 透過 `whenLoaded` 回傳，理論上後端一定 eager load，但加 fallback 讓前端更防禦性，避免未來後端改動造成靜默失敗。

## Risks / Trade-offs

- [短暫 loading 閃爍] Reset 後有短暫空白期 → 可加 loading spinner（可選，不在此次 scope）
- [API 次數不變] 改為依序後兩個 API 仍各打一次，只是不再並發，latency 略增 → 接受，正確性優先

## Open Questions

- 是否要在「找不到 scooterIds 對應的機車」時顯示 warning toast？（目前只有 console.warn）→ 暫保留 console.warn 即可
