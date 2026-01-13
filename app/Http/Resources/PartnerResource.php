<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'tax_id' => $this->tax_id,
            'manager' => $this->manager,
            'photo_path' => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'color' => $this->color,
            'is_default_for_booking' => $this->is_default_for_booking ?? false,
            'default_shipping_company' => $this->default_shipping_company,
            'transfer_fees' => $this->whenLoaded('scooterModelTransferFees', function () {
                return $this->scooterModelTransferFees->map(function ($fee) {
                    return [
                        'scooter_model_id' => $fee->scooter_model_id,
                        'scooter_model' => $fee->scooterModel ? [
                            'id' => $fee->scooterModel->id,
                            'name' => $fee->scooterModel->name,
                            'type' => $fee->scooterModel->type,
                        ] : null,
                        'same_day_transfer_fee' => $fee->same_day_transfer_fee ? (int) $fee->same_day_transfer_fee : null,
                        'overnight_transfer_fee' => $fee->overnight_transfer_fee ? (int) $fee->overnight_transfer_fee : null,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

