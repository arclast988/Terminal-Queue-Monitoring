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

if (! function_exists('vehicle_type_label')) {
    function vehicle_type_label(?string $type): string
    {
        $labels = [
            'jeepney' => 'Jeepney',
            'van' => 'Van',
            'minibus' => 'Minibus',
        ];

        $typeKey = vehicle_type_key($type);
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
    function vehicle_type_badge(?string $type, string $extraClass = ''): string
    {
        $typeKey = vehicle_type_key($type);
        $classes = trim('vehicle-type-chip vehicle-type-' . $typeKey . ' ' . $extraClass);

        return '<span class="' . esc($classes, 'attr') . '">' . esc(vehicle_type_label($typeKey)) . '</span>';
    }
}

if (! function_exists('vehicle_type_image')) {
    function vehicle_type_image(?string $type): string
    {
        $images = [
            'jeepney' => 'jeep.png',
            'van'     => 'van.png',
            'minibus' => 'minibus.png',
        ];
        return $images[vehicle_type_key($type)] ?? 'minibus.png';
    }
}
