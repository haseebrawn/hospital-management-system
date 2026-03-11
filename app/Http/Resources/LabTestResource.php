<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabTestResource extends JsonResource
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
            'test_type' => $this->test_type,
            'status' => $this->status,
            'results' => $this->results,
            'patient' => $this->patient->name ?? null,
            'doctor' => $this->doctor->name ?? null,
            'lab_technician' => $this->technician->name ?? null,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
