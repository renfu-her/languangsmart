<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FineResource extends JsonResource
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
            'scooter_id' => $this->scooter_id,
            'scooter' => $this->whenLoaded('scooter', function () {
                return new ScooterResource($this->scooter);
            }),
            'order_id' => $this->order_id,
            'order' => $this->whenLoaded('order', function () {
                return $this->order ? new OrderResource($this->order) : null;
            }),
            'tenant' => $this->tenant,
            'violation_date' => $this->violation_date->format('Y-m-d'),
            'violation_type' => $this->violation_type,
            'fine_amount' => (float) $this->fine_amount,
            'payment_status' => $this->payment_status,
            'photo_path' => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

