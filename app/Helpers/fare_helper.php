<?php

use App\Models\FareModel;
use App\Models\FareDiscountModel;

if (!function_exists('enrich_routes_with_discounts')) {
    /**
     * Attach standard 'fare' and a 'discounted_fares' array to every route row.
     *
     * @param array $routes    Rows from RouteModel
     * @param array $discounts Active fare_discounts rows; loaded by terminal when omitted.
     * @return array
     */
    function enrich_routes_with_discounts(array $routes, array $discounts = []): array
    {
        $fareModel = new FareModel();
        
        $routeIds = array_column($routes, 'id');
        if (empty($routeIds)) {
            return $routes;
        }

        // Older routes may have a regular fare without materialized discount rows.
        // Display their configured terminal discounts without writing on a read.
        if (func_num_args() < 2) {
            $terminalIds = array_values(array_unique(array_filter(array_map(
                static fn(array $route): int => (int) ($route['terminal_id'] ?? 0), $routes
            ))));
            if ($terminalIds) {
                try {
                    $discounts = (new FareDiscountModel())
                        ->whereIn('terminal_id', $terminalIds)
                        ->where('is_active', 1)->where('type !=', 'regular')
                        ->orderBy('type', 'ASC')->findAll();
                } catch (\Throwable $e) {
                    $discounts = [];
                }
            }
        }
        $discountsByTerminal = [];
        foreach ($discounts as $discount) {
            if (($discount['type'] ?? 'regular') === 'regular' || (int) ($discount['is_active'] ?? 0) !== 1) continue;
            $percent = (float) ($discount['discount_percent'] ?? -1);
            if ($percent < 0 || $percent > 100 || !is_finite($percent)) continue;
            $discountsByTerminal[(int) ($discount['terminal_id'] ?? 0)][$discount['type']] = $discount;
        }

        $faresGrouped = [];
        try {
            $faresList = $fareModel
                ->select('fares.route_id, fares.amount, fare_discounts.type, fare_discounts.label, fare_discounts.discount_percent, fare_discounts.is_active')
                ->join('fare_discounts', 'fare_discounts.id = fares.fare_discount_id')
                ->whereIn('fares.route_id', $routeIds)
                ->groupStart()
                    ->where('fare_discounts.type', 'regular')
                    ->orWhere('fare_discounts.is_active', 1)
                ->groupEnd()
                ->findAll();

            foreach ($faresList as $f) {
                $faresGrouped[$f['route_id']][] = $f;
            }
        } catch (\Throwable $e) {
            $faresGrouped = [];
        }

        foreach ($routes as &$r) {
            $r['fare'] = isset($r['fare']) ? (float) $r['fare'] : 0.00;
            $r['discounted_fares'] = [];

            $fares = $faresGrouped[$r['id']] ?? [];
                               
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

            if ($r['fare'] <= 0) continue;
            foreach ($discountsByTerminal[(int) ($r['terminal_id'] ?? 0)] ?? [] as $type => $discount) {
                if (isset($r['discounted_fares'][$type])) continue;
                $r['discounted_fares'][$type] = [
                    'label' => $discount['label'],
                    'discount_percent' => (float) $discount['discount_percent'],
                    'amount' => round($r['fare'] * (1 - ((float) $discount['discount_percent'] / 100)), 2),
                ];
            }
        }
        unset($r);
        return $routes;
    }
}
