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
                        $photoUrl = null;
                        if (!empty($r['photo'])) {
                            $relPath = ltrim(str_replace(['\\'], '/', $r['photo']), '/');
                            $fc = defined('FCPATH') ? FCPATH : (defined('ROOTPATH') ? rtrim(ROOTPATH, '/\\') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR : '');
                            $fullPath = $fc ? (rtrim($fc, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relPath)) : '';
                            if ($fullPath && is_file($fullPath)) {
                                $mtime = @filemtime($fullPath);
                                $photoUrl = base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
                            } else {
                                $photoUrl = base_url($relPath);
                            }
                        }
                        $cached[$key] = [
                            'name'  => $r['name'],
                            'slug'  => $r['slug'],
                            'color' => $r['color'] ?? null,
                            'icon'  => $r['icon'] ?? null,
                            'photo' => $photoUrl,
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
        // CSS-var-driven so admin color edits apply live (via the
        // vehicle-type-colors style block + WS pushes) without a reload.
        // Hex values remain as fallbacks for first paint / no-JS.
        $var = '--vehicle-' . $typeKey;
        if ($isLight) {
            $bgColor = '#f1f5f9';
            $textColor = '#0f172a';
            $borderColor = '#cbd5e1';
        } else {
            $bgColor = 'var(' . $var . '-soft, ' . $color . '18)';
            $textColor = 'var(' . $var . ', ' . $color . ')';
            $borderColor = 'var(' . $var . ', ' . $color . ')';
        }

        $style = 'background: ' . $bgColor . ' !important; background-color: ' . $bgColor . ' !important; color: ' . $textColor . ' !important; border: 1.5px solid ' . $borderColor . ' !important; font-weight: 800;';

        return '<span class="' . esc($classes, 'attr') . '" data-vtype="' . esc($typeKey, 'attr') . '" style="' . esc($style, 'attr') . '">' . $iconHtml . esc(vehicle_type_label($typeKey)) . '</span>';
    }
}

if (! function_exists('vehicle_type_image')) {
    function vehicle_type_image(?string $type): ?string
    {
        $key = vehicle_type_key($type);
        $dbTypes = get_db_vehicle_types();
        if (!empty($dbTypes[$key]['photo'])) {
            $photoPath = explode('?', $dbTypes[$key]['photo'], 2)[0];
            $base = base_url();
            if (str_starts_with($photoPath, $base)) {
                $photoPath = ltrim(substr($photoPath, strlen($base)), '/');
            }
            return '../' . ltrim($photoPath, '/');
        }

        return null;
    }
}

if (! function_exists('vehicle_type_photo')) {
    function vehicle_type_photo(?string $type): ?string
    {
        if ($type === null || $type === '') {
            return null;
        }
        $key = vehicle_type_key($type);
        $dbTypes = get_db_vehicle_types();
        if (!empty($dbTypes[$key]['photo'])) {
            return $dbTypes[$key]['photo'];
        }
        return null;
    }
}

if (! function_exists('vehicle_resolved_photo')) {
    /**
     * Resolves the display photo for a vehicle.
     * Highest priority: The individual vehicle's uploaded photo (never overridden by vehicle type).
     * Fallback: The vehicle type's assigned photo.
     * Return null if neither exists.
     */
    function vehicle_resolved_photo($vehicleOrQueueItem, ?string $fallbackType = null): ?string
    {
        $vehPhoto = null;
        if (is_array($vehicleOrQueueItem)) {
            $vehPhoto = $vehicleOrQueueItem['vehicle_photo'] ?? $vehicleOrQueueItem['photo'] ?? null;
            if (empty($fallbackType)) {
                $fallbackType = $vehicleOrQueueItem['vehicle_type'] ?? $vehicleOrQueueItem['type'] ?? null;
            }
        } elseif (is_string($vehicleOrQueueItem) && !empty($vehicleOrQueueItem)) {
            $vehPhoto = $vehicleOrQueueItem;
        }

        if (!empty($vehPhoto)) {
            $photoPath = trim((string)$vehPhoto);
            if (str_starts_with($photoPath, 'http://') || str_starts_with($photoPath, 'https://')) {
                return $photoPath;
            }
            return base_url(ltrim($photoPath, '/'));
        }

        if (!empty($fallbackType)) {
            $typePhoto = vehicle_type_photo($fallbackType);
            if (!empty($typePhoto)) {
                if (str_starts_with($typePhoto, 'http://') || str_starts_with($typePhoto, 'https://')) {
                    return $typePhoto;
                }
                return base_url(ltrim($typePhoto, '/'));
            }
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

if (! function_exists('passenger_color_class')) {
    /**
     * Returns the CSS class for the current passenger count based on vehicle capacity percentage:
     * < 50%  -> Green   (passenger-color-green)
     * 50-69% -> Yellow  (passenger-color-yellow)
     * 70-89% -> Orange  (passenger-color-orange)
     * >= 90% -> Red     (passenger-color-red)
     */
    function passenger_color_class(int $count, int $capacity): string
    {
        if ($capacity <= 0) {
            return 'passenger-color-green';
        }
        $percent = ($count / $capacity) * 100;
        if ($percent >= 90) {
            return 'passenger-color-red';
        }
        if ($percent >= 70) {
            return 'passenger-color-orange';
        }
        if ($percent >= 50) {
            return 'passenger-color-yellow';
        }
        return 'passenger-color-green';
    }
}

/* ==========================================================================
   System Branding & Settings Master Helpers
   ========================================================================== */

if (! function_exists('get_all_system_settings')) {
    /**
     * Retrieve all system settings as a key-value dictionary with caching.
     */
    function get_all_system_settings(bool $forceRefresh = false): array
    {
        static $inMemoryCache = null;
        if (! $forceRefresh && $inMemoryCache !== null) {
            return $inMemoryCache;
        }

        $cache = function_exists('cache') ? cache() : null;
        if ($forceRefresh) {
            $inMemoryCache = null;
            if ($cache) {
                $cache->delete('system_settings');
            }
        } elseif (! $forceRefresh && $cache) {
            $fromCache = $cache->get('system_settings');
            if (is_array($fromCache)) {
                $inMemoryCache = $fromCache;
                return $fromCache;
            }
        }

        $defaults = [
            'app_name'               => 'Palompon Transit',
            'app_subtitle'           => 'Terminal Monitor',
            'acronym'                => 'PTTM',
            'system_title'           => 'Palompon Transit Terminal Management System',
            'app_logo'               => null,
            'app_background_image'   => null,
            'theme_guest_primary'    => '#C62828',
            'theme_guest_nav_bg'     => '#ffffff',
            'theme_guest_nav_text'   => '#1c2430',
            'theme_staff_primary'    => '#15803d',
            'theme_staff_nav_bg'     => '#15803d',
            'theme_staff_nav_text'   => '#ffffff',
            'theme_admin_primary'    => '#B71C1C',
            'theme_admin_nav_bg'     => '#B71C1C',
            'theme_admin_nav_text'   => '#ffffff',
            'app_bg_mode'            => 'slideshow',
            'app_bg_slideshow_1'     => null,
            'app_bg_slideshow_2'     => null,
            'app_bg_slideshow_3'     => null,
            'app_bg_slideshow_4'     => null,
            'app_bg_slideshow_5'     => null,
            'footer_about_title'     => 'PTTM System',
            'footer_about_text'      => 'Palompon Transit Terminal Management System provides real-time tracking of vehicle queues and departure schedules to ensure efficient travel for every passenger.',
            'footer_credit'          => 'Municipality of Palompon, Leyte',
            'footer_copyright_text'  => '© {year} {title} ({acronym}). All rights reserved. | {credit}',
            'contact_email'          => '',
            'contact_phone'          => '(053) 555-8376 / 338-2022',
            'contact_address'        => 'Palompon Transit Terminal, Rizal St., Palompon, Leyte 6538',
            'log_retention_days'       => '60',
            'departure_retention_days' => '60',
            'vehicle_cooldown_minutes' => '30',
        ];

        $settings = $defaults;

        if (class_exists('\Config\Database')) {
            try {
                $db = \Config\Database::connect();
                if ($db && $db->tableExists('system_settings')) {
                    $rows = $db->table('system_settings')->get()->getResultArray();
                    foreach ($rows as $r) {
                        if (isset($r['setting_key'])) {
                            $settings[$r['setting_key']] = $r['setting_value'];
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Return defaults on DB connection or query error
            }
        }

        if ($cache) {
            $cache->save('system_settings', $settings, 3600);
        }

        $inMemoryCache = $settings;
        return $settings;
    }
}

if (! function_exists('get_system_setting')) {
    /**
     * Retrieve a specific system setting by key.
     */
    function get_system_setting(string $key, ?string $default = null): ?string
    {
        $settings = get_all_system_settings();
        if (array_key_exists($key, $settings) && $settings[$key] !== null) {
            return (string) $settings[$key];
        }
        return $default;
    }
}

if (! function_exists('vehicle_cooldown_minutes')) {
    /**
     * Vehicle cooldown interval before being visible / eligible back in queue management.
     * Default: 30 minutes. Configurable by Superadmin in System Settings.
     */
    function vehicle_cooldown_minutes(): int
    {
        $val = get_system_setting('vehicle_cooldown_minutes', '30');
        return is_numeric($val) && (int) $val >= 0 ? (int) $val : 30;
    }
}

if (! function_exists('log_retention_days')) {
    /**
     * Number of days to retain system audit logs before automated maintenance purge.
     * Default: 60 days. Configurable by Superadmin in System Settings.
     */
    function log_retention_days(): int
    {
        $val = get_system_setting('log_retention_days', '60');
        return is_numeric($val) && (int) $val >= 1 ? (int) $val : 60;
    }
}

if (! function_exists('departure_retention_days')) {
    /**
     * Number of days to retain completed trip departure history before automated maintenance purge.
     * Default: 60 days. Configurable by Superadmin in System Settings.
     */
    function departure_retention_days(): int
    {
        $val = get_system_setting('departure_retention_days', '60');
        return is_numeric($val) && (int) $val >= 1 ? (int) $val : 60;
    }
}

if (! function_exists('app_name')) {
    /**
     * Primary system / transit name (e.g., 'Palompon Transit').
     */
    function app_name(): string
    {
        return get_system_setting('app_name', 'Palompon Transit') ?: 'Palompon Transit';
    }
}

if (! function_exists('app_subtitle')) {
    /**
     * Terminal subtitle / tagline (e.g., 'Terminal Monitor').
     */
    function app_subtitle(): string
    {
        return get_system_setting('app_subtitle', 'Terminal Monitor') ?: 'Terminal Monitor';
    }
}

if (! function_exists('app_logo')) {
    /**
     * URL to the active system logo (custom uploaded or default seal).
     */
    function app_logo(): string
    {
        $customLogo = get_system_setting('app_logo');
        if (! empty($customLogo)) {
            $relPath = ltrim(str_replace(['\\'], '/', $customLogo), '/');
            if (is_file(FCPATH . $relPath)) {
                $mtime = @filemtime(FCPATH . $relPath);
                return base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
            }
        }
        return base_url('images/9HFScgVg_400x400.png');
    }
}

if (! function_exists('app_bg_image')) {
    /**
     * URL to the active background / hero picture (custom uploaded or default artwork).
     */
    function app_bg_image(): string
    {
        $customBg = get_system_setting('app_background_image');
        if (! empty($customBg)) {
            $relPath = ltrim(str_replace(['\\'], '/', $customBg), '/');
            if (is_file(FCPATH . $relPath)) {
                $mtime = @filemtime(FCPATH . $relPath);
                return base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
            }
        }
        return base_url('images/system bg image.png');
    }
}

if (! function_exists('app_acronym')) {
    /**
     * Short system code / acronym (e.g., 'PTTM').
     */
    function app_acronym(): string
    {
        return get_system_setting('acronym', 'PTTM') ?: 'PTTM';
    }
}

if (! function_exists('app_system_title')) {
    /**
     * Full official system title (e.g., 'Palompon Transit Terminal Management System').
     */
    function app_system_title(): string
    {
        return get_system_setting('system_title', 'Palompon Transit Terminal Management System') ?: 'Palompon Transit Terminal Management System';
    }
}

if (! function_exists('app_footer_about_title')) {
    /**
     * Footer about heading (e.g., 'PTTM System').
     */
    function app_footer_about_title(): string
    {
        return get_system_setting('footer_about_title', 'PTTM System') ?: 'PTTM System';
    }
}

if (! function_exists('app_footer_about_text')) {
    /**
     * Footer description text.
     */
    function app_footer_about_text(): string
    {
        return get_system_setting('footer_about_text', 'Palompon Transit Terminal Management System provides real-time tracking of vehicle queues and departure schedules to ensure efficient travel for every passenger.') ?: '';
    }
}

if (! function_exists('app_footer_credit')) {
    /**
     * Municipality or managing agency credit.
     */
    function app_footer_credit(): string
    {
        return get_system_setting('footer_credit', 'Municipality of Palompon, Leyte') ?: 'Municipality of Palompon, Leyte';
    }
}

if (! function_exists('app_footer_copyright')) {
    /**
     * Render the official system copyright & municipal attribution notice.
     * Supports template variables: {year}, {title}, {acronym}, {credit}.
     */
    function app_footer_copyright(): string
    {
        $custom = get_system_setting('footer_copyright_text');
        $year    = date('Y');
        $title   = esc(app_system_title());
        $acronym = esc(app_acronym());
        $credit  = esc(app_footer_credit());

        if (! empty($custom)) {
            $rendered = str_replace(
                ['{year}', '{title}', '{acronym}', '{credit}'],
                [$year, $title, $acronym, $credit],
                esc($custom)
            );
            return htmlspecialchars_decode($rendered, ENT_QUOTES);
        }

        return "&copy; {$year} {$title} ({$acronym}). All rights reserved. | {$credit}";
    }
}

if (! function_exists('app_bg_mode')) {
    /**
     * Get active background mode ('slideshow' or 'single').
     */
    function app_bg_mode(): string
    {
        return get_system_setting('app_bg_mode', 'slideshow') ?: 'slideshow';
    }
}

if (! function_exists('app_bg_slideshow')) {
    /**
     * Return array of background picture URLs for the auth/login slideshow (supporting 5+ photos).
     */
    function app_bg_slideshow(): array
    {
        $defaultFiles = [
            1 => 'images/bg/bg1_townhall.png',
            2 => 'images/bg/bg2_aerial_port.png',
            3 => 'images/bg/bg3_aerial_town.png',
            4 => 'images/bg/bg4_terminal_exterior.png',
            5 => 'images/bg/bg5_terminal_bay.png',
        ];

        $urls = [];
        for ($i = 1; $i <= 5; $i++) {
            $custom = get_system_setting("app_bg_slideshow_{$i}");
            if (! empty($custom)) {
                $relPath = ltrim(str_replace(['\\'], '/', $custom), '/');
                if (is_file(FCPATH . $relPath)) {
                    $mtime = @filemtime(FCPATH . $relPath);
                    $urls[$i] = base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
                    continue;
                }
            }
            $urls[$i] = base_url($defaultFiles[$i]);
        }

        // Additional custom slots (slot 6+)
        $allSettings = get_all_system_settings();
        foreach ($allSettings as $k => $val) {
            if (preg_match('/^app_bg_slideshow_([6-9]|[1-9][0-9]+)$/', $k, $matches) && ! empty($val)) {
                $slotIdx = (int) $matches[1];
                $relPath = ltrim(str_replace(['\\'], '/', $val), '/');
                if (is_file(FCPATH . $relPath)) {
                    $mtime = @filemtime(FCPATH . $relPath);
                    $urls[$slotIdx] = base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
                }
            }
        }

        ksort($urls);
        return $urls;
    }
}

if (! function_exists('app_contact_phone')) {
    function app_contact_phone(): string
    {
        return get_system_setting('contact_phone', '(053) 555-8376 / 338-2022') ?: '';
    }
}

if (! function_exists('app_contact_address')) {
    function app_contact_address(): string
    {
        return get_system_setting('contact_address', 'Palompon Transit Terminal, Rizal St., Palompon, Leyte 6538') ?: '';
    }
}

if (! function_exists('app_contact_email')) {
    function app_contact_email(): string
    {
        return get_system_setting('contact_email', '') ?: '';
    }
}

if (! function_exists('app_theme_color_helper')) {
    /**
     * Internal helper to compute derived color tokens (dark, soft, contrast, alpha).
     */
    function app_theme_color_helper(string $hex, float $darkenFactor = 0.82): array
    {
        $clean = ltrim(trim($hex), '#');
        if (strlen($clean) === 3) {
            $clean = $clean[0] . $clean[0] . $clean[1] . $clean[1] . $clean[2] . $clean[2];
        }
        if (strlen($clean) !== 6 || ! ctype_xdigit($clean)) {
            $clean = 'C62828';
        }

        $r = hexdec(substr($clean, 0, 2));
        $g = hexdec(substr($clean, 2, 2));
        $b = hexdec(substr($clean, 4, 2));

        $darkR = max(0, min(255, (int) round($r * $darkenFactor)));
        $darkG = max(0, min(255, (int) round($g * $darkenFactor)));
        $darkB = max(0, min(255, (int) round($b * $darkenFactor)));

        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
        $onColor = ($brightness > 155) ? '#0f172a' : '#ffffff';

        return [
            'hex'       => '#' . $clean,
            'dark'      => sprintf('#%02x%02x%02x', $darkR, $darkG, $darkB),
            'soft'      => "rgba({$r}, {$g}, {$b}, 0.14)",
            'softAlpha' => "rgba({$r}, {$g}, {$b}, 0.08)",
            'on'        => $onColor,
            'isLight'   => ($brightness > 155),
            'softText'  => ($brightness > 155) ? 'rgba(255, 255, 255, 0.90)' : 'rgba(15, 23, 42, 0.82)',
            'chipBg'    => ($brightness > 155) ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.18)',
            'divider'   => ($brightness > 155) ? 'rgba(0, 0, 0, 0.12)' : 'rgba(255, 255, 255, 0.22)',
        ];
    }
}

if (! function_exists('app_theme_css')) {
    /**
     * Outputs custom dynamic CSS variables for guest, dispatcher, and admin themes.
     */
    function app_theme_css(): string
    {
        $guestPrimary = get_system_setting('theme_guest_primary', '#C62828');
        $guestNavBg   = get_system_setting('theme_guest_nav_bg', '#ffffff');
        $guestNavText = get_system_setting('theme_guest_nav_text', '#1c2430');

        $staffPrimary = get_system_setting('theme_staff_primary', '#15803d');
        $staffNavBg   = get_system_setting('theme_staff_nav_bg', '#15803d');
        $staffNavText = get_system_setting('theme_staff_nav_text', '#ffffff');

        $adminPrimary = get_system_setting('theme_admin_primary', '#B71C1C');
        $adminNavBg   = get_system_setting('theme_admin_nav_bg', '#B71C1C');
        $adminNavText = get_system_setting('theme_admin_nav_text', '#ffffff');

        $gp = app_theme_color_helper($guestPrimary);
        $gn = app_theme_color_helper($guestNavBg);
        $gt = app_theme_color_helper($guestNavText);

        $sp = app_theme_color_helper($staffPrimary);
        $sn = app_theme_color_helper($staffNavBg);
        $st = app_theme_color_helper($staffNavText);

        $ap = app_theme_color_helper($adminPrimary);
        $an = app_theme_color_helper($adminNavBg);
        $at = app_theme_color_helper($adminNavText);

        $css = <<<CSS
<style id="app-dynamic-themes">
/* --- Guest Portal Dynamic Theme Tokens --- */
:root, body.guest-theme, html body.guest-theme, body:not(.admin-theme):not(.staff-theme) {
    --primary: {$gp['hex']} !important;
    --primary-dark: {$gp['dark']} !important;
    --primary-soft: {$gp['soft']} !important;
    --on-primary: {$gp['on']} !important;
    --nav-bg: {$gn['hex']} !important;
    --nav-text: {$gt['hex']} !important;
    --nav-text-soft: {$gt['softText']} !important;
    --nav-accent: {$gp['hex']} !important;
    --nav-cta-bg: {$gp['hex']} !important;
    --nav-cta-text: {$gp['on']} !important;
    --nav-chip-bg: {$gn['chipBg']} !important;
    --nav-divider: {$gn['divider']} !important;
}

/* Guest Header elements responsive to dynamic theme settings */
.guest-header {
    background: {$gn['hex']} !important;
    border-bottom: 1px solid {$gn['divider']} !important;
}
.guest-header .logo-text h1,
body.guest-theme #site-header .logo-text h1,
body.guest-theme .logo-section .logo-text h1,
body.guest-theme .logo-text h1,
body:not(.admin-theme):not(.staff-theme) #site-header .logo-text h1,
body:not(.admin-theme):not(.staff-theme) .logo-section .logo-text h1 {
    color: {$gt['hex']} !important;
    -webkit-text-fill-color: {$gt['hex']} !important;
}
.guest-header .logo-text p,
body.guest-theme #site-header .logo-text p,
body.guest-theme .logo-section .logo-text p,
body.guest-theme .logo-text p,
body:not(.admin-theme):not(.staff-theme) #site-header .logo-text p,
body:not(.admin-theme):not(.staff-theme) .logo-section .logo-text p {
    color: {$gt['softText']} !important;
    -webkit-text-fill-color: {$gt['softText']} !important;
    opacity: 0.90;
}
.guest-header .nav-menu > a:not(.login-btn):not(.profile-dropdown-item),
body.guest-theme .nav-menu > a:not(.login-btn):not(.profile-dropdown-item),
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn):not(.profile-dropdown-item) {
    color: {$gt['hex']} !important;
    -webkit-text-fill-color: {$gt['hex']} !important;
}
.guest-header .nav-menu > a:not(.login-btn):not(.profile-dropdown-item) i,
body.guest-theme .nav-menu > a:not(.login-btn):not(.profile-dropdown-item) i,
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn):not(.profile-dropdown-item) i {
    color: {$gt['softText']} !important;
    -webkit-text-fill-color: {$gt['softText']} !important;
}
.guest-header .nav-menu > a:not(.login-btn):hover,
body.guest-theme .nav-menu > a:not(.login-btn):hover,
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn):hover {
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
    background: {$gp['softAlpha']} !important;
}
.guest-header .nav-menu > a:not(.login-btn):hover i,
body.guest-theme .nav-menu > a:not(.login-btn):hover i,
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn):hover i {
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
}
.guest-header .nav-menu > a:not(.login-btn).active,
body.guest-theme .nav-menu > a:not(.login-btn).active,
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn).active {
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
    background: {$gp['softAlpha']} !important;
    font-weight: 700;
}
.guest-header .nav-menu > a:not(.login-btn).active i,
body.guest-theme .nav-menu > a:not(.login-btn).active i,
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn).active i {
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
}
.guest-header .nav-menu a::after {
    background: {$gp['hex']} !important;
}
.guest-header .nav-menu a.login-btn,
body.guest-theme .nav-menu a.login-btn,
body:not(.admin-theme):not(.staff-theme) .nav-menu a.login-btn {
    background: {$gp['hex']} !important;
    color: {$gp['on']} !important;
    -webkit-text-fill-color: {$gp['on']} !important;
}
.guest-header .nav-menu a.login-btn i,
body.guest-theme .nav-menu a.login-btn i,
body:not(.admin-theme):not(.staff-theme) .nav-menu a.login-btn i {
    color: {$gp['on']} !important;
    -webkit-text-fill-color: {$gp['on']} !important;
}
.guest-header .nav-menu a.login-btn:hover,
body.guest-theme .nav-menu a.login-btn:hover,
body:not(.admin-theme):not(.staff-theme) .nav-menu a.login-btn:hover {
    background: {$gp['dark']} !important;
}
.guest-header .mobile-toggle,
body.guest-theme .nav-hamburger-btn,
body:not(.admin-theme):not(.staff-theme) .nav-hamburger-btn {
    color: {$gt['hex']} !important;
}
.guest-header .mobile-toggle i,
body.guest-theme .nav-hamburger-btn i,
body:not(.admin-theme):not(.staff-theme) .nav-hamburger-btn i {
    color: {$gt['hex']} !important;
    -webkit-text-fill-color: {$gt['hex']} !important;
}
.guest-header .header-info {
    border-left-color: {$gn['divider']} !important;
}
.guest-header .header-clock-pill,
.header-clock-pill {
    color: {$gt['hex']} !important;
}
.guest-header .header-clock-pill span,
.header-clock-pill span {
    color: {$gt['hex']} !important;
}
.guest-header .header-clock-pill i,
.header-clock-pill i {
    color: {$gp['hex']} !important;
}
.breadcrumb-section a,
.guest-header ~ .breadcrumb-section a,
.ann-modal-text a {
    color: {$gp['hex']} !important;
}
.advisory-bar,
.ann-modal-header {
    background: linear-gradient(135deg, {$gp['hex']} 0%, {$gp['dark']} 100%) !important;
}
.advisory-icon {
    color: {$gp['hex']} !important;
}
.ann-bullet-wrap {
    color: {$gp['hex']} !important;
    background: {$gp['soft']} !important;
}
@media (max-width: 1200px) {
    .guest-header .nav-menu {
        background: {$gn['hex']} !important;
        border-left: 1px solid {$gn['divider']} !important;
    }
    .guest-header .nav-menu a:not(.login-btn) {
        background: {$gn['chipBg']} !important;
        color: {$gt['hex']} !important;
    }
    .guest-header .nav-menu a:not(.login-btn).active {
        background: {$gp['soft']} !important;
        color: {$gp['hex']} !important;
    }
}
/* Autocomplete dynamic styles for Public / Guest */
body:not(.admin-theme):not(.staff-theme) .autocomplete-item i,
body:not(.admin-theme):not(.staff-theme) .autocomplete-item .autocomplete-item-text i {
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
}
body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover,
body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item {
    background-color: {$gp['soft']} !important;
    color: {$gp['hex']} !important;
}
body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover span,
body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item span,
body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover .autocomplete-item-text,
body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item .autocomplete-item-text,
body:not(.admin-theme):not(.staff-theme) .autocomplete-item:hover i,
body:not(.admin-theme):not(.staff-theme) .autocomplete-item.active-item i {
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
}
body:not(.admin-theme):not(.staff-theme) .autocomplete-badge {
    background-color: {$gp['soft']} !important;
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
    border: 1px solid {$gp['soft']} !important;
}

/* Guest / Public Portal Dynamic Button, Filter & Search Accent Overrides */
body.guest-theme .search-bar button,
body:not(.admin-theme):not(.staff-theme) .search-bar button,
body.guest-theme .filter-btn,
body:not(.admin-theme):not(.staff-theme) .filter-btn,
body.guest-theme .btn-primary,
body:not(.admin-theme):not(.staff-theme) .btn-primary {
    background: {$gp['hex']} !important;
    background-color: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
    color: {$gp['on']} !important;
    -webkit-text-fill-color: {$gp['on']} !important;
}
body.guest-theme .search-bar button:hover,
body:not(.admin-theme):not(.staff-theme) .search-bar button:hover,
body.guest-theme .filter-btn:hover,
body:not(.admin-theme):not(.staff-theme) .filter-btn:hover,
body.guest-theme .btn-primary:hover,
body:not(.admin-theme):not(.staff-theme) .btn-primary:hover {
    background: {$gp['dark']} !important;
    background-color: {$gp['dark']} !important;
    border-color: {$gp['dark']} !important;
    color: {$gp['on']} !important;
    -webkit-text-fill-color: {$gp['on']} !important;
}
body.guest-theme .search-bar:focus-within,
body:not(.admin-theme):not(.staff-theme) .search-bar:focus-within {
    border-color: {$gp['hex']} !important;
    box-shadow: 0 0 0 4px {$gp['soft']}, var(--shadow-lg) !important;
}
body.guest-theme .rules-route-chip:hover,
body:not(.admin-theme):not(.staff-theme) .rules-route-chip:hover,
body.guest-theme .filter-chip:hover,
body:not(.admin-theme):not(.staff-theme) .filter-chip:hover,
body.guest-theme .route-chip:hover,
body:not(.admin-theme):not(.staff-theme) .route-chip:hover {
    border-color: {$gp['hex']} !important;
    color: {$gp['hex']} !important;
    background: {$gp['softAlpha']} !important;
}
body.guest-theme .filter-chip:hover:not(.active) .chip-count,
body:not(.admin-theme):not(.staff-theme) .filter-chip:hover:not(.active) .chip-count,
body.guest-theme .route-chip:hover:not(.active) .chip-count,
body:not(.admin-theme):not(.staff-theme) .route-chip:hover:not(.active) .chip-count {
    background: {$gp['soft']} !important;
    color: {$gp['hex']} !important;
}
body.guest-theme .filter-badge,
body:not(.admin-theme):not(.staff-theme) .filter-badge {
    color: {$gp['hex']} !important;
}
body.guest-theme .back-link,
body:not(.admin-theme):not(.staff-theme) .back-link {
    color: {$gp['hex']} !important;
    border-color: {$gp['soft']} !important;
}
body.guest-theme .back-link:hover,
body:not(.admin-theme):not(.staff-theme) .back-link:hover {
    background: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
    color: {$gp['on']} !important;
}
body.guest-theme .btn-outline-primary,
body:not(.admin-theme):not(.staff-theme) .btn-outline-primary {
    color: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
}
body.guest-theme .btn-outline-primary:hover,
body:not(.admin-theme):not(.staff-theme) .btn-outline-primary:hover {
    background-color: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
    color: {$gp['on']} !important;
}
body.guest-theme .guide-pill-btn.active,
body:not(.admin-theme):not(.staff-theme) .guide-pill-btn.active {
    background: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
    color: {$gp['on']} !important;
    box-shadow: 0 4px 12px {$gp['soft']} !important;
}
body.guest-theme .btn-hero-action:not(.btn-hero-outline),
body:not(.admin-theme):not(.staff-theme) .btn-hero-action:not(.btn-hero-outline) {
    color: {$gp['hex']} !important;
}
body.guest-theme .btn-hero-action:not(.btn-hero-outline):hover,
body:not(.admin-theme):not(.staff-theme) .btn-hero-action:not(.btn-hero-outline):hover {
    color: {$gp['dark']} !important;
}

/* --- Dispatcher / Staff Dynamic Theme Tokens --- */
body.staff-theme, html body.staff-theme {
    --primary: {$sp['hex']} !important;
    --primary-dark: {$sp['dark']} !important;
    --primary-soft: {$sp['soft']} !important;
    --on-primary: {$sp['on']} !important;
    --nav-bg: {$sn['hex']} !important;
    --nav-text: {$st['hex']} !important;
    --nav-text-soft: {$st['softText']} !important;
    --nav-chip-bg: {$sn['chipBg']} !important;
    --nav-divider: {$sn['divider']} !important;
    --nav-drawer-bg: {$sn['dark']} !important;
    --nav-cta-bg: {$sp['on']} !important;
    --nav-cta-text: {$sp['hex']} !important;
}
body.staff-theme header#site-header {
    background: {$sn['hex']} !important;
    border-bottom: 1px solid {$sn['divider']} !important;
}
body.staff-theme #site-header .logo-text h1,
body.staff-theme .logo-section .logo-text h1,
body.staff-theme .logo-text h1 {
    color: {$st['hex']} !important;
    -webkit-text-fill-color: {$st['hex']} !important;
}
body.staff-theme #site-header .logo-text p,
body.staff-theme .logo-section .logo-text p,
body.staff-theme .logo-text p {
    color: {$st['hex']} !important;
    -webkit-text-fill-color: {$st['hex']} !important;
    opacity: 0.88;
}
body.staff-theme .nav-menu > a:not(.profile-dropdown-item),
body.staff-theme .nav-menu > .dropdown > .dropbtn {
    color: {$st['hex']} !important;
    -webkit-text-fill-color: {$st['hex']} !important;
}
body.staff-theme .nav-menu > a:not(.profile-dropdown-item) i,
body.staff-theme .nav-menu > .dropdown > .dropbtn i {
    color: {$st['hex']} !important;
    -webkit-text-fill-color: {$st['hex']} !important;
    opacity: 0.92;
}
body.staff-theme .nav-menu > a:not(.profile-dropdown-item):hover,
body.staff-theme .nav-menu > .dropdown > .dropbtn:hover {
    background: {$sn['chipBg']} !important;
    color: {$st['hex']} !important;
    -webkit-text-fill-color: {$st['hex']} !important;
}
body.staff-theme .nav-menu > a:not(.profile-dropdown-item):hover i,
body.staff-theme .nav-menu > .dropdown > .dropbtn:hover i {
    color: {$st['hex']} !important;
    -webkit-text-fill-color: {$st['hex']} !important;
    opacity: 1;
}
body.staff-theme .nav-menu > a.active:not(.profile-dropdown-item),
body.staff-theme .nav-menu > .dropdown > .dropbtn.active {
    background: {$sn['chipBg']} !important;
    color: {$st['hex']} !important;
    -webkit-text-fill-color: {$st['hex']} !important;
}
body.staff-theme .nav-menu > a.active:not(.profile-dropdown-item) i,
body.staff-theme .nav-menu > .dropdown > .dropbtn.active i {
    color: {$st['hex']} !important;
    -webkit-text-fill-color: {$st['hex']} !important;
    opacity: 1;
}
body.staff-theme .nav-hamburger-btn {
    color: {$st['hex']} !important;
}
body.staff-theme .nav-hamburger-btn i {
    color: {$st['hex']} !important;
    -webkit-text-fill-color: {$st['hex']} !important;
}
body.staff-theme .profile-trigger-name {
    color: {$st['hex']} !important;
    -webkit-text-fill-color: {$st['hex']} !important;
}
body.staff-theme .profile-trigger-caret {
    color: {$st['softText']} !important;
}
body.staff-theme .profile-trigger-btn {
    background: {$sn['chipBg']} !important;
    border-color: {$sn['divider']} !important;
}
body.staff-theme .user-profile-dropdown {
    border-left-color: {$sn['divider']} !important;
}
body.staff-theme .drawer-brand-subtitle {
    color: {$sp['hex']} !important;
}
body.staff-theme .drawer-nav-item:hover {
    background: {$sp['soft']} !important;
    color: {$sp['hex']} !important;
}
body.staff-theme .drawer-nav-item:hover i {
    color: {$sp['hex']} !important;
}
body.staff-theme .drawer-nav-item.active {
    background: {$sp['soft']} !important;
    color: {$sp['hex']} !important;
}
body.staff-theme .drawer-nav-item.active i {
    color: {$sp['hex']} !important;
}
body.staff-theme .logout-stripe {
    background: linear-gradient(90deg, {$sp['hex']} 0%, {$sp['dark']} 100%) !important;
}
body.staff-theme .logout-btn-confirm {
    background: linear-gradient(135deg, {$sp['hex']} 0%, {$sp['dark']} 100%) !important;
}
body.staff-theme .autocomplete-item i,
body.staff-theme .autocomplete-item .autocomplete-item-text i {
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
}
body.staff-theme .autocomplete-item:hover,
body.staff-theme .autocomplete-item.active-item {
    background-color: {$sp['soft']} !important;
    color: {$sp['hex']} !important;
}
body.staff-theme .autocomplete-badge {
    background-color: {$sp['soft']} !important;
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
    border: 1px solid {$sp['soft']} !important;
}

/* --- Admin & Super Admin Dynamic Theme Tokens --- */
body.admin-theme, html body.admin-theme {
    --primary: {$ap['hex']} !important;
    --primary-dark: {$ap['dark']} !important;
    --primary-soft: {$ap['soft']} !important;
    --on-primary: {$ap['on']} !important;
    --sb-primary: {$ap['hex']} !important;
    --sb-primary-hover: {$ap['dark']} !important;
    --nav-bg: {$an['hex']} !important;
    --nav-text: {$at['hex']} !important;
    --nav-text-soft: {$at['softText']} !important;
    --nav-chip-bg: {$an['chipBg']} !important;
    --nav-divider: {$an['divider']} !important;
    --nav-drawer-bg: {$an['dark']} !important;
    --nav-cta-bg: {$ap['on']} !important;
    --nav-cta-text: {$ap['hex']} !important;
}
body.admin-theme header#site-header {
    background: {$an['hex']} !important;
    border-bottom: 1px solid {$an['divider']} !important;
}
body.admin-theme #site-header .logo-text h1,
body.admin-theme .logo-section .logo-text h1,
body.admin-theme .logo-text h1 {
    color: {$at['hex']} !important;
    -webkit-text-fill-color: {$at['hex']} !important;
}
body.admin-theme #site-header .logo-text p,
body.admin-theme .logo-section .logo-text p,
body.admin-theme .logo-text p {
    color: {$at['hex']} !important;
    -webkit-text-fill-color: {$at['hex']} !important;
    opacity: 0.88;
}
body.admin-theme .nav-menu > a:not(.profile-dropdown-item),
body.admin-theme .nav-menu > .dropdown > .dropbtn {
    color: {$at['hex']} !important;
    -webkit-text-fill-color: {$at['hex']} !important;
}
body.admin-theme .nav-menu > a:not(.profile-dropdown-item) i,
body.admin-theme .nav-menu > .dropdown > .dropbtn i {
    color: {$at['hex']} !important;
    -webkit-text-fill-color: {$at['hex']} !important;
    opacity: 0.92;
}
body.admin-theme .nav-menu > a:not(.profile-dropdown-item):hover,
body.admin-theme .nav-menu > .dropdown > .dropbtn:hover {
    background: {$an['chipBg']} !important;
    color: {$at['hex']} !important;
    -webkit-text-fill-color: {$at['hex']} !important;
}
body.admin-theme .nav-menu > a:not(.profile-dropdown-item):hover i,
body.admin-theme .nav-menu > .dropdown > .dropbtn:hover i {
    color: {$at['hex']} !important;
    -webkit-text-fill-color: {$at['hex']} !important;
    opacity: 1;
}
body.admin-theme .nav-menu > a.active:not(.profile-dropdown-item),
body.admin-theme .nav-menu > .dropdown > .dropbtn.active {
    background: {$an['chipBg']} !important;
    color: {$at['hex']} !important;
    -webkit-text-fill-color: {$at['hex']} !important;
}
body.admin-theme .nav-menu > a.active:not(.profile-dropdown-item) i,
body.admin-theme .nav-menu > .dropdown > .dropbtn.active i {
    color: {$at['hex']} !important;
    -webkit-text-fill-color: {$at['hex']} !important;
    opacity: 1;
}
body.admin-theme .nav-hamburger-btn {
    color: {$at['hex']} !important;
}
body.admin-theme .nav-hamburger-btn i {
    color: {$at['hex']} !important;
    -webkit-text-fill-color: {$at['hex']} !important;
}
body.admin-theme .profile-trigger-name {
    color: {$at['hex']} !important;
    -webkit-text-fill-color: {$at['hex']} !important;
}
body.admin-theme .profile-trigger-caret {
    color: {$at['softText']} !important;
}
body.admin-theme .profile-trigger-btn {
    background: {$an['chipBg']} !important;
    border-color: {$an['divider']} !important;
}
body.admin-theme .user-profile-dropdown {
    border-left-color: {$an['divider']} !important;
}
body.admin-theme .drawer-brand-subtitle {
    color: {$ap['hex']} !important;
}
body.admin-theme .drawer-nav-item:hover {
    background: {$ap['soft']} !important;
    color: {$ap['hex']} !important;
}
body.admin-theme .drawer-nav-item:hover i {
    color: {$ap['hex']} !important;
}
body.admin-theme .drawer-nav-item.active {
    background: {$ap['soft']} !important;
    color: {$ap['hex']} !important;
}
body.admin-theme .drawer-nav-item.active i {
    color: {$ap['hex']} !important;
}
body.admin-theme .logout-stripe {
    background: linear-gradient(90deg, {$ap['hex']} 0%, {$ap['dark']} 100%) !important;
}
body.admin-theme .logout-btn-confirm {
    background: linear-gradient(135deg, {$ap['hex']} 0%, {$ap['dark']} 100%) !important;
}
body.admin-theme .autocomplete-item i,
body.admin-theme .autocomplete-item .autocomplete-item-text i {
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
}
body.admin-theme .autocomplete-item:hover,
body.admin-theme .autocomplete-item.active-item {
    background-color: {$ap['soft']} !important;
    color: {$ap['hex']} !important;
}
body.admin-theme .autocomplete-badge {
    background-color: {$ap['soft']} !important;
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
    border: 1px solid {$ap['soft']} !important;
}

/* --- Common Dropdown Menus (Management, Records, etc.) --- */
body.admin-theme .dropdown-content,
body.staff-theme .dropdown-content {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15) !important;
    border-radius: 12px !important;
    padding: 6px 0 !important;
    z-index: 100001 !important;
    min-width: 200px !important;
}
body.admin-theme .dropdown-content a,
body.staff-theme .dropdown-content a {
    color: #1e293b !important;
    -webkit-text-fill-color: #1e293b !important;
    font-size: 13.5px !important;
    font-weight: 600 !important;
    padding: 10px 18px !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    background: transparent !important;
    text-decoration: none !important;
    transition: background 0.15s ease, color 0.15s ease !important;
}
body.admin-theme .dropdown-content a i,
body.staff-theme .dropdown-content a i {
    color: #64748b !important;
    -webkit-text-fill-color: #64748b !important;
    font-size: 14px !important;
    width: 18px !important;
    text-align: center !important;
    opacity: 1 !important;
}
body.admin-theme .dropdown-content a:hover {
    background: #f1f5f9 !important;
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
}
body.admin-theme .dropdown-content a:hover i {
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
}
body.admin-theme .dropdown-content a.active {
    background: {$ap['soft']} !important;
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
    font-weight: 700 !important;
}
body.admin-theme .dropdown-content a.active i {
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
}
body.staff-theme .dropdown-content a:hover {
    background: #f1f5f9 !important;
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
}
body.staff-theme .dropdown-content a:hover i {
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
}
body.staff-theme .dropdown-content a.active {
    background: {$sp['soft']} !important;
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
    font-weight: 700 !important;
}
body.staff-theme .dropdown-content a.active i {
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
}

/* Nav badge chips for Record / Audit status inside dropdowns */
.nav-badge-chip {
    margin-left: auto;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 9999px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.nav-badge-chip.chip-purple {
    background: #f3e8ff !important;
    color: #7e22ce !important;
    -webkit-text-fill-color: #7e22ce !important;
    border: 1px solid #e9d5ff;
}
.nav-badge-chip.chip-amber {
    background: #fef3c7 !important;
    color: #b45309 !important;
    -webkit-text-fill-color: #b45309 !important;
    border: 1px solid #fde68a;
}
</style>
CSS;
        return $css;
    }
}

if (! function_exists('app_has_custom_bg')) {
    function app_has_custom_bg(): bool
    {
        $customBg = get_system_setting('app_background_image');
        if (! empty($customBg)) {
            $relPath = ltrim(str_replace(['\\'], '/', $customBg), '/');
            return is_file(FCPATH . $relPath);
        }
        return false;
    }
}

if (! function_exists('app_has_custom_logo')) {
    function app_has_custom_logo(): bool
    {
        $customLogo = get_system_setting('app_logo');
        if (! empty($customLogo)) {
            $relPath = ltrim(str_replace(['\\'], '/', $customLogo), '/');
            return is_file(FCPATH . $relPath);
        }
        return false;
    }
}


