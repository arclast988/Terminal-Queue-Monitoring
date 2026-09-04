<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('vehicle_type_key')) {
    function vehicle_type_key(?string $type): string
    {
        $type = strtolower(trim((string) $type));
        $type = preg_replace('/[^a-z0-9_-]+/', '_', $type) ?: '';
        return trim($type, '_-') ?: 'vehicle';
    }
}

if (! function_exists('get_db_vehicle_types')) {
    function get_db_vehicle_types(bool $forceRefresh = false): array
    {
        $cache = function_exists('cache') ? cache() : null;
        if ($forceRefresh && $cache) {
            $cache->delete('db_vehicle_types');
        } elseif ($cache) {
            $fromCache = $cache->get('db_vehicle_types');
            if (is_array($fromCache)) {
                return $fromCache;
            }
        }

        $cached = [];
        if (class_exists('\Config\Database')) {
            try {
                $db = \Config\Database::connect();
                if ($db && $db->tableExists('vehicle_types')) {
                    $rows = $db->table('vehicle_types')->get()->getResultArray();
                    foreach ($rows as $r) {
                        $key = vehicle_type_key($r['slug']);
                        $cached[$key] = [
                            'name'  => $r['name'],
                            'slug'  => $r['slug'],
                            'color' => $r['color'] ?? null,
                            'icon'  => $r['icon'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Return empty array on DB error
            }
        }

        if ($cache && !empty($cached)) {
            $cache->save('db_vehicle_types', $cached, 3600);
        }

        return $cached;
    }
}

if (! function_exists('vehicle_type_label')) {
    function vehicle_type_label(?string $type): string
    {
        $typeKey = vehicle_type_key($type);
        $dbTypes = get_db_vehicle_types();
        if (!empty($dbTypes[$typeKey]['name'])) {
            return $dbTypes[$typeKey]['name'];
        }

        $labels = [
            'jeepney'     => 'Jeepney',
            'van'         => 'Van',
            'minibus'     => 'Minibus',
            'bus'         => 'Bus',
            'tricecle'    => 'Tricecle',
            'tricycle'    => 'Tricycle',
            'habal_habal' => 'Habal Habal',
            'motorcycle'  => 'Motorcycle',
            'taxi'        => 'Taxi',
            'car'         => 'Car',
        ];

        return $labels[$typeKey] ?? ucwords(str_replace(['_', '-'], ' ', $typeKey));
    }
}

if (! function_exists('vehicle_type_class')) {
    function vehicle_type_class(?string $type): string
    {
        return 'vehicle-type-' . vehicle_type_key($type);
    }
}

if (! function_exists('vehicle_type_badge')) {
    function vehicle_type_badge(?string $type, string $extraClass = '', bool $withIcon = true): string
    {
        $typeKey = vehicle_type_key($type);
        $color = vehicle_type_color($typeKey);
        $classes = trim('vehicle-type-chip vehicle-type-' . $typeKey . ' ' . $extraClass);
        $iconHtml = '';
        if ($withIcon) {
            $icon = vehicle_type_icon($typeKey);
            $iconHtml = '<i class="fas ' . esc($icon) . ' me-1" style="font-size: 11px;"></i>';
        }

        $isLight = (contrast_text_color($color) === '#0f172a');
        $textColor = $isLight ? '#0f172a' : $color;
        $bgColor = $isLight ? '#f1f5f9' : ($color . '18');
        $borderColor = $isLight ? '#cbd5e1' : ($color . '44');

        $style = 'background: ' . $bgColor . ' !important; background-color: ' . $bgColor . ' !important; color: ' . $textColor . ' !important; border: 1.5px solid ' . $borderColor . ' !important; font-weight: 800;';

        return '<span class="' . esc($classes, 'attr') . '" style="' . esc($style, 'attr') . '">' . $iconHtml . esc(vehicle_type_label($typeKey)) . '</span>';
    }
}

if (! function_exists('vehicle_type_image')) {
    function vehicle_type_image(?string $type): ?string
    {
        $key = vehicle_type_key($type);
        $images = [
            'jeepney'     => 'jeep.png',
            'jeep'        => 'jeep.png',
            'van'         => 'van.png',
            'minibus'     => 'minibus.png',
            'bus'         => 'bus.png',
            'coach'       => 'bus.png',
            'tricycle'    => 'tricycle.png',
            'tricecle'    => 'tricycle.png',
            'trike'       => 'tricycle.png',
            'motorcycle'  => 'motorcycle.png',
            'motorbike'   => 'motorcycle.png',
            'habal_habal' => 'motorcycle.png',
            'habalhabal'  => 'motorcycle.png',
            'car'         => 'car.png',
            'sedan'       => 'car.png',
            'taxi'        => 'taxi.png',
            'cab'         => 'taxi.png',
        ];

        if (isset($images[$key])) {
            return $images[$key];
        }

        if (str_contains($key, 'habal') || str_contains($key, 'motor') || str_contains($key, 'bike')) {
            return 'motorcycle.png';
        }
        if (str_contains($key, 'tri')) {
            return 'tricycle.png';
        }
        if (str_contains($key, 'bus')) {
            return 'bus.png';
        }
        if (str_contains($key, 'taxi') || str_contains($key, 'cab')) {
            return 'taxi.png';
        }
        if (str_contains($key, 'car') || str_contains($key, 'sedan')) {
            return 'car.png';
        }
        if (str_contains($key, 'van') || str_contains($key, 'shuttle')) {
            return 'van.png';
        }
        if (str_contains($key, 'jeep')) {
            return 'jeep.png';
        }

        return null;
    }
}

if (! function_exists('vehicle_type_color')) {
    function vehicle_type_color(?string $type, ?string $customColor = null): string
    {
        if (! empty($customColor)) {
            return $customColor;
        }

        $typeKey = vehicle_type_key($type);
        $dbTypes = get_db_vehicle_types();
        if (!empty($dbTypes[$typeKey]['color'])) {
            return $dbTypes[$typeKey]['color'];
        }

        $colors = [
            'van'         => '#c62828', // Red
            'jeepney'     => '#1565c0', // Blue
            'minibus'     => '#2e7d32', // Green
            'bus'         => '#ea580c', // Orange
            'tricecle'    => '#7c3aed', // Purple
            'tricycle'    => '#7c3aed', // Purple
            'habal_habal' => '#ea580c', // Orange
            'motorcycle'  => '#059669', // Emerald
            'taxi'        => '#ca8a04', // Yellow/Gold
            'car'         => '#0891b2', // Cyan
        ];

        return $colors[$typeKey] ?? '#ea580c';
    }
}

if (! function_exists('vehicle_type_icon')) {
    function vehicle_type_icon(?string $type, ?string $customIcon = null): string
    {
        if (! empty($customIcon)) {
            return $customIcon;
        }

        $typeKey = vehicle_type_key($type);
        $dbTypes = get_db_vehicle_types();
        if (!empty($dbTypes[$typeKey]['icon'])) {
            return $dbTypes[$typeKey]['icon'];
        }

        $icons = [
            'van'         => 'fa-van-shuttle',
            'jeepney'     => 'fa-truck-front',
            'minibus'     => 'fa-bus',
            'bus'         => 'fa-bus-simple',
            'tricecle'    => 'fa-motorcycle',
            'tricycle'    => 'fa-motorcycle',
            'habal_habal' => 'fa-motorcycle',
            'motorcycle'  => 'fa-motorcycle',
            'taxi'        => 'fa-taxi',
            'car'         => 'fa-car',
        ];

        if (str_contains($typeKey, 'habal') || str_contains($typeKey, 'motor') || str_contains($typeKey, 'bike')) {
            return 'fa-motorcycle';
        }
        if (str_contains($typeKey, 'tri')) {
            return 'fa-motorcycle';
        }
        if (str_contains($typeKey, 'bus')) {
            return 'fa-bus';
        }

        return $icons[$typeKey] ?? 'fa-bus';
    }
}

if (! function_exists('vehicle_type_bi_icon')) {
    function vehicle_type_bi_icon(?string $type, ?string $faIcon = null): string
    {
        $typeKey = vehicle_type_key($type);
        $biIcons = [
            'van'         => 'bi-truck-front',
            'jeepney'     => 'bi-truck',
            'minibus'     => 'bi-bus-front',
            'bus'         => 'bi-bus-front-fill',
            'tricecle'    => 'bi-bicycle',
            'tricycle'    => 'bi-bicycle',
            'habal_habal' => 'bi-bicycle',
            'motorcycle'  => 'bi-bicycle',
            'taxi'        => 'bi-taxi-front',
            'car'         => 'bi-car-front',
        ];

        if (isset($biIcons[$typeKey])) {
            return $biIcons[$typeKey];
        }

        if (str_contains($typeKey, 'habal') || str_contains($typeKey, 'motor') || str_contains($typeKey, 'bike') || str_contains($typeKey, 'tri')) {
            return 'bi-bicycle';
        }

        if (! empty($faIcon)) {
            if (str_contains($faIcon, 'motorcycle') || str_contains($faIcon, 'bicycle')) return 'bi-bicycle';
            if (str_contains($faIcon, 'car')) return 'bi-car-front';
            if (str_contains($faIcon, 'taxi')) return 'bi-taxi-front';
            if (str_contains($faIcon, 'van')) return 'bi-truck-front';
            if (str_contains($faIcon, 'truck')) return 'bi-truck';
            if (str_contains($faIcon, 'bus')) return 'bi-bus-front';
        }

        return 'bi-truck';
    }
}

if (! function_exists('vehicle_type_colors_css')) {
    /**
     * Emit a <style> block overriding --vehicle-{slug} CSS variables with
     * the admin-customized DB colors. This keeps every CSS-var-based
     * icon box, chip, and filter button in sync with vehicle_type_badge()
     * (which uses inline DB colors) without editing each view.
     */
    function vehicle_type_colors_css(): string
    {
        $types = get_db_vehicle_types();
        if (empty($types)) {
            return '';
        }

        $rules = [];
        foreach ($types as $key => $t) {
            $slug = preg_replace('/[^a-z0-9_-]+/', '_', strtolower(trim((string) ($t['slug'] ?? $key))));
            $slug = trim($slug, '_-');
            if ($slug === '' || empty($t['color'])) {
                continue;
            }
            $color = $t['color'];
            // Basic hex sanity check; fall back to helper default otherwise.
            if (! preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', (string) $color)) {
                $color = vehicle_type_color($slug);
            }
            $rules[] = "--vehicle-{$slug}: {$color}; --vehicle-{$slug}-soft: {$color}18;";
        }

        if (empty($rules)) {
            return '';
        }

        return '<style id="vehicle-type-colors">:root{' . implode('', $rules) . '}</style>';
    }
}

if (! function_exists('contrast_text_color')) {
    function contrast_text_color(?string $hexColor): string
    {
        if (empty($hexColor)) {
            return '#ffffff';
        }
        $hex = ltrim(trim($hexColor), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) < 6) {
            return '#ffffff';
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
        return ($brightness > 155) ? '#0f172a' : '#ffffff';
    }
}

