<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScooterResource extends JsonResource
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
            'partner_id' => $this->partner_id,
            'partner' => $this->whenLoaded('partner', function () {
                return new PartnerResource($this->partner);
            }),
            'plate_number' => $this->plate_number,
            'model' => $this->model,
            'type' => $this->type,
            'color' => $this->color,
            'status' => $this->status,
            'photo_path' => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

