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
            'same_day_transfer_fee' => $this->same_day_transfer_fee ? (float) $this->same_day_transfer_fee : null,
            'overnight_transfer_fee' => $this->overnight_transfer_fee ? (float) $this->overnight_transfer_fee : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

