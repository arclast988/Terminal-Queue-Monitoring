<?php

if (!function_exists('apply_fare_discounts')) {
    /**
     * Build a map of discount-applied fares.
     *
     * @param float $fare      Regular fare from routes.fare
     * @param array $discounts Active rows from fare_discounts (each: type, label, discount_percent)
     * @return array<string, array{label:string, amount:float, discount_percent:float}>
     *         Keyed by discount type (e.g. 'pwd', 'student', 'senior_citizen').
     */
    function apply_fare_discounts(float $fare, array $discounts): array
    {
        $out = [];
        foreach ($discounts as $d) {
            $pct = (float) ($d['discount_percent'] ?? 0);
            $out[$d['type']] = [
                'label'            => $d['label'] ?? $d['type'],
                'discount_percent' => $pct,
                'amount'           => round($fare * (1 - $pct / 100), 2),
            ];
        }
        return $out;
    }
}

if (!function_exists('enrich_routes_with_discounts')) {
    /**
     * Attach a `discounted_fares` array to every route row.
     *
     * @param array $routes    Rows from RouteModel (each must have a 'fare' key)
     * @param array $discounts Active fare_discounts rows
     * @return array
     */
    function enrich_routes_with_discounts(array $routes, array $discounts): array
    {
        foreach ($routes as &$r) {
            $r['discounted_fares'] = apply_fare_discounts((float) ($r['fare'] ?? 0), $discounts);
        }
        unset($r);
        return $routes;
    }
}
