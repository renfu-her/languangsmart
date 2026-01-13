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
            'same_day_transfer_fee_white' => $this->same_day_transfer_fee_white ? (int) $this->same_day_transfer_fee_white : null,
            'same_day_transfer_fee_green' => $this->same_day_transfer_fee_green ? (int) $this->same_day_transfer_fee_green : null,
            'same_day_transfer_fee_electric' => $this->same_day_transfer_fee_electric ? (int) $this->same_day_transfer_fee_electric : null,
            'same_day_transfer_fee_tricycle' => $this->same_day_transfer_fee_tricycle ? (int) $this->same_day_transfer_fee_tricycle : null,
            'overnight_transfer_fee_white' => $this->overnight_transfer_fee_white ? (int) $this->overnight_transfer_fee_white : null,
            'overnight_transfer_fee_green' => $this->overnight_transfer_fee_green ? (int) $this->overnight_transfer_fee_green : null,
            'overnight_transfer_fee_electric' => $this->overnight_transfer_fee_electric ? (int) $this->overnight_transfer_fee_electric : null,
            'overnight_transfer_fee_tricycle' => $this->overnight_transfer_fee_tricycle ? (int) $this->overnight_transfer_fee_tricycle : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

