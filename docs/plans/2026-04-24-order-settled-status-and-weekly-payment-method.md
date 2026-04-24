# 訂單已結清狀態與週結付款方式 Task Plan

> 執行規則：只能按照下列 task 執行；task 沒有寫到的內容，不做。每完成一個 task，必須回到這份文件打勾。

## 背景 / 目標
- 合作商有時會在每月正式結帳日前先行結帳，導致目前匯出合作商月報表 Excel 時，無法快速辨識哪些訂單已先結清。
- 使用者希望在訂單狀態最下方新增「已結清」選項，並讓匯出 Excel 能清楚標示「已結清」訂單（依附圖需求，以紅字呈現，並補上紅字標示說明）。
- 另外需要在付款方式新增「週結」，並同步到可建立/編輯訂單的相關前後端流程。

## Scope
- 在 orders 狀態與 payment_method 的資料層、API 驗證層、後台編輯/建立 UI 層加入新選項。
- 調整合作商月報表匯出資料結構與 Excel 樣式，讓「已結清」訂單於明細/總計中可被紅字辨識，並依需求補上說明文字。
- 補最小必要測試，驗證新 enum 值與匯出標示邏輯。

## Out of scope
- 不調整其他未被需求提及的訂單流程或狀態文案。
- 不重做整個合作商月報表版型，只做與「已結清」辨識相關的最小必要修改。
- 不主動擴充其他付款方式、篩選器、報表欄位或批次結帳流程。
- 不修改與本需求無關的 booking / guesthouse / 其他模組 UI。

## Task Checklist

- [x] Task 1: 盤點並補齊訂單狀態/付款方式的新 enum 與驗證來源
  - Files:
    - `database/migrations/`
    - `app/Http/Controllers/Api/OrderController.php`
    - `app/Http/Controllers/Api/BookingController.php`
    - `system/backend/types.ts`
  - In scope:
    - 新增 orders `status` 的「已結清」資料層支援。
    - 新增 orders `payment_method` 的「週結」資料層支援。
    - 同步更新 Order / Booking API 驗證可接受值。
    - 盤點是否有共用 enum / 型別檔需同步補值，避免前後端不一致。
  - Out of scope:
    - 不改其他資料表欄位。
    - 不新增與本需求無關的資料轉換/清洗規則。
  - 驗證:
    - 新 migration 可通過語法檢查。
    - `php artisan test` 至少可跑過本次新增/相關測試。
  - 完成內容：
    - 已新增 orders `status=已結清` 與 `payment_method=週結` migration。
    - 已同步更新 Order / Booking API 驗證字串與後台共用 enum 型別。
    - 已將 `已結清` 視為待出租類型狀態，避免機車狀態映射錯誤。
  - 驗證結果：
    - `php -l database/migrations/2026_04_24_140500_add_weekly_payment_method_and_settled_status_to_orders_table.php`
    - `php -l app/Http/Controllers/Api/OrderController.php`
    - `php -l app/Http/Controllers/Api/BookingController.php`

- [x] Task 2: 更新後台訂單相關 UI，新增「已結清」與「週結」選項
  - Files:
    - `system/backend/components/AddOrderModal.tsx`
    - `system/backend/components/ConvertBookingModal.tsx`
    - `system/backend/pages/OrdersPage.tsx`
    - `system/backend/types.ts`
  - In scope:
    - 在訂單建立/編輯 modal 的付款方式下拉新增「週結」。
    - 在訂單狀態下拉最下方新增「已結清」。
    - 若訂單列表有付款方式顏色或狀態排序/顯示邏輯，補上對應分支，避免新值顯示異常。
    - 維持現有 UI 結構與欄位寬度/對齊，不重排版。
  - Out of scope:
    - 不改動與本需求無關的欄位、按鈕或表單流程。
    - 不做額外的 UI 美化或重構。
  - 驗證:
    - `pnpm --dir system/backend build` 可通過。
    - 檢查相關下拉選單程式碼皆已包含新值。
  - 完成內容：
    - 已在新增/編輯訂單與預約轉訂單流程加入 `週結`。
    - 已在訂單狀態下拉、狀態 badge、排序與快捷狀態選單加入 `已結清`。
    - 已補上 `週結` 的列表顏色顯示。
  - 驗證結果：
    - `pnpm --dir system/backend build` 通過。
    - 已檢查 `AddOrderModal.tsx`、`ConvertBookingModal.tsx`、`OrdersPage.tsx` 皆包含新值。

- [x] Task 3: 調整合作商月報表匯出，讓「已結清」訂單在 Excel 內以紅字辨識並補上標示說明
  - Files:
    - `app/Http/Controllers/Api/OrderController.php`
    - `app/Exports/PartnerMonthlyReportExport.php`
    - `system/backend/pages/OrdersPage.tsx`
  - In scope:
    - 將訂單狀態資訊帶入合作商日/月報表匯出資料結構。
    - 依附圖需求，讓「已結清」訂單對應的 Excel 明細/總計內容以紅字呈現。
    - 在 Excel 指定區域補上紅字說明文字（如紅框示意），避免使用者匯出後無法理解紅字意義。
    - 若前端目前使用 ExcelJS 自行匯出，需同步更新前端匯出邏輯，避免與後端匯出表現不一致。
  - Out of scope:
    - 不新增新的匯出格式。
    - 不修改與「已結清」無關的統計公式。
  - 驗證:
    - 以測試或程式化方式驗證匯出資料中可辨識 `已結清` 訂單。
    - 確認前端/後端匯出邏輯中的紅字條件一致。
  - 完成內容：
    - 已在合作商日報 JSON 中附帶每筆訂單 `status`，供前後端匯出共用。
    - 前端 ExcelJS 匯出已加入 `紅字標示為已結清訂單` 說明列，且 `已結清` 訂單資料列改為紅字。
    - 後端 PhpSpreadsheet 匯出已同步加入說明列與紅字列標示，並修正相容寫值 helper。
  - 驗證結果：
    - `php -l app/Exports/PartnerMonthlyReportExport.php`
    - `php artisan test --filter=OrderSettledStatusAndWeeklyPaymentTest`

- [x] Task 4: 補測試與回寫驗證結果
  - Files:
    - `tests/Feature/`
    - `database/migrations/`（限 sqlite 測試環境所需的 enum/string raw ALTER TABLE 相容性修補）
    - `docs/plans/2026-04-24-order-settled-status-and-weekly-payment-method.md`
  - In scope:
    - 新增最小必要 Feature / 匯出測試，涵蓋新 status/payment_method 驗證與已結清匯出標示。
    - 若 sqlite 測試環境被 enum-only migration 阻塞，可對上述 migration 補 driver guard，讓測試可執行，同時不影響 MySQL 正式行為。
    - 執行本計畫列出的驗證命令後，將完成狀態與必要驗證結果回寫到本檔。
  - Out of scope:
    - 不補與本需求無關的大量測試重構。
  - 驗證:
    - `php artisan test --filter=OrderSettledStatusAndWeeklyPaymentTest`
    - `pnpm --dir system/backend build`
  - 完成內容：
    - 已新增 `tests/Feature/OrderSettledStatusAndWeeklyPaymentTest.php`，覆蓋週結/已結清 API 驗證與匯出紅字標示。
    - 已對 sqlite 測試環境會卡住的 raw ALTER TABLE migration 補上 driver guard。
    - 已同步調整既有 `OrderAmountRecalculationTest` 基礎 fixture，配合目前 `scooter_type_id` schema。
  - 驗證結果：
    - `cmd.exe /C "cd /d D:\laragon\www\languangsmart && D:\laragon\bin\php\php-8.4.15-nts-Win32-vs17-x64\php.exe artisan test --filter=OrderSettledStatusAndWeeklyPaymentTest"`
    - `pnpm --dir system/backend build`

## 驗證方式
- Backend:
  - `php artisan test --filter=OrderSettledStatusAndWeeklyPaymentTest`
- Frontend:
  - `pnpm --dir system/backend build`
- Code review:
  - 確認下列新值一致存在：
    - 訂單狀態：`已結清`
    - 付款方式：`週結`
  - 確認合作商 Excel 匯出對 `已結清` 訂單有紅字標示與說明文字。

## 風險 / 待確認事項
- 目前專案同時存在 Laravel `PhpSpreadsheet` 匯出與後台頁面 `ExcelJS` 匯出邏輯，若兩者都仍在使用，需同步調整避免行為分歧。
- `已結清` 是否在所有機車狀態映射中視同「已完成/待出租」處理，實作時需維持既有訂單結束態的業務語意。
- 若資料庫現有 `orders.status` / `orders.payment_method` 為 enum，需用 migration 安全擴值，避免直接寫入造成 SQL 錯誤。
