<?php

namespace App\Http\Resources\Api\V1\User\Calculators;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalculatorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $inputs = $this->input_config ?? [];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'icon' => $this->icon,
            'formula_type' => $this->formula_type,
            'route_name' => $this->route_name,
            'display_order' => $this->display_order,
            'inputs' => $inputs,
            'defaults' => $this->extractDefaults($inputs),
            'results' => $this->result_config ?? [],
        ];
    }

    private function extractDefaults(array $inputs): array
    {
        $defaults = [];

        foreach ($inputs as $key => $config) {
            if (is_array($config) && array_key_exists('default', $config)) {
                $defaults[$key] = $config['default'];
            }
        }

        return $defaults;
    }
}