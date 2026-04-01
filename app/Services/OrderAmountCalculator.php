<?php

namespace App\Services;

use App\Models\Scooter;
use App\Models\PartnerScooterModelTransferFee;
use Carbon\Carbon;

class OrderAmountCalculator
{
    /**
     * 計算訂單金額。
     * 依合作商費率、機車型號、租借天數計算總金額。
     * 若缺少必要資料（合作商、機車、時間），返回 0。
     */
    public function calculate($partnerId, array $scooterIds, $startTime, $endTime): float
    {
        if (!$partnerId || empty($scooterIds) || !$startTime || !$endTime) {
            return 0;
        }

        $scooters = Scooter::whereIn('id', $scooterIds)->get();
        if ($scooters->isEmpty()) {
            return 0;
        }

        $startTimeCarbon = Carbon::parse($startTime)->timezone('Asia/Taipei');
        $endTimeCarbon = Carbon::parse($endTime)->timezone('Asia/Taipei');
        $isSameDay = $startTimeCarbon->isSameDay($endTimeCarbon);
        $days = $isSameDay ? 1 : $startTimeCarbon->diffInDays($endTimeCarbon);

        $transferFeesMap = PartnerScooterModelTransferFee::with('scooterModel')
            ->where('partner_id', $partnerId)
            ->get()
            ->keyBy(function ($fee) {
                return $fee->scooterModel
                    ? "{$fee->scooterModel->name} {$fee->scooterModel->type}"
                    : null;
            })
            ->filter();

        $totalAmount = 0;

        $scootersByModel = $scooters->groupBy(function ($scooter) {
            $model = $scooter->attributes['model'] ?? '';
            $type = $scooter->attributes['type'] ?? '';
            if ($model && $type) {
                return trim("{$model} {$type}");
            }
            if ($model) {
                return $model;
            }
            if ($type) {
                return $type;
            }
            return $scooter->plate_number ?? '';
        })->filter(function ($scooters, $modelString) {
            return !empty($modelString);
        });

        foreach ($scootersByModel as $modelString => $modelScooters) {
            $scooterCount = $modelScooters->count();
            $feeKey = $transferFeesMap->get($modelString);
            $transferFeePerUnit = $feeKey
                ? ($isSameDay ? ($feeKey->same_day_transfer_fee ?? 0) : ($feeKey->overnight_transfer_fee ?? 0))
                : 0;
            $totalAmount += (int) $transferFeePerUnit * $days * $scooterCount;
        }

        return (float) $totalAmount;
    }
}
