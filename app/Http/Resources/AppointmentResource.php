<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'patient' => $this->whenLoaded('patient', fn () => trim(($this->patient->first_name ?? '') . ' ' . ($this->patient->last_name ?? ''))),
            'doctor' => $this->whenLoaded('doctor', fn () => $this->doctor?->name),
            'date' => $this->date,
            'time' => $this->time,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'status' => $this->status,
            'visit_status' => $this->visit_status,
            'checked_in_at' => optional($this->checked_in_at)->toIso8601String(),
            'checked_out_at' => optional($this->checked_out_at)->toIso8601String(),
        ];
    }
}
