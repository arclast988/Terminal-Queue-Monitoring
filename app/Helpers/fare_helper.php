<?php

use App\Models\FareModel;

if (!function_exists('enrich_routes_with_discounts')) {
    /**
     * Attach standard 'fare' and a 'discounted_fares' array to every route row.
     *
     * @param array $routes    Rows from RouteModel
     * @param array $discounts Active fare_discounts rows (unused now, kept for signature compatibility)
     * @return array
     */
    function enrich_routes_with_discounts(array $routes, array $discounts = []): array
    {
        $fareModel = new FareModel();
        
        foreach ($routes as &$r) {
            $r['fare'] = isset($r['fare']) ? (float) $r['fare'] : 0.00;
            $r['discounted_fares'] = [];

            try {
                $fares = $fareModel
                    ->select('fares.amount, fare_discounts.type, fare_discounts.label, fare_discounts.discount_percent, fare_discounts.is_active')
                    ->join('fare_discounts', 'fare_discounts.id = fares.fare_discount_id')
                    ->where('fares.route_id', $r['id'])
                    ->groupStart()
                        ->where('fare_discounts.type', 'regular')
                        ->orWhere('fare_discounts.is_active', 1)
                    ->groupEnd()
                    ->findAll();
            } catch (\Throwable $e) {
                $fares = [];
            }
                               
            foreach ($fares as $f) {
                if ($f['type'] === 'regular') {
                    $r['fare'] = (float)$f['amount'];
                } else {
                    $r['discounted_fares'][$f['type']] = [
                        'label'            => $f['label'],
                        'discount_percent' => (float)$f['discount_percent'],
                        'amount'           => (float)$f['amount'],
                    ];
                }
            }
        }
        unset($r);
        return $routes;
    }
}
