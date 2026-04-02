<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerScooterModelTransferFee;
use App\Models\Scooter;
use App\Models\ScooterModel;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAmountRecalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_calculates_payment_amount_on_backend(): void
    {
        [$store, $partner, $primaryModel, $secondaryModel] = $this->createBaseFixtures();
        [$scooterA, $scooterB] = $this->createPrimaryScooters($store, $primaryModel);
        $this->createTransferFee($partner, $primaryModel, 100, 300);

        $response = $this->postJson('/api/orders', [
            'partner_id' => $partner->id,
            'store_id' => $store->id,
            'tenant' => '王小明',
            'appointment_date' => '2026-04-01',
            'start_time' => '2026-04-01T09:00',
            'end_time' => '2026-04-01T18:00',
            'payment_amount' => 9999,
            'status' => '已預訂',
            'scooter_ids' => [$scooterA->id, $scooterB->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.payment_amount', 200.0);

        $orderId = $response->json('data.id');
        $this->assertSame(200.0, (float) Order::findOrFail($orderId)->payment_amount);
    }

    public function test_update_keeps_original_amount_when_only_non_fee_fields_change(): void
    {
        [$store, $partner, $primaryModel] = $this->createBaseFixtures();
        [$scooterA] = $this->createPrimaryScooters($store, $primaryModel, 1);
        $this->createTransferFee($partner, $primaryModel, 100, 300);
        $order = $this->createOrder($partner, $store, [$scooterA], '2026-04-01 09:00:00', '2026-04-01 18:00:00', 100);

        $response = $this->putJson("/api/orders/{$order->id}", [
            'partner_id' => $partner->id,
            'store_id' => $store->id,
            'tenant' => '王小明',
            'appointment_date' => '2026-04-01',
            'start_time' => '2026-04-01T09:00',
            'end_time' => '2026-04-01T18:00',
            'payment_amount' => 9999,
            'status' => '已預訂',
            'remark' => '只改備註',
            'scooter_ids' => [$scooterA->id],
        ]);

        $response->assertOk()
            ->assertJsonMissingPath('warning')
            ->assertJsonPath('data.payment_amount', 100.0);

        $this->assertSame(100.0, (float) $order->fresh()->payment_amount);
    }

    public function test_update_accepts_manual_payment_amount_override(): void
    {
        [$store, $partner, $primaryModel] = $this->createBaseFixtures();
        [$scooterA] = $this->createPrimaryScooters($store, $primaryModel, 1);
        $this->createTransferFee($partner, $primaryModel, 100, 300);
        $order = $this->createOrder($partner, $store, [$scooterA], '2026-04-01 09:00:00', '2026-04-01 18:00:00', 100);

        $response = $this->putJson("/api/orders/{$order->id}", [
            'partner_id' => $partner->id,
            'store_id' => $store->id,
            'tenant' => '王小明',
            'appointment_date' => '2026-04-01',
            'start_time' => '2026-04-01T09:00',
            'end_time' => '2026-04-02T09:00',
            'payment_amount' => 555,
            'is_manual_amount' => true,
            'status' => '已預訂',
            'scooter_ids' => [$scooterA->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.payment_amount', 555.0);

        $this->assertSame(555.0, (float) $order->fresh()->payment_amount);
    }

    public function test_update_recalculates_amount_when_scooters_change(): void
    {
        [$store, $partner, $primaryModel] = $this->createBaseFixtures();
        [$scooterA, $scooterB] = $this->createPrimaryScooters($store, $primaryModel);
        $this->createTransferFee($partner, $primaryModel, 100, 300);
        $order = $this->createOrder($partner, $store, [$scooterA], '2026-04-01 09:00:00', '2026-04-01 18:00:00', 100);

        $response = $this->putJson("/api/orders/{$order->id}", [
            'partner_id' => $partner->id,
            'store_id' => $store->id,
            'tenant' => '王小明',
            'appointment_date' => '2026-04-01',
            'start_time' => '2026-04-01T09:00',
            'end_time' => '2026-04-01T18:00',
            'status' => '已預訂',
            'scooter_ids' => [$scooterA->id, $scooterB->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.payment_amount', 200.0);

        $this->assertSame(200.0, (float) $order->fresh()->payment_amount);
    }

    public function test_update_recalculates_amount_when_time_changes(): void
    {
        [$store, $partner, $primaryModel] = $this->createBaseFixtures();
        [$scooterA] = $this->createPrimaryScooters($store, $primaryModel, 1);
        $this->createTransferFee($partner, $primaryModel, 100, 300);
        $order = $this->createOrder($partner, $store, [$scooterA], '2026-04-01 09:00:00', '2026-04-01 18:00:00', 100);

        $response = $this->putJson("/api/orders/{$order->id}", [
            'partner_id' => $partner->id,
            'store_id' => $store->id,
            'tenant' => '王小明',
            'appointment_date' => '2026-04-01',
            'start_time' => '2026-04-01T09:00',
            'end_time' => '2026-04-02T09:00',
            'status' => '已預訂',
            'scooter_ids' => [$scooterA->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.payment_amount', 300.0);

        $this->assertSame(300.0, (float) $order->fresh()->payment_amount);
    }

    public function test_update_keeps_amount_when_same_scooters_are_submitted_in_different_order(): void
    {
        [$store, $partner, $primaryModel] = $this->createBaseFixtures();
        [$scooterA, $scooterB] = $this->createPrimaryScooters($store, $primaryModel);
        $this->createTransferFee($partner, $primaryModel, 100, 300);
        $order = $this->createOrder($partner, $store, [$scooterA, $scooterB], '2026-04-01 09:00:00', '2026-04-01 18:00:00', 200);

        $response = $this->putJson("/api/orders/{$order->id}", [
            'partner_id' => $partner->id,
            'store_id' => $store->id,
            'tenant' => '王小明',
            'appointment_date' => '2026-04-01',
            'start_time' => '2026-04-01T09:00',
            'end_time' => '2026-04-01T18:00',
            'status' => '已預訂',
            'remark' => '順序交換',
            'scooter_ids' => [$scooterB->id, $scooterA->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.payment_amount', 200.0);

        $this->assertSame(200.0, (float) $order->fresh()->payment_amount);
    }

    public function test_update_returns_warning_and_keeps_old_amount_when_recalculation_fails(): void
    {
        [$store, $partner, $primaryModel, $secondaryModel] = $this->createBaseFixtures();
        [$scooterA] = $this->createPrimaryScooters($store, $primaryModel, 1);
        $scooterWithoutFee = $this->createScooter($store, $secondaryModel, 'TST-003');
        $this->createTransferFee($partner, $primaryModel, 100, 300);
        $order = $this->createOrder($partner, $store, [$scooterA], '2026-04-01 09:00:00', '2026-04-01 18:00:00', 100);

        $response = $this->putJson("/api/orders/{$order->id}", [
            'partner_id' => $partner->id,
            'store_id' => $store->id,
            'tenant' => '王小明',
            'appointment_date' => '2026-04-01',
            'start_time' => '2026-04-01T09:00',
            'end_time' => '2026-04-01T18:00',
            'status' => '已預訂',
            'scooter_ids' => [$scooterWithoutFee->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.payment_amount', 100.0)
            ->assertJsonPath('warning', '無法重新計算有效總金額，已保留原訂單金額。請檢查合作商費率、車輛或時間資料是否完整。');

        $this->assertSame(100.0, (float) $order->fresh()->payment_amount);
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

        $primaryModel = ScooterModel::create([
            'name' => 'JET SL',
            'type' => '白牌',
        ]);

        $secondaryModel = ScooterModel::create([
            'name' => 'MMBCU',
            'type' => '白牌',
        ]);

        return [$store, $partner, $primaryModel, $secondaryModel];
    }

    private function createPrimaryScooters(Store $store, ScooterModel $model, int $count = 2): array
    {
        $scooters = [];

        for ($i = 1; $i <= $count; $i++) {
            $scooters[] = $this->createScooter($store, $model, sprintf('TST-%03d', $i));
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

    private function createOrder(Partner $partner, Store $store, array $scooters, string $startTime, string $endTime, float $paymentAmount): Order
    {
        $order = Order::create([
            'partner_id' => $partner->id,
            'store_id' => $store->id,
            'tenant' => '王小明',
            'appointment_date' => '2026-04-01',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'payment_amount' => $paymentAmount,
            'status' => '已預訂',
        ]);

        $order->scooters()->attach(array_map(fn (Scooter $scooter) => $scooter->id, $scooters));

        return $order->load('scooters');
    }
}
