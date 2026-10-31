<?php

namespace App\Http\Resources\Api\V1\User\Calculators;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalculatorUsageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'calculator' => [
                'id' => $this->calculator?->id,
                'name' => $this->calculator?->name,
                'slug' => $this->calculator?->slug,
                'formula_type' => $this->calculator?->formula_type,
                'icon' => $this->calculator?->icon,
            ],
            'inputs' => $this->inputs ?? [],
            'result' => $this->result ?? [],
            'event_type' => $this->event_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}