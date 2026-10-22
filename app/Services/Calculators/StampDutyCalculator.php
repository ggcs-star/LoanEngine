<?php

namespace App\Services\Calculators;

class StampDutyCalculator
{
   
    private const STATES = [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar',
        'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana',
        'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala',
        'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya',
        'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan',
        'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
        'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
        'Andaman and Nicobar Islands', 'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu',
        'Delhi', 'Jammu and Kashmir', 'Ladakh',
        'Lakshadweep', 'Puducherry',
    ];

    public function calculate(array $inputs): array
    {
        $propertyValue = (float) $inputs['property_value'];
        $state = $inputs['state'];
        $propertyType = $inputs['property_type'];
        $buyerCategory = $inputs['buyer_category'];
        $method = $inputs['stamp_duty_method'];

        /*
         * These are configurable demonstration defaults, NOT verified
         * legal rates for each state or buyer category.
         */
        $baseRate = (float) ($inputs['stamp_duty_rate'] ?? 5);
        $registrationType = $inputs['registration_type'];
        $registrationValue = (float) $inputs['registration_value'];
        $otherCharges = (float) $inputs['other_charges'];

        if ($method === 'slab') {
            $stampDuty = $this->calculateSlabDuty($propertyValue);
        } else {
            $stampDuty = $propertyValue * ($baseRate / 100);
        }

        $registrationFee = $registrationType === 'percentage'
            ? $propertyValue * ($registrationValue / 100)
            : $registrationValue;

        $stampDuty = max(0, $stampDuty);
        $registrationFee = max(0, $registrationFee);
        $otherCharges = max(0, $otherCharges);

        $totalCharges = $stampDuty + $registrationFee + $otherCharges;
        $upfrontCost = $propertyValue + $totalCharges;

        $chargePercentage = $upfrontCost > 0
            ? ($totalCharges / $upfrontCost) * 100
            : 0;

        $insights = [];

        if ($stampDuty > 0) {
            $insights[] = [
                'title' => 'Stamp duty',
                'description' => sprintf(
                    'Stamp duty is %s of the estimated additional charges.',
                    number_format(
                        $totalCharges > 0
                            ? ($stampDuty / $totalCharges) * 100
                            : 0,
                        1
                    ) . '%'
                ),
            ];
        }

        $insights[] = [
            'title' => 'State and property',
            'description' => sprintf(
                'Estimate configured for %s and %s.',
                $state,
                $propertyType
            ),
        ];

        $insights[] = [
            'title' => 'Estimated upfront cost',
            'description' => sprintf(
                'Property value plus estimated charges: ₹%s.',
                number_format($upfrontCost, 0, '.', ',')
            ),
        ];

        if (in_array($buyerCategory, ['female', 'joint', 'senior', 'first_time'], true)) {
            $insights[] = [
                'title' => 'Check buyer concessions',
                'description' => 'Confirm whether your selected buyer category qualifies for a concession under the applicable state rules. No concession is assumed automatically.',
            ];
        }

        return [
            'state' => $state,
            'property_type' => $propertyType,
            'buyer_category' => $buyerCategory,
            'stamp_duty_method' => $method,
            'stamp_duty_rate' => $baseRate,
            'property_value' => round($propertyValue, 2),
            'stamp_duty' => round($stampDuty, 2),
            'registration_fee' => round($registrationFee, 2),
            'other_charges' => round($otherCharges, 2),
            'total_charges' => round($totalCharges, 2),
            'upfront_cost' => round($upfrontCost, 2),
            'charges_percentage' => round($chargePercentage, 2),
            'insights' => $insights,
            'notice' => 'Indicative estimate only. Actual stamp duty, registration fees and concessions depend on current state rules and transaction details.',
        ];
    }

    private function calculateSlabDuty(float $propertyValue): float
    {
        /*
         * Example progressive slabs for demonstration only.
         * Replace with the selected state's verified slab rules.
         */
        $slabs = [
            ['limit' => 1000000, 'rate' => 2],
            ['limit' => 5000000, 'rate' => 3],
            ['limit' => INF, 'rate' => 5],
        ];

        $remaining = max(0, $propertyValue);
        $previousLimit = 0;
        $duty = 0;

        foreach ($slabs as $slab) {
            $bandWidth = $slab['limit'] - $previousLimit;
            $taxableInBand = min($remaining, $bandWidth);

            if ($taxableInBand > 0) {
                $duty += $taxableInBand * ($slab['rate'] / 100);
                $remaining -= $taxableInBand;
            }

            $previousLimit = $slab['limit'];

            if ($remaining <= 0) {
                break;
            }
        }

        return $duty;
    }
}
