<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => __DIR__ . '/database/test-debug.sqlite']);
@unlink(__DIR__ . '/database/test-debug.sqlite');
touch(__DIR__ . '/database/test-debug.sqlite');
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--database' => 'sqlite', '--force' => true]);
echo Artisan::output();

$store = App\Models\Store::create(['name' => '測試門市', 'manager' => '店長']);
$partner = App\Models\Partner::create(['name' => '測試合作商', 'manager' => '窗口', 'store_id' => $store->id]);
$type = App\Models\ScooterType::create(['name' => '白牌', 'color' => '#FFFFFF']);
$model = App\Models\ScooterModel::create(['name' => 'JET SL', 'scooter_type_id' => $type->id, 'type' => '白牌']);
$s1 = App\Models\Scooter::create(['store_id' => $store->id, 'scooter_model_id' => $model->id, 'plate_number' => 'A1', 'model' => $model->name, 'type' => $model->type, 'status' => '待出租']);
$s2 = App\Models\Scooter::create(['store_id' => $store->id, 'scooter_model_id' => $model->id, 'plate_number' => 'A2', 'model' => $model->name, 'type' => $model->type, 'status' => '待出租']);
App\Models\PartnerScooterModelTransferFee::create(['partner_id' => $partner->id, 'scooter_model_id' => $model->id, 'same_day_transfer_fee' => 100, 'overnight_transfer_fee' => 300]);
$calc = $app->make(App\Services\OrderAmountCalculator::class);
var_dump($calc->calculate($partner->id, [$s1->id, $s2->id], '2026-04-01 09:00:00', '2026-04-01 18:00:00'));
$fees = App\Models\PartnerScooterModelTransferFee::with('scooterModel')->where('partner_id',$partner->id)->get();
foreach ($fees as $fee) { echo 'fee-key=' . ($fee->scooterModel ? ($fee->scooterModel->name . ' ' . $fee->scooterModel->type) : 'null') . PHP_EOL; }
$scooters = App\Models\Scooter::whereIn('id', [$s1->id,$s2->id])->get();
foreach ($scooters as $s) { echo 'scooter-key=' . trim(($s->model ?? '') . ' ' . ($s->type ?? '')) . PHP_EOL; }
