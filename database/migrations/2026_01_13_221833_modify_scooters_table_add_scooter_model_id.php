<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 步驟 1: 從現有 scooters 表中提取唯一的 (model, type) 組合，創建 ScooterModel 記錄
        $uniqueModels = DB::table('scooters')
            ->select('model', 'type')
            ->distinct()
            ->get();

        $modelMap = []; // 儲存 (model, type) => scooter_model_id 的對應關係

        foreach ($uniqueModels as $item) {
            // 檢查是否已存在相同的型號名稱（避免重複）
            $existingModel = DB::table('scooter_models')
                ->where('name', $item->model)
                ->first();

            if ($existingModel) {
                $modelMap[$item->model . '|' . $item->type] = $existingModel->id;
            } else {
                // 創建新的 ScooterModel 記錄
                $scooterModelId = DB::table('scooter_models')->insertGetId([
                    'name' => $item->model,
                    'type' => $item->type,
                    'image_path' => null,
                    'color' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $modelMap[$item->model . '|' . $item->type] = $scooterModelId;
            }
        }

        // 步驟 2: 在 scooters 表中新增 scooter_model_id 欄位（暫時允許 null）
        Schema::table('scooters', function (Blueprint $table) {
            $table->foreignId('scooter_model_id')->nullable()->after('store_id')->constrained('scooter_models')->onDelete('restrict');
        });

        // 步驟 3: 將現有的 model 字串轉換為 scooter_model_id
        foreach ($modelMap as $key => $scooterModelId) {
            [$model, $type] = explode('|', $key);
            DB::table('scooters')
                ->where('model', $model)
                ->where('type', $type)
                ->update(['scooter_model_id' => $scooterModelId]);
        }

        // 步驟 4: 將 scooter_model_id 設為必填（NOT NULL）
        Schema::table('scooters', function (Blueprint $table) {
            $table->foreignId('scooter_model_id')->nullable(false)->change();
        });

        // 步驟 5: 將現有合作商的 8 個調車費用欄位轉換為 partner_scooter_model_transfer_fees 記錄
        $partners = DB::table('partners')->get();
        
        foreach ($partners as $partner) {
            // 獲取所有機車型號
            $scooterModels = DB::table('scooter_models')->get();
            
            foreach ($scooterModels as $scooterModel) {
                $sameDayFee = null;
                $overnightFee = null;
                
                // 根據機車型號的 type 來對應到合作商的調車費用欄位
                switch ($scooterModel->type) {
                    case '白牌':
                        $sameDayFee = $partner->same_day_transfer_fee_white ?? null;
                        $overnightFee = $partner->overnight_transfer_fee_white ?? null;
                        break;
                    case '綠牌':
                        $sameDayFee = $partner->same_day_transfer_fee_green ?? null;
                        $overnightFee = $partner->overnight_transfer_fee_green ?? null;
                        break;
                    case '電輔車':
                        $sameDayFee = $partner->same_day_transfer_fee_electric ?? null;
                        $overnightFee = $partner->overnight_transfer_fee_electric ?? null;
                        break;
                    case '三輪車':
                        $sameDayFee = $partner->same_day_transfer_fee_tricycle ?? null;
                        $overnightFee = $partner->overnight_transfer_fee_tricycle ?? null;
                        break;
                }
                
                // 只有在有設定費用時才創建記錄
                if ($sameDayFee !== null || $overnightFee !== null) {
                    DB::table('partner_scooter_model_transfer_fees')->insert([
                        'partner_id' => $partner->id,
                        'scooter_model_id' => $scooterModel->id,
                        'same_day_transfer_fee' => $sameDayFee,
                        'overnight_transfer_fee' => $overnightFee,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 步驟 6: 保留 model 欄位以便向後兼容，但之後會改用 scooter_model_id
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 步驟 1: 將 scooter_model_id 轉換回 model 字串
        $scooters = DB::table('scooters')
            ->join('scooter_models', 'scooters.scooter_model_id', '=', 'scooter_models.id')
            ->select('scooters.id', 'scooter_models.name as model')
            ->get();

        foreach ($scooters as $scooter) {
            DB::table('scooters')
                ->where('id', $scooter->id)
                ->update(['model' => $scooter->model]);
        }

        // 步驟 2: 將 partner_scooter_model_transfer_fees 轉換回 partners 表的 8 個欄位
        $partners = DB::table('partners')->get();
        
        foreach ($partners as $partner) {
            $transferFees = DB::table('partner_scooter_model_transfer_fees')
                ->join('scooter_models', 'partner_scooter_model_transfer_fees.scooter_model_id', '=', 'scooter_models.id')
                ->where('partner_scooter_model_transfer_fees.partner_id', $partner->id)
                ->select('scooter_models.type', 'partner_scooter_model_transfer_fees.same_day_transfer_fee', 'partner_scooter_model_transfer_fees.overnight_transfer_fee')
                ->get();
            
            $updateData = [];
            foreach ($transferFees as $fee) {
                switch ($fee->type) {
                    case '白牌':
                        $updateData['same_day_transfer_fee_white'] = $fee->same_day_transfer_fee;
                        $updateData['overnight_transfer_fee_white'] = $fee->overnight_transfer_fee;
                        break;
                    case '綠牌':
                        $updateData['same_day_transfer_fee_green'] = $fee->same_day_transfer_fee;
                        $updateData['overnight_transfer_fee_green'] = $fee->overnight_transfer_fee;
                        break;
                    case '電輔車':
                        $updateData['same_day_transfer_fee_electric'] = $fee->same_day_transfer_fee;
                        $updateData['overnight_transfer_fee_electric'] = $fee->overnight_transfer_fee;
                        break;
                    case '三輪車':
                        $updateData['same_day_transfer_fee_tricycle'] = $fee->same_day_transfer_fee;
                        $updateData['overnight_transfer_fee_tricycle'] = $fee->overnight_transfer_fee;
                        break;
                }
            }
            
            if (!empty($updateData)) {
                DB::table('partners')
                    ->where('id', $partner->id)
                    ->update($updateData);
            }
        }

        // 步驟 3: 移除 scooter_model_id 欄位
        Schema::table('scooters', function (Blueprint $table) {
            $table->dropForeign(['scooter_model_id']);
            $table->dropColumn('scooter_model_id');
        });
    }
};
