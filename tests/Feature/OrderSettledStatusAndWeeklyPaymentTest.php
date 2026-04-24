<?php

namespace Tests\Feature;

use App\Exports\PartnerMonthlyReportExport;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerScooterModelTransferFee;
use App\Models\Scooter;
use App\Models\ScooterModel;
use App\Models\ScooterType;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSettledStatusAndWeeklyPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_accepts_weekly_payment_method_and_settled_status(): void
    {
        [$store, $partner, $primaryModel] = $this->createBaseFixtures();
        [$scooter] = $this->createPrimaryScooters($store, $primaryModel, 1);
        $this->createTransferFee($partner, $primaryModel, 100, 300);

        $response = $this->postJson('/api/orders', [
            'partner_id' => $partner->id,
            'store_id' => $store->id,
            'tenant' => '王小明',
            'appointment_date' => '2026-04-23',
            'start_time' => '2026-04-23T09:00',
            'end_time' => '2026-04-23T18:00',
            'payment_method' => '週結',
            'payment_amount' => 100,
            'status' => '已結清',
            'scooter_ids' => [$scooter->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.payment_method', '週結')
            ->assertJsonPath('data.status', '已結清');

        $order = Order::findOrFail($response->json('data.id'));
        $this->assertSame('週結', $order->payment_method);
        $this->assertSame('已結清', $order->status);
        $this->assertSame('待出租', $scooter->fresh()->status);
    }

    public function test_partner_monthly_report_export_marks_settled_orders_in_red_and_adds_note(): void
    {
        $export = new PartnerMonthlyReportExport(
            '測試合作商',
            2026,
            4,
            [[
                'date' => '2026-04-01',
                'weekday' => 'Wednesday',
                'orders' => [[
                    'order_number' => 'ORD-001',
                    'status' => '已結清',
                    'models' => [[
                        'model' => 'JET SL',
                        'type' => '白牌',
                        'same_day_count' => 1,
                        'same_day_days' => 1,
                        'same_day_amount' => 600,
                        'overnight_count' => 0,
                        'overnight_days' => 0,
                        'overnight_amount' => 0,
                    ]],
                ]],
            ]],
            ['JET SL 白牌']
        );

        $spreadsheet = $export->generate();
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('紅字標示為已結清訂單', $sheet->getCell('A2')->getValue());
        $this->assertSame('2026年04月01日', $sheet->getCell('A6')->getValue());
        $this->assertSame('星期三', $sheet->getCell('B6')->getValue());
        $this->assertSame('FF0000', $sheet->getStyle('A6')->getFont()->getColor()->getRGB());
        $this->assertSame('FF0000', $sheet->getStyle('F6')->getFont()->getColor()->getRGB());
    }

    private function createBaseFixtures(): array
    {
        $store = Store::create([
            'name' => '測試門市',
            'manager' => '店長',
        ]);

        $partner = Partner::create([
            'name' => '測試合作商',
            'manager' => '窗口',
            'store_id' => $store->id,
        ]);

        $scooterType = ScooterType::create([
            'name' => '白牌',
            'color' => '#FFFFFF',
        ]);

        $primaryModel = ScooterModel::create([
            'name' => 'JET SL',
            'scooter_type_id' => $scooterType->id,
            'type' => '白牌',
        ]);

        return [$store, $partner, $primaryModel];
    }

    private function createPrimaryScooters(Store $store, ScooterModel $model, int $count = 2): array
    {
        $scooters = [];

        for ($i = 1; $i <= $count; $i++) {
            $scooters[] = $this->createScooter($store, $model, sprintf('SET-%03d', $i));
        }

        return $scooters;
    }

    private function createScooter(Store $store, ScooterModel $model, string $plateNumber): Scooter
    {
        return Scooter::create([
            'store_id' => $store->id,
            'scooter_model_id' => $model->id,
            'plate_number' => $plateNumber,
            'model' => $model->name,
            'type' => $model->type,
            'status' => '待出租',
        ]);
    }

    private function createTransferFee(Partner $partner, ScooterModel $model, int $sameDayFee, int $overnightFee): void
    {
        PartnerScooterModelTransferFee::create([
            'partner_id' => $partner->id,
            'scooter_model_id' => $model->id,
            'same_day_transfer_fee' => $sameDayFee,
            'overnight_transfer_fee' => $overnightFee,
        ]);
    }
}
