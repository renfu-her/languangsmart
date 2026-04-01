<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderAmountCalculator;
use Illuminate\Console\Command;

class FixOrderAmounts extends Command
{
    protected $signature = 'orders:fix-amounts {--apply : 實際修正資料（預設為 dry-run）}';

    protected $description = '掃描並修正 payment_amount 與實際計算值不符的訂單。預設為 dry-run，加 --apply 才會實際寫入。';

    public function __construct(private OrderAmountCalculator $calculator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $isApply = $this->option('apply');
        $mode = $isApply ? '套用模式' : 'Dry-run 模式';
        $this->info("orders:fix-amounts — {$mode}");
        $this->newLine();

        $orders = Order::with(['scooters', 'partner'])->get();
        $total = $orders->count();
        $diffCount = 0;
        $skippedCount = 0;
        $fixedCount = 0;

        $rows = [];

        foreach ($orders as $order) {
            $scooterIds = $order->scooters->pluck('id')->toArray();
            $calculated = $this->calculator->calculate(
                $order->partner_id,
                $scooterIds,
                $order->start_time,
                $order->end_time
            );

            // 費率資料不完整，跳過
            if ($calculated === 0.0) {
                $skippedCount++;
                $this->warn("  [SKIP] 訂單 #{$order->id} ({$order->tenant}) — 費率資料不完整，無法計算，跳過");
                continue;
            }

            $stored = (float) $order->payment_amount;

            if (abs($stored - $calculated) > 0.01) {
                $diffCount++;
                $rows[] = [
                    $order->id,
                    $order->tenant ?? '-',
                    $order->appointment_date ?? '-',
                    number_format($stored),
                    number_format($calculated),
                ];

                if ($isApply) {
                    $order->update(['payment_amount' => $calculated]);
                    $fixedCount++;
                }
            }
        }

        if (empty($rows)) {
            $this->info("✓ 所有訂單 payment_amount 均正確，無需修正。");
        } else {
            $this->table(
                ['訂單 ID', '租客', '預約日期', '目前金額', '計算金額'],
                $rows
            );

            if ($isApply) {
                $this->newLine();
                $this->info("✓ 已修正 {$fixedCount} 筆訂單。");
            } else {
                $this->newLine();
                $this->comment("共 {$diffCount} 筆訂單金額有差異。執行 --apply 以實際修正。");
            }
        }

        if ($skippedCount > 0) {
            $this->newLine();
            $this->warn("略過 {$skippedCount} 筆訂單（費率資料不完整）。");
        }

        $this->newLine();
        $this->line("掃描總計：{$total} 筆，差異：{$diffCount} 筆，略過：{$skippedCount} 筆。");

        return Command::SUCCESS;
    }
}
