<?php

/** Display clock times in the format used by the current operations role. */
function operations_time(?string $value = null): string
{
    $timestamp = $value === null ? time() : strtotime($value);
    if ($timestamp === false) return 'TBA';
    return date(session()->get('role') === 'staff' ? 'g:i A' : 'H:i', $timestamp);
}

function operations_recorded_departure(?string $value): string
{
    return history_departure_time($value, session()->get('role') === 'staff' ? 'g:i A' : 'H:i');
}

/** Strict clock parsing; AM/PM inputs are enabled only for dispatcher forms. */
function departure_clock_value(?string $value, bool $allowAmPm = false): ?string
{
    $value = trim((string) $value);
    if ($allowAmPm && preg_match('/^(0?[1-9]|1[0-2]):([0-5]\d)\s*(AM|PM)$/i', $value, $parts)) {
        $hour = (int) $parts[1] % 12 + (strtoupper($parts[3]) === 'PM' ? 12 : 0);
        return sprintf('%02d:%s:00', $hour, $parts[2]);
    }
    return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) ? $value . ':00' : null;
}

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

if (! function_exists('app_asset_url')) {
    /** Keep cached public assets in sync with their contents after deployment. */
    function app_asset_url(string $path): string
    {
        static $versions = [];
        $path = ltrim($path, '/');
        if (!array_key_exists($path, $versions)) {
            $file = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $path);
            $versions[$path] = is_file($file) ? substr(hash_file('sha256', $file), 0, 12) : null;
        }

        return base_url($path) . ($versions[$path] !== null ? '?v=' . $versions[$path] : '');
    }
}

if (! function_exists('departure_rule_days')) {
    function departure_rule_days(array $rule): array
    {
        $stored = $rule['days_of_week'] ?? null;
        if ($stored === null || $stored === '') {
            return empty($rule['day_of_week']) ? range(1, 7) : [(int) $rule['day_of_week']];
        }
        $days = array_values(array_unique(array_map('intval', is_array($stored) ? $stored : explode(',', $stored))));
        $days = array_values(array_filter($days, static fn(int $day): bool => $day >= 1 && $day <= 7));
        sort($days);
        return $days;
    }
}

if (! function_exists('departure_rule_day_label')) {
    function departure_rule_day_label(array $rule): string
    {
        $days = departure_rule_days($rule);
        if ($days === range(1, 7)) return 'Every day';
        $names = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        $parts = [];
        for ($i = 0; $i < count($days); $i++) {
            $start = $days[$i];
            $end = $start;
            while (isset($days[$i + 1]) && $days[$i + 1] === $end + 1) $end = $days[++$i];
            $parts[] = $start === $end ? $names[$start] : $names[$start] . '–' . $names[$end];
        }
        return implode(', ', $parts);
    }
}

if (! function_exists('departure_round_scope')) {
    /** Key shared by all rules whose rounds are numbered together. */
    function departure_round_scope(int $terminalId, ?string $destination): string
    {
        return $terminalId . '|' . strtoupper(trim((string) $destination));
    }
}

if (! function_exists('departure_round_choices')) {
    /**
     * Offer configured rounds for this destination and its terminal defaults.
     * The add form offers the first unused number, starting at Round 1.
     */
    function departure_round_choices(array $rules, int $currentRound = 0, ?string $scope = null, bool $allowNew = true): array
    {
        $choices = $currentRound > 0 ? [min(999, $currentRound)] : [];
        $terminalScope = $scope === null ? null : explode('|', $scope, 2)[0] . '|';
        foreach ($rules as $rule) {
            if ($scope !== null && !in_array($rule['round_scope'] ?? null, [$scope, $terminalScope], true)) continue;
            $round = (int) ($rule['round_number'] ?? 1);
            if ($round >= 1 && $round <= 999) $choices[] = $round;
        }
        if ($allowNew) {
            for ($next = 1; $next <= 999; $next++) {
                if (!in_array($next, $choices, true)) { $choices[] = $next; break; }
            }
        }
        $choices = array_values(array_unique($choices));
        sort($choices);
        return $choices;
    }
}

if (! function_exists('history_departure_time')) {
    /** Record history on five-minute clock marks while retaining the actual queue timestamp. */
    function history_departure_time(?string $departure, string $format = 'H:i'): string
    {
        $timestamp = $departure === null || trim($departure) === '' ? false : strtotime($departure);
        if ($timestamp === false) {
            return '—';
        }

        return date($format, (int) (ceil($timestamp / 300) * 300));
    }
}

if (! function_exists('history_departure_date_boundary')) {
    /** Raw timestamps above this boundary round into the selected history date. */
    function history_departure_date_boundary(string $date, bool $nextDay = false): ?string
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) {
            return null;
        }
        $day = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if ($day === false || $day->format('Y-m-d') !== $date) {
            return null;
        }
        if ($nextDay) {
            $day = $day->modify('+1 day');
        }

        return date('Y-m-d H:i:s', $day->getTimestamp() - 300);
    }
}

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

if (! function_exists('media_url')) {
    /**
     * Resolves a media asset URL.
     * If the path is a remote URL (Cloudinary HTTPS or external), returns it directly.
     * Otherwise returns the local base_url path.
     */
    function media_url(?string $path): string
    {
        if (empty($path)) {
            return '';
        }
        $trimmed = trim($path);
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }
        $clean = ltrim(str_replace(['\\'], '/', $trimmed), '/');
        return base_url($clean);
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

        $dbTypes = get_db_vehicle_types();
        if (!empty($dbTypes[$typeKey]['icon'])) {
            $dbIcon = $dbTypes[$typeKey]['icon'];
            // If the database has the legacy unconfigured default 'fa-bus' on a non-bus
            // type (like Van or Jeepney), return the proper distinct icon matching the system.
            if ($dbIcon !== 'fa-bus' || in_array($typeKey, ['minibus', 'bus'], true)) {
                return $dbIcon;
            }
        }

        if (str_contains($typeKey, 'habal') || str_contains($typeKey, 'motor') || str_contains($typeKey, 'bike')) {
            return 'fa-motorcycle';
        }
        if (str_contains($typeKey, 'tri')) {
            return 'fa-motorcycle';
        }
        if (str_contains($typeKey, 'jeep')) {
            return 'fa-truck-front';
        }
        if (str_contains($typeKey, 'van') || str_contains($typeKey, 'shuttle')) {
            return 'fa-van-shuttle';
        }
        if (str_contains($typeKey, 'taxi') || str_contains($typeKey, 'cab')) {
            return 'fa-taxi';
        }
        if (str_contains($typeKey, 'car') || str_contains($typeKey, 'sedan')) {
            return 'fa-car';
        }
        if (str_contains($typeKey, 'minibus')) {
            return 'fa-bus';
        }
        if (str_contains($typeKey, 'bus') || str_contains($typeKey, 'coach')) {
            return 'fa-bus-simple';
        }

        return $icons[$typeKey] ?? 'fa-bus-simple';
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
        if (str_contains($typeKey, 'jeep')) {
            return 'bi-truck';
        }
        if (str_contains($typeKey, 'van') || str_contains($typeKey, 'shuttle')) {
            return 'bi-truck-front';
        }
        if (str_contains($typeKey, 'taxi') || str_contains($typeKey, 'cab')) {
            return 'bi-taxi-front';
        }
        if (str_contains($typeKey, 'car') || str_contains($typeKey, 'sedan')) {
            return 'bi-car-front';
        }
        if (str_contains($typeKey, 'minibus')) {
            return 'bi-bus-front';
        }
        if (str_contains($typeKey, 'bus')) {
            return 'bi-bus-front-fill';
        }

        if (! empty($faIcon)) {
            if (str_contains($faIcon, 'motorcycle') || str_contains($faIcon, 'bicycle')) return 'bi-bicycle';
            if (str_contains($faIcon, 'car')) return 'bi-car-front';
            if (str_contains($faIcon, 'taxi')) return 'bi-taxi-front';
            if (str_contains($faIcon, 'van') || str_contains($faIcon, 'shuttle')) return 'bi-truck-front';
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
            'app_name'               => 'Terminal Queue',
            'app_subtitle'           => 'Monitoring System',
            'acronym'                => 'TQMS',
            'system_title'           => 'Terminal Queue Monitoring System',
            'app_logo'               => null,
            'app_background_image'   => null,
            'app_login_card_image'   => null,
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
            'footer_about_title'     => 'TQMS System',
            'footer_about_text'      => 'Terminal Queue Monitoring System provides real-time tracking of vehicle queues and departure schedules to ensure efficient travel for every passenger.',
            'footer_credit'          => 'Terminal Operations & Management',
            'footer_copyright_text'  => '© {year} {title} ({acronym}). All rights reserved. | {credit}',
            'contact_email'          => '',
            'contact_phone'          => '',
            'contact_address'        => 'Central Public Transit Terminal',
            'login_headline'         => 'Keep every trip on time.',
            'login_subheadline'      => 'gives dispatchers a clear view of every route and its next departure.',
            'login_kicker'           => 'Terminal Operations',
            'login_feature1_title'   => 'Live queue',
            'login_feature1_desc'    => 'See which vehicle is next in line.',
            'login_feature2_title'   => 'Role-based access',
            'login_feature2_desc'    => 'Tools tailored to dispatchers and administrators.',
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

if (! function_exists('managed_content_override')) {
    /**
     * Return a non-empty Superadmin content override, or null to use built-in copy.
     */
    function managed_content_override(string $key): ?string
    {
        $value = get_system_setting($key);
        if ($value === null || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}

if (! function_exists('managed_content_value')) {
    /**
     * Return a content override while retaining the supplied version-controlled fallback.
     */
    function managed_content_value(string $key, string $fallback): string
    {
        return managed_content_override($key) ?? $fallback;
    }
}

if (! function_exists('managed_content_overrides')) {
    /**
     * Build a safe JSON-ready map containing only configured overrides.
     *
     * @param list<string> $keys
     * @return array<string, string>
     */
    function managed_content_overrides(array $keys): array
    {
        $values = [];
        foreach ($keys as $key) {
            $value = managed_content_override($key);
            if ($value !== null) {
                $values[$key] = $value;
            }
        }

        return $values;
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
        return get_system_setting('app_name', 'Terminal Queue') ?: 'Terminal Queue';
    }
}

if (! function_exists('app_subtitle')) {
    /**
     * Terminal subtitle / tagline (e.g., 'Terminal Monitor').
     */
    function app_subtitle(): string
    {
        return get_system_setting('app_subtitle', 'Monitoring System') ?: 'Monitoring System';
    }
}

if (! function_exists('login_copy_setting')) {
    /** Keep existing custom copy while refreshing the original stock wording. */
    function login_copy_setting(string $key, string $newDefault, string $oldDefault): string
    {
        $value = get_system_setting($key, $newDefault);
        return (! $value || $value === $oldDefault) ? $newDefault : $value;
    }
}

if (! function_exists('login_headline')) {
    function login_headline(): string
    {
        return login_copy_setting('login_headline', 'Keep every trip on time.', 'Move every van, jeepney & bus on time.');
    }
}

if (! function_exists('login_headline_html')) {
    function login_headline_html(): string
    {
        $raw = login_headline();
        $words = explode(' ', trim($raw));
        if (count($words) >= 2) {
            $lastTwo = array_splice($words, -2);
            return esc(implode(' ', $words)) . ' <span class="accent">' . esc(implode(' ', $lastTwo)) . '</span>';
        }
        return esc($raw);
    }
}

if (! function_exists('login_subheadline')) {
    function login_subheadline(): string
    {
        return login_copy_setting('login_subheadline', 'gives dispatchers a clear view of every route and its next departure.', 'gives dispatchers a live view of vehicle queues, routes, and departures — so every trip leaves the terminal on schedule.');
    }
}

if (! function_exists('login_kicker')) {
    function login_kicker(): string
    {
        return get_system_setting('login_kicker', 'Terminal Operations') ?: 'Terminal Operations';
    }
}

if (! function_exists('login_feature1_title')) {
    function login_feature1_title(): string
    {
        return login_copy_setting('login_feature1_title', 'Live queue', 'Real-time queue');
    }
}

if (! function_exists('login_feature1_desc')) {
    function login_feature1_desc(): string
    {
        return login_copy_setting('login_feature1_desc', 'See which vehicle is next in line.', 'Live queue and departure status across every route.');
    }
}

if (! function_exists('login_feature2_title')) {
    function login_feature2_title(): string
    {
        return login_copy_setting('login_feature2_title', 'Role-based access', 'Secure & audited');
    }
}

if (! function_exists('login_feature2_desc')) {
    function login_feature2_desc(): string
    {
        return login_copy_setting('login_feature2_desc', 'Tools tailored to dispatchers and administrators.', 'Role-based access with a full activity audit trail.');
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
            if (str_starts_with($customLogo, 'http://') || str_starts_with($customLogo, 'https://')) {
                return $customLogo;
            }
            $relPath = ltrim(str_replace(['\\'], '/', $customLogo), '/');
            if (is_file(FCPATH . $relPath)) {
                $mtime = @filemtime(FCPATH . $relPath);
                return base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
            }
        }
        $relPath = 'images/logo.webp';
        $mtime = is_file(FCPATH . $relPath) ? @filemtime(FCPATH . $relPath) : null;
        return base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
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
            if (str_starts_with($customBg, 'http://') || str_starts_with($customBg, 'https://')) {
                return $customBg;
            }
            $relPath = ltrim(str_replace(['\\'], '/', $customBg), '/');
            if (is_file(FCPATH . $relPath)) {
                $mtime = @filemtime(FCPATH . $relPath);
                return base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
            }
        }
        $relPath = 'images/logo.webp';
        $mtime = is_file(FCPATH . $relPath) ? @filemtime(FCPATH . $relPath) : null;
        return base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
    }
}

if (! function_exists('app_login_card_image')) {
    /**
     * URL to the login hero artwork, using the active system logo by default.
     */
    function app_login_card_image(): string
    {
        $customCard = get_system_setting('app_login_card_image');
        if (! empty($customCard)) {
            if (str_starts_with($customCard, 'http://') || str_starts_with($customCard, 'https://')) {
                return $customCard;
            }
            $relPath = ltrim(str_replace(['\\'], '/', $customCard), '/');
            if (is_file(FCPATH . $relPath)) {
                $mtime = @filemtime(FCPATH . $relPath);
                return base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
            }
        }
        return app_logo();
    }
}

if (! function_exists('app_has_custom_login_card')) {
    /**
     * True if a custom login card illustration exists on disk or remote URL.
     */
    function app_has_custom_login_card(): bool
    {
        $customCard = get_system_setting('app_login_card_image');
        if (! empty($customCard)) {
            if (str_starts_with($customCard, 'http://') || str_starts_with($customCard, 'https://')) {
                return true;
            }
            $relPath = ltrim(str_replace(['\\'], '/', $customCard), '/');
            return is_file(FCPATH . $relPath);
        }
        return false;
    }
}

if (! function_exists('app_full_branding_payload')) {
    /**
     * Unified, full branding payload for WebSocket broadcast and AJAX settings responses.
     */
    function app_full_branding_payload(?string $category = null): array
    {
        $payload = [
            'app_name'              => app_name(),
            'app_subtitle'          => app_subtitle(),
            'acronym'               => app_acronym(),
            'system_title'          => app_system_title(),
            'app_logo'              => app_logo(),
            'app_background_image'  => app_bg_image(),
            'app_login_card_image'  => app_login_card_image(),
            'has_custom_login_card' => app_has_custom_login_card(),
            'app_bg_mode'           => app_bg_mode(),
            'app_bg_slideshow'      => array_values(app_bg_slideshow()),
            'theme_guest_primary'   => get_system_setting('theme_guest_primary', '#C62828'),
            'theme_guest_nav_bg'    => get_system_setting('theme_guest_nav_bg', '#ffffff'),
            'theme_guest_nav_text'  => get_system_setting('theme_guest_nav_text', '#1c2430'),
            'theme_staff_primary'   => get_system_setting('theme_staff_primary', '#15803d'),
            'theme_staff_nav_bg'    => get_system_setting('theme_staff_nav_bg', '#15803d'),
            'theme_staff_nav_text'  => get_system_setting('theme_staff_nav_text', '#ffffff'),
            'theme_admin_primary'   => get_system_setting('theme_admin_primary', '#B71C1C'),
            'theme_admin_nav_bg'    => get_system_setting('theme_admin_nav_bg', '#B71C1C'),
            'theme_admin_nav_text'  => get_system_setting('theme_admin_nav_text', '#ffffff'),
            'contact_phone'         => app_contact_phone(),
            'contact_address'       => app_contact_address(),
            'contact_email'         => app_contact_email(),
            'footer_about_title'    => app_footer_about_title(),
            'footer_about_desc'     => app_footer_about_text(),
            'footer_credit'         => app_footer_credit(),
            'login_headline'        => login_headline(),
            'login_subheadline'     => login_subheadline(),
            'login_kicker'          => login_kicker(),
            'login_feature1_title'  => login_feature1_title(),
            'login_feature1_desc'   => login_feature1_desc(),
            'login_feature2_title'  => login_feature2_title(),
            'login_feature2_desc'   => login_feature2_desc(),
        ];
        if ($category !== null) {
            $payload['category'] = $category;
        }
        return $payload;
    }
}

if (! function_exists('app_acronym')) {
    /**
     * Short system code / acronym (e.g., 'PTTM').
     */
    function app_acronym(): string
    {
        return get_system_setting('acronym', 'TQMS') ?: 'TQMS';
    }
}

if (! function_exists('app_system_title')) {
    /**
     * Full official system title (e.g., 'Palompon Transit Terminal Management System').
     */
    function app_system_title(): string
    {
        return get_system_setting('system_title', 'Terminal Queue Monitoring System') ?: 'Terminal Queue Monitoring System';
    }
}

if (! function_exists('app_footer_about_title')) {
    /**
     * Footer about heading (e.g., 'PTTM System').
     */
    function app_footer_about_title(): string
    {
        return get_system_setting('footer_about_title', 'TQMS System') ?: 'TQMS System';
    }
}

if (! function_exists('app_footer_about_text')) {
    /**
     * Footer description text.
     */
    function app_footer_about_text(): string
    {
        return get_system_setting('footer_about_text', 'Terminal Queue Monitoring System provides real-time tracking of vehicle queues and departure schedules to ensure efficient travel for every passenger.') ?: '';
    }
}

if (! function_exists('app_footer_credit')) {
    /**
     * Municipality or managing agency credit.
     */
    function app_footer_credit(): string
    {
        return get_system_setting('footer_credit', 'Terminal Operations & Management') ?: 'Terminal Operations & Management';
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
     * Return array of background picture URLs for the auth/login slideshow (supporting 1, 2, 3, 4, 5, or more photos).
     */
    function app_bg_slideshow(): array
    {
        $defaultFiles = [
            1 => 'images/bg/bg1_townhall.webp',
            2 => 'images/bg/bg2_aerial_port.webp',
            3 => 'images/bg/bg3_aerial_town.webp',
            4 => 'images/bg/bg4_terminal_exterior.webp',
            5 => 'images/bg/bg5_terminal_bay.webp',
        ];

        // Active slots setting (e.g. '1,2,3,4,5' or '1,2' or '1,3,4')
        $slotsSetting = get_system_setting('app_bg_slideshow_slots');
        if (! empty($slotsSetting)) {
            $rawSlots = explode(',', $slotsSetting);
            $activeSlots = [];
            foreach ($rawSlots as $rs) {
                $val = (int) trim($rs);
                if ($val > 0) {
                    $activeSlots[] = $val;
                }
            }
            $activeSlots = array_values(array_unique($activeSlots));
        } else {
            $activeSlots = [1, 2, 3, 4, 5];
        }

        // Also include any custom slots 6+
        $allSettings = get_all_system_settings();
        foreach ($allSettings as $k => $val) {
            if (preg_match('/^app_bg_slideshow_([6-9]|[1-9][0-9]+)$/', $k, $matches) && ! empty($val)) {
                $sIdx = (int) $matches[1];
                if (! in_array($sIdx, $activeSlots, true)) {
                    $activeSlots[] = $sIdx;
                }
            }
        }

        sort($activeSlots, SORT_NUMERIC);

        $urls = [];
        foreach ($activeSlots as $slotIdx) {
            $custom = get_system_setting("app_bg_slideshow_{$slotIdx}");
            if (! empty($custom)) {
                if (str_starts_with($custom, 'http://') || str_starts_with($custom, 'https://')) {
                    $urls[$slotIdx] = $custom;
                    continue;
                }
                $relPath = ltrim(str_replace(['\\'], '/', $custom), '/');
                if (is_file(FCPATH . $relPath)) {
                    $mtime = @filemtime(FCPATH . $relPath);
                    $urls[$slotIdx] = base_url($relPath) . ($mtime ? '?v=' . $mtime : '');
                    continue;
                }
            }
            if (isset($defaultFiles[$slotIdx])) {
                $urls[$slotIdx] = base_url($defaultFiles[$slotIdx]);
            }
        }

        if (empty($urls)) {
            $urls[1] = base_url('images/bg/bg1_townhall.webp');
        }

        return $urls;
    }
}

if (! function_exists('app_bg_slideshow_css')) {
    /**
     * Dynamically generate CSS animation and background rules for ANY number of slideshow images (1, 2, 3, 4, 5, or more).
     */
    function app_bg_slideshow_css(?array $slides = null, float $targetOpacity = 0.22, string $selector = 'body::after'): string
    {
        if ($slides === null) {
            $slides = app_bg_slideshow();
        }
        $slideList = array_values($slides);
        $count = count($slideList);

        if ($count <= 0) {
            $bg = app_bg_image();
            return "{$selector} { content: ''; position: fixed !important; inset: 0 !important; width: 100vw !important; height: 100vh !important; background-repeat: no-repeat !important; background-position: center center !important; background-size: cover !important; background-image: url('{$bg}') !important; opacity: {$targetOpacity} !important; z-index: 0 !important; pointer-events: none !important; }";
        }

        if ($count === 1) {
            $url = esc($slideList[0]);
            return "{$selector} { content: ''; position: fixed !important; inset: 0 !important; width: 100vw !important; height: 100vh !important; background-repeat: no-repeat !important; background-position: center center !important; background-size: cover !important; background-image: url('{$url}') !important; opacity: {$targetOpacity} !important; z-index: 0 !important; pointer-events: none !important; }";
        }

        $duration = $count * 6; // 6 seconds per slide
        $keyframes = "@keyframes terminalBgSlideshow {\n";

        for ($i = 0; $i < $count; $i++) {
            $url = esc($slideList[$i]);
            $startPct = round(($i / $count) * 100, 1);
            $holdPct  = round((($i + 0.85) / $count) * 100, 1);
            $fadePct  = round((($i + 0.95) / $count) * 100, 1);

            $keyframes .= "    {$startPct}%, {$holdPct}% { background-image: url('{$url}'); opacity: {$targetOpacity}; }\n";
            $keyframes .= "    {$fadePct}% { opacity: 0.03; }\n";
        }
        $keyframes .= "    100% { opacity: {$targetOpacity}; }\n";
        $keyframes .= "}\n";

        $css = "{$selector} {\n";
        $css .= "    content: '';\n";
        $css .= "    position: fixed !important;\n";
        $css .= "    inset: 0 !important;\n";
        $css .= "    width: 100vw !important;\n";
        $css .= "    height: 100vh !important;\n";
        $css .= "    background-repeat: no-repeat !important;\n";
        $css .= "    background-position: center center !important;\n";
        $css .= "    background-size: cover !important;\n";
        // Keep the first photo when lite/reduced-motion rules disable keyframes.
        // A normal declaration lets the full-mode slideshow still change photos.
        $css .= "    background-image: url('" . esc($slideList[0]) . "');\n";
        $css .= "    opacity: {$targetOpacity} !important;\n";
        $css .= "    z-index: 0 !important;\n";
        $css .= "    pointer-events: none !important;\n";
        $css .= "    animation: terminalBgSlideshow {$duration}s infinite ease-in-out !important;\n";
        $css .= "}\n";
        $css .= $keyframes;

        return $css;
    }
}

if (! function_exists('app_contact_phone')) {
    function app_contact_phone(): string
    {
        return get_system_setting('contact_phone', '') ?: '';
    }
}

if (! function_exists('app_contact_address')) {
    function app_contact_address(): string
    {
        return get_system_setting('contact_address', 'Central Public Transit Terminal') ?: '';
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
            'on'            => $onColor,
            'isLight'       => ($brightness > 155),
            'softText'      => ($brightness > 155) ? 'rgba(255, 255, 255, 0.90)' : '#5d6b7e',
            'chipBg'        => ($brightness > 155) ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.18)',
            'divider'       => ($brightness > 155) ? 'rgba(0, 0, 0, 0.12)' : 'rgba(255, 255, 255, 0.22)',
            'activeBadgeBg' => ($brightness > 155) ? 'rgba(0, 0, 0, 0.15)' : 'rgba(255, 255, 255, 0.25)',
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

        $bgMode = app_bg_mode();
        $bgImg = app_bg_image();
        $slides = array_values(app_bg_slideshow());
        $slidesJson = json_encode($slides);

        if ($bgMode === 'single' || count($slides) <= 1) {
            $singleTarget = $bgImg ?: ($slides[0] ?? '');
            $bgLayerCss = <<<BGCSS
/* --- Universal Single/System Background Layer across all pages --- */
body:not(.auth-page)::before {
    content: "" !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: cover !important;
    background-image: url('{$singleTarget}') !important;
    opacity: 0.12 !important;
    z-index: 0 !important;
    pointer-events: none !important;
    animation: none !important;
}
BGCSS;
        } else {
            $bgLayerCss = "/* --- Universal Dynamic Rotating Slideshow Layer across all pages --- */\n" . app_bg_slideshow_css($slides, 0.12, 'body:not(.auth-page)::before');
        }

        $css = <<<CSS
<style id="app-dynamic-themes">
{$bgLayerCss}
body:not(.auth-page) > *:not(.modal):not(.route-average-modal):not(.dropdown-content):not(.sticky-top-wrapper):not(#site-header):not(.ann-modal-overlay):not(.mobile-nav-overlay):not(.sidebar-drawer-overlay):not(#sidebarDrawerOverlay):not(.sidebar-drawer):not(#siteSidebarDrawer):not(aside):not(.support-modal-backdrop):not(.scenery):not(.global-progress-bar):not(#global-progress-bar):not(.footer):not(footer) {
    position: relative;
    z-index: 1;
}

/* --- Guest Portal Dynamic Theme Tokens --- */
:root {
    --guest-primary: {$gp['hex']};
    --staff-primary: {$sp['hex']}; --staff-on-primary: {$sp['on']};
    --admin-primary: {$ap['hex']}; --admin-on-primary: {$ap['on']};
}
:root, body.guest-theme, html body.guest-theme, body:not(.admin-theme):not(.staff-theme) {
    --primary: {$gp['hex']} !important;
    --primary-dark: {$gp['dark']} !important;
    --primary-soft: {$gp['soft']} !important;
    --primary-red: {$gp['hex']} !important;
    --primary-red-dark: {$gp['dark']} !important;
    --primary-red-light: {$gp['soft']} !important;
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
.guest-header,
body.guest-theme header#site-header,
body:not(.admin-theme):not(.staff-theme) header#site-header {
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
}
.guest-header .logo-text p,
body.guest-theme #site-header .logo-text p,
body.guest-theme .logo-section .logo-text p,
body.guest-theme .logo-text p,
body:not(.admin-theme):not(.staff-theme) #site-header .logo-text p,
body:not(.admin-theme):not(.staff-theme) .logo-section .logo-text p {
    color: {$gt['softText']} !important;
    opacity: 0.90;
}
.guest-header .nav-menu > a:not(.login-btn):not(.profile-dropdown-item),
body.guest-theme .nav-menu > a:not(.login-btn):not(.profile-dropdown-item),
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn):not(.profile-dropdown-item) {
    color: {$gt['softText']} !important;
}
.guest-header .nav-menu > a:not(.login-btn):not(.profile-dropdown-item) i,
body.guest-theme .nav-menu > a:not(.login-btn):not(.profile-dropdown-item) i,
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn):not(.profile-dropdown-item) i {
    color: {$gt['softText']} !important;
}
.guest-header .nav-menu > a:not(.login-btn):hover,
body.guest-theme .nav-menu > a:not(.login-btn):hover,
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn):hover {
    color: {$gp['hex']} !important;
    background: {$gp['softAlpha']} !important;
}
.guest-header .nav-menu > a:not(.login-btn):hover i,
body.guest-theme .nav-menu > a:not(.login-btn):hover i,
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn):hover i {
    color: {$gp['hex']} !important;
}
.guest-header .nav-menu > a:not(.login-btn).active,
body.guest-theme .nav-menu > a:not(.login-btn).active,
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn).active {
    color: {$gp['hex']} !important;
    background: {$gp['softAlpha']} !important;
    font-weight: 700;
}
.guest-header .nav-menu > a:not(.login-btn).active i,
body.guest-theme .nav-menu > a:not(.login-btn).active i,
body:not(.admin-theme):not(.staff-theme) .nav-menu > a:not(.login-btn).active i {
    color: {$gp['hex']} !important;
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
@media (max-width: 900px) {
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

/* Universal Search Clear Button Protection (keeps icon clean, transparent, and non-pill) */
body.guest-theme .search-bar .guest-clear-search-btn,
body:not(.admin-theme):not(.staff-theme) .search-bar .guest-clear-search-btn,
.search-bar .guest-clear-search-btn,
.guest-clear-search-btn,
.btn-clear-search,
.fare-search-clear {
    background: transparent !important;
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 26px !important;
    height: 26px !important;
    min-width: 26px !important;
    color: #94a3b8 !important;
    -webkit-text-fill-color: #94a3b8 !important;
    border-radius: 50% !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
}
body.guest-theme .search-bar .guest-clear-search-btn:hover,
body:not(.admin-theme):not(.staff-theme) .search-bar .guest-clear-search-btn:hover,
.search-bar .guest-clear-search-btn:hover,
.guest-clear-search-btn:hover,
.btn-clear-search:hover,
.fare-search-clear:hover {
    color: #475569 !important;
    -webkit-text-fill-color: #475569 !important;
    background: rgba(0, 0, 0, 0.05) !important;
    background-color: rgba(0, 0, 0, 0.05) !important;
}

/* Guest / Public Portal Dynamic Button, Filter & Search Accent Overrides */
body.guest-theme .search-bar button:not(.guest-clear-search-btn),
body:not(.admin-theme):not(.staff-theme) .search-bar button:not(.guest-clear-search-btn),
body.guest-theme .filter-btn,
body:not(.admin-theme):not(.staff-theme) .filter-btn,
body.guest-theme .btn-primary,
body:not(.admin-theme):not(.staff-theme) .btn-primary,
body.guest-theme .btn-hero-action:not(.btn-hero-outline),
body:not(.admin-theme):not(.staff-theme) .btn-hero-action:not(.btn-hero-outline) {
    background: {$gp['hex']} !important;
    background-color: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
    color: {$gp['on']} !important;
    -webkit-text-fill-color: {$gp['on']} !important;
}
body.guest-theme .search-bar button:not(.guest-clear-search-btn):hover,
body:not(.admin-theme):not(.staff-theme) .search-bar button:not(.guest-clear-search-btn):hover,
body.guest-theme .filter-btn:hover,
body:not(.admin-theme):not(.staff-theme) .filter-btn:hover,
body.guest-theme .btn-primary:hover,
body:not(.admin-theme):not(.staff-theme) .btn-primary:hover,
body.guest-theme .btn-hero-action:not(.btn-hero-outline):hover,
body:not(.admin-theme):not(.staff-theme) .btn-hero-action:not(.btn-hero-outline):hover {
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

/* Guest Filter Chips & Route Chips (inactive, hover, active, active hover) */
body.guest-theme .filter-chip:not(.active),
body:not(.admin-theme):not(.staff-theme) .filter-chip:not(.active),
body.guest-theme .route-chip:not(.active),
body:not(.admin-theme):not(.staff-theme) .route-chip:not(.active),
body.guest-theme .rules-route-chip:not(.active),
body:not(.admin-theme):not(.staff-theme) .rules-route-chip:not(.active) {
    background: #ffffff;
    border-color: #e2e8f0;
    color: #475569;
}
body.guest-theme .filter-chip:not(.active) .chip-count,
body:not(.admin-theme):not(.staff-theme) .filter-chip:not(.active) .chip-count,
body.guest-theme .route-chip:not(.active) .chip-count,
body:not(.admin-theme):not(.staff-theme) .route-chip:not(.active) .chip-count,
body.guest-theme .rules-route-chip:not(.active) .chip-count,
body:not(.admin-theme):not(.staff-theme) .rules-route-chip:not(.active) .chip-count {
    background: #e2e8f0;
    color: #475569;
    -webkit-text-fill-color: #475569;
}

body.guest-theme .rules-route-chip:hover:not(.active),
body:not(.admin-theme):not(.staff-theme) .rules-route-chip:hover:not(.active),
body.guest-theme .filter-chip:hover:not(.active),
body:not(.admin-theme):not(.staff-theme) .filter-chip:hover:not(.active),
body.guest-theme .route-chip:hover:not(.active),
body:not(.admin-theme):not(.staff-theme) .route-chip:hover:not(.active) {
    border-color: {$gp['hex']} !important;
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
    background: {$gp['softAlpha']} !important;
}
body.guest-theme .filter-chip:hover:not(.active) .chip-count,
body:not(.admin-theme):not(.staff-theme) .filter-chip:hover:not(.active) .chip-count,
body.guest-theme .route-chip:hover:not(.active) .chip-count,
body:not(.admin-theme):not(.staff-theme) .route-chip:hover:not(.active) .chip-count,
body.guest-theme .rules-route-chip:hover:not(.active) .chip-count,
body:not(.admin-theme):not(.staff-theme) .rules-route-chip:hover:not(.active) .chip-count {
    background: {$gp['soft']} !important;
    color: {$gp['dark']} !important;
    -webkit-text-fill-color: {$gp['dark']} !important;
}

body.guest-theme .filter-chip.active,
body:not(.admin-theme):not(.staff-theme) .filter-chip.active,
body.guest-theme .route-chip.active,
body:not(.admin-theme):not(.staff-theme) .route-chip.active,
body.guest-theme .rules-route-chip.active,
body:not(.admin-theme):not(.staff-theme) .rules-route-chip.active {
    background: {$gp['hex']} !important;
    background-color: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
    color: {$gp['on']} !important;
    -webkit-text-fill-color: {$gp['on']} !important;
    box-shadow: 0 4px 12px {$gp['soft']} !important;
}
body.guest-theme .filter-chip.active:hover,
body:not(.admin-theme):not(.staff-theme) .filter-chip.active:hover,
body.guest-theme .route-chip.active:hover,
body:not(.admin-theme):not(.staff-theme) .route-chip.active:hover,
body.guest-theme .rules-route-chip.active:hover,
body:not(.admin-theme):not(.staff-theme) .rules-route-chip.active:hover {
    background: {$gp['dark']} !important;
    background-color: {$gp['dark']} !important;
    border-color: {$gp['dark']} !important;
    color: {$gp['on']} !important;
    -webkit-text-fill-color: {$gp['on']} !important;
}
body.guest-theme .filter-chip.active .chip-count,
body:not(.admin-theme):not(.staff-theme) .filter-chip.active .chip-count,
body.guest-theme .route-chip.active .chip-count,
body:not(.admin-theme):not(.staff-theme) .route-chip.active .chip-count,
body.guest-theme .rules-route-chip.active .chip-count,
body:not(.admin-theme):not(.staff-theme) .rules-route-chip.active .chip-count {
    background: {$gp['activeBadgeBg']} !important;
    color: {$gp['on']} !important;
    -webkit-text-fill-color: {$gp['on']} !important;
}

/* Guest Cards & Interactive Element Hover/Focus Highlights */
body.guest-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type])::before,
body:not(.admin-theme):not(.staff-theme) .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type])::before {
    background: linear-gradient(90deg, {$gp['hex']}, {$gp['dark']}) !important;
}
body.guest-theme .stat-card:hover,
body:not(.admin-theme):not(.staff-theme) .stat-card:hover,
body.guest-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type]):hover,
body:not(.admin-theme):not(.staff-theme) .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type]):hover,
body.guest-theme .modern-card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body:not(.admin-theme):not(.staff-theme) .modern-card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body.guest-theme .card-modern:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body:not(.admin-theme):not(.staff-theme) .card-modern:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body.guest-theme .card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body:not(.admin-theme):not(.staff-theme) .card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body.guest-theme .schedule-card:hover,
body:not(.admin-theme):not(.staff-theme) .schedule-card:hover,
body.guest-theme .filter-box:hover,
body:not(.admin-theme):not(.staff-theme) .filter-box:hover {
    border-color: {$gp['soft']} !important;
    box-shadow: 0 10px 25px {$gp['soft']} !important;
}
body.guest-theme .stat-card-link:not(.stat-card),
body:not(.admin-theme):not(.staff-theme) .stat-card-link:not(.stat-card) {
    color: {$gp['hex']} !important;
}
body.guest-theme .stat-card-link:not(.stat-card):hover,
body:not(.admin-theme):not(.staff-theme) .stat-card-link:not(.stat-card):hover {
    color: {$gp['dark']} !important;
}
body.guest-theme .card-header-modern .card-title-modern i:first-child:not(.text-danger):not(.text-warning):not(.text-success),
body.guest-theme .modern-card-header .modern-card-title i:first-child:not(.text-danger):not(.text-warning):not(.text-success),
body.guest-theme .schedule-card .card-header h3 i:first-child:not(.text-danger):not(.text-warning):not(.text-success),
body:not(.admin-theme):not(.staff-theme) .card-header-modern .card-title-modern i:first-child:not(.text-danger):not(.text-warning):not(.text-success),
body:not(.admin-theme):not(.staff-theme) .modern-card-header .modern-card-title i:first-child:not(.text-danger):not(.text-warning):not(.text-success),
body:not(.admin-theme):not(.staff-theme) .schedule-card .card-header h3 i:first-child:not(.text-danger):not(.text-warning):not(.text-success) {
    color: {$gp['hex']} !important;
}
body.guest-theme .count-badge,
body:not(.admin-theme):not(.staff-theme) .count-badge {
    background: {$gp['soft']} !important;
    color: {$gp['hex']} !important;
}
body.guest-theme .stat-card-link:focus,
body.guest-theme .stat-card-button:focus,
body:not(.admin-theme):not(.staff-theme) .stat-card-link:focus,
body:not(.admin-theme):not(.staff-theme) .stat-card-button:focus {
    outline: none !important;
    box-shadow: 0 0 0 3px {$gp['soft']}, var(--shadow-md) !important;
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
body:not(.admin-theme):not(.staff-theme) .btn-outline-primary,
body.guest-theme .btn-hero-outline,
body:not(.admin-theme):not(.staff-theme) .btn-hero-outline {
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
    background: transparent !important;
}
body.guest-theme .btn-outline-primary:hover,
body:not(.admin-theme):not(.staff-theme) .btn-outline-primary:hover,
body.guest-theme .btn-hero-outline:hover,
body:not(.admin-theme):not(.staff-theme) .btn-hero-outline:hover {
    background-color: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
    color: {$gp['on']} !important;
    -webkit-text-fill-color: {$gp['on']} !important;
}
body.guest-theme .guide-pill-btn.active,
body:not(.admin-theme):not(.staff-theme) .guide-pill-btn.active {
    background: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
    color: {$gp['on']} !important;
    box-shadow: 0 4px 12px {$gp['soft']} !important;
}
body.guest-theme .pagination .page-item.active .page-link,
body:not(.admin-theme):not(.staff-theme) .pagination .page-item.active .page-link {
    background-color: {$gp['hex']} !important;
    border-color: {$gp['hex']} !important;
    color: {$gp['on']} !important;
}

/* Guest Footer Elements dynamic brand accent */
body.guest-theme footer .f-about h3,
body:not(.admin-theme):not(.staff-theme) footer .f-about h3 {
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
}
body.guest-theme footer .f-links h4::after,
body:not(.admin-theme):not(.staff-theme) footer .f-links h4::after {
    background: {$gp['hex']} !important;
}
body.guest-theme footer .f-links a:hover,
body:not(.admin-theme):not(.staff-theme) footer .f-links a:hover {
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
}
body.guest-theme footer .f-links li i.fa-map-marker-alt,
body.guest-theme footer .f-links li i.fa-phone,
body.guest-theme footer .f-links li i.fa-envelope,
body:not(.admin-theme):not(.staff-theme) footer .f-links li i.fa-map-marker-alt,
body:not(.admin-theme):not(.staff-theme) footer .f-links li i.fa-phone,
body:not(.admin-theme):not(.staff-theme) footer .f-links li i.fa-envelope {
    color: {$gp['hex']} !important;
    -webkit-text-fill-color: {$gp['hex']} !important;
}

/* --- Dispatcher / Staff Dynamic Theme Tokens --- */
body.staff-theme, html body.staff-theme {
    --primary: {$sp['hex']} !important;
    --primary-dark: {$sp['dark']} !important;
    --primary-soft: {$sp['soft']} !important;
    --primary-red: {$sp['hex']} !important;
    --primary-red-dark: {$sp['dark']} !important;
    --primary-red-light: {$sp['soft']} !important;
    --on-primary: {$sp['on']} !important;
    --nav-bg: {$sn['hex']} !important;
    --nav-text: {$st['hex']} !important;
    --nav-text-soft: {$st['softText']} !important;
    --nav-accent: {$st['hex']} !important;
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
    text-shadow: none !important;
}
body.staff-theme .profile-trigger-caret {
    color: {$st['softText']} !important;
    -webkit-text-fill-color: {$st['softText']} !important;
}
body.staff-theme .profile-trigger-btn {
    background: {$sn['chipBg']} !important;
    border-color: {$sn['divider']} !important;
}
body.staff-theme .profile-trigger-btn:hover {
    background: {$sn['chipBg']} !important;
    border-color: {$sn['divider']} !important;
    filter: brightness(0.92);
}
body.staff-theme .user-profile-dropdown {
    border-left-color: {$sn['divider']} !important;
}
body.staff-theme .drawer-brand-subtitle {
    color: var(--primary) !important;
}
body.staff-theme .drawer-nav-item:hover {
    background: var(--primary-soft) !important;
    color: var(--primary-dark) !important;
}
body.staff-theme .drawer-nav-item:hover i {
    color: var(--primary-dark) !important;
}
body.staff-theme .drawer-nav-item.active {
    background: var(--primary-soft) !important;
    color: var(--primary-dark) !important;
}
body.staff-theme .drawer-nav-item.active i {
    color: var(--primary-dark) !important;
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

/* Dispatcher Buttons, Chips & Cards */
body.staff-theme .btn-primary,
body.staff-theme .btn-modern.btn-primary,
body.staff-theme .btn-modern.btn-modern-primary,
body.staff-theme .btn-action-primary {
    background: {$sp['hex']} !important;
    background-color: {$sp['hex']} !important;
    border-color: {$sp['hex']} !important;
    color: {$sp['on']} !important;
    -webkit-text-fill-color: {$sp['on']} !important;
    box-shadow: 0 2px 6px {$sp['soft']} !important;
}
body.staff-theme .btn-primary:hover,
body.staff-theme .btn-modern.btn-primary:hover,
body.staff-theme .btn-modern.btn-modern-primary:hover,
body.staff-theme .btn-action-primary:hover {
    background: {$sp['dark']} !important;
    background-color: {$sp['dark']} !important;
    border-color: {$sp['dark']} !important;
    color: {$sp['on']} !important;
    -webkit-text-fill-color: {$sp['on']} !important;
}
body.staff-theme .btn-outline-primary {
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
    border-color: {$sp['hex']} !important;
    background: transparent !important;
}
body.staff-theme .btn-outline-primary:hover {
    background-color: {$sp['hex']} !important;
    border-color: {$sp['hex']} !important;
    color: {$sp['on']} !important;
    -webkit-text-fill-color: {$sp['on']} !important;
}

body.staff-theme .route-chip:not(.active) {
    background: #ffffff;
    border-color: #cbd5e1;
    color: #475569;
}
body.staff-theme .route-chip:not(.active) .chip-count {
    background: #e2e8f0;
    color: #475569;
    -webkit-text-fill-color: #475569;
}
body.staff-theme .route-chip:hover:not(.active),
body.staff-theme .dep-filter-btn[data-type="all"]:hover:not(.active) {
    border-color: {$sp['hex']} !important;
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
    background: {$sp['softAlpha']} !important;
}
body.staff-theme .route-chip:hover:not(.active) .chip-count,
body.staff-theme .dep-filter-btn[data-type="all"]:hover:not(.active) .dep-chip-count {
    background: {$sp['soft']} !important;
    color: {$sp['dark']} !important;
    -webkit-text-fill-color: {$sp['dark']} !important;
}
body.staff-theme .route-chip.active,
body.staff-theme .dep-filter-btn[data-type="all"].active {
    background: {$sp['hex']} !important;
    background-color: {$sp['hex']} !important;
    border-color: {$sp['hex']} !important;
    color: {$sp['on']} !important;
    -webkit-text-fill-color: {$sp['on']} !important;
    box-shadow: 0 4px 12px {$sp['soft']} !important;
}
body.staff-theme .route-chip.active:hover,
body.staff-theme .dep-filter-btn[data-type="all"].active:hover {
    background: {$sp['dark']} !important;
    background-color: {$sp['dark']} !important;
    border-color: {$sp['dark']} !important;
    color: {$sp['on']} !important;
    -webkit-text-fill-color: {$sp['on']} !important;
}
body.staff-theme .route-chip.active .chip-count,
body.staff-theme .dep-filter-btn[data-type="all"].active .dep-chip-count {
    background: {$sp['activeBadgeBg']} !important;
    color: {$sp['on']} !important;
    -webkit-text-fill-color: {$sp['on']} !important;
}

/* Dispatcher Cards & Highlights (preserving vehicle type and fare colors) */
body.staff-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type])::before,
body.staff-theme .stat-card-modern.success-accent::before {
    background: linear-gradient(90deg, {$sp['hex']}, {$sp['dark']}) !important;
}
body.staff-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type]) .stat-card-icon,
body.staff-theme .stat-card-modern.success-accent .stat-card-icon {
    background: {$sp['soft']} !important;
    color: {$sp['hex']} !important;
}
body.staff-theme .modern-card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body.staff-theme .card-modern:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body.staff-theme .card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body.staff-theme .stat-card:hover,
body.staff-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type]):hover {
    border-color: {$sp['soft']} !important;
    box-shadow: 0 8px 25px {$sp['soft']} !important;
}
body.staff-theme .stat-card-link:not(.stat-card) {
    color: {$sp['hex']} !important;
}
body.staff-theme .stat-card-link:not(.stat-card):hover {
    color: {$sp['dark']} !important;
}
body.staff-theme .modern-card-header .modern-card-title i:not(.text-danger):not(.text-warning):not(.text-success),
body.staff-theme .card-header-modern .card-title-modern i:not(.text-danger):not(.text-warning):not(.text-success),
body.staff-theme .card-header .card-title i:not(.text-danger):not(.text-warning):not(.text-success) {
    color: {$sp['hex']} !important;
}
body.staff-theme .route-breakdown-card {
    border-left-color: {$sp['hex']} !important;
}
body.staff-theme .departure-search-group:focus-within .input-group-text,
body.staff-theme .departure-search-group:focus-within .departure-search-input {
    border-color: {$sp['hex']} !important;
    box-shadow: 0 0 0 3px {$sp['soft']} !important;
}
body.staff-theme .route-filter-label i {
    color: {$sp['hex']} !important;
}
body.staff-theme .pagination .page-item.active .page-link {
    background-color: {$sp['hex']} !important;
    border-color: {$sp['hex']} !important;
    color: {$sp['on']} !important;
}

/* --- Admin & Super Admin Dynamic Theme Tokens --- */
body.admin-theme, html body.admin-theme {
    --primary: {$ap['hex']} !important;
    --primary-dark: {$ap['dark']} !important;
    --primary-soft: {$ap['soft']} !important;
    --primary-red: {$ap['hex']} !important;
    --primary-red-dark: {$ap['dark']} !important;
    --primary-red-light: {$ap['soft']} !important;
    --on-primary: {$ap['on']} !important;
    --sb-primary: {$ap['hex']} !important;
    --sb-primary-hover: {$ap['dark']} !important;
    --nav-bg: {$an['hex']} !important;
    --nav-text: {$at['hex']} !important;
    --nav-text-soft: {$at['softText']} !important;
    --nav-accent: {$at['hex']} !important;
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
    text-shadow: none !important;
}
body.admin-theme .profile-trigger-caret {
    color: {$at['softText']} !important;
    -webkit-text-fill-color: {$at['softText']} !important;
}
body.admin-theme .profile-trigger-btn {
    background: {$an['chipBg']} !important;
    border-color: {$an['divider']} !important;
}
body.admin-theme .profile-trigger-btn:hover {
    background: {$an['chipBg']} !important;
    border-color: {$an['divider']} !important;
    filter: brightness(0.92);
}
body.admin-theme .user-profile-dropdown {
    border-left-color: {$an['divider']} !important;
}
body.admin-theme .drawer-brand-subtitle {
    color: var(--primary) !important;
}
body.admin-theme .drawer-nav-item:hover {
    background: var(--primary-soft) !important;
    color: var(--primary-dark) !important;
}
body.admin-theme .drawer-nav-item:hover i {
    color: var(--primary-dark) !important;
}
body.admin-theme .drawer-nav-item.active {
    background: var(--primary-soft) !important;
    color: var(--primary-dark) !important;
}
body.admin-theme .drawer-nav-item.active i {
    color: var(--primary-dark) !important;
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

/* Admin Buttons, Tabs, Chips & Cards */
body.admin-theme .btn-primary,
body.admin-theme .btn-modern.btn-primary,
body.admin-theme .btn-modern.btn-modern-primary,
body.admin-theme .btn-submit,
body.admin-theme .btn-action-primary {
    background: {$ap['hex']} !important;
    background-color: {$ap['hex']} !important;
    border-color: {$ap['hex']} !important;
    color: {$ap['on']} !important;
    -webkit-text-fill-color: {$ap['on']} !important;
    box-shadow: 0 2px 6px {$ap['soft']} !important;
}
body.admin-theme .btn-primary:hover,
body.admin-theme .btn-modern.btn-primary:hover,
body.admin-theme .btn-modern.btn-modern-primary:hover,
body.admin-theme .btn-submit:hover,
body.admin-theme .btn-action-primary:hover {
    background: {$ap['dark']} !important;
    background-color: {$ap['dark']} !important;
    border-color: {$ap['dark']} !important;
    color: {$ap['on']} !important;
    -webkit-text-fill-color: {$ap['on']} !important;
}
body.admin-theme .btn-outline-primary {
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
    border-color: {$ap['hex']} !important;
    background: transparent !important;
}
body.admin-theme .btn-outline-primary:hover {
    background-color: {$ap['hex']} !important;
    border-color: {$ap['hex']} !important;
    color: {$ap['on']} !important;
    -webkit-text-fill-color: {$ap['on']} !important;
}

body.admin-theme .nav-tabs .nav-link.active,
body.admin-theme .nav-pills .nav-link.active,
body.admin-theme .settings-tab-btn.active,
body.admin-theme .tab-pill.active {
    background-color: {$ap['hex']} !important;
    border-color: {$ap['hex']} !important;
    color: {$ap['on']} !important;
    -webkit-text-fill-color: {$ap['on']} !important;
}
body.admin-theme .btn-filter.active,
body.admin-theme .route-filter-btn.active,
body.admin-theme .admin-chip.active {
    background: {$ap['hex']} !important;
    background-color: {$ap['hex']} !important;
    border-color: {$ap['hex']} !important;
    color: {$ap['on']} !important;
    -webkit-text-fill-color: {$ap['on']} !important;
    box-shadow: 0 3px 10px {$ap['soft']} !important;
}
body.admin-theme .btn-filter.active .chip-count,
body.admin-theme .route-filter-btn.active .chip-count,
body.admin-theme .admin-chip.active .chip-count {
    background: {$ap['activeBadgeBg']} !important;
    color: {$ap['on']} !important;
    -webkit-text-fill-color: {$ap['on']} !important;
}
body.admin-theme .btn-filter:hover:not(.active),
body.admin-theme .route-filter-btn:hover:not(.active),
body.admin-theme .admin-chip:hover:not(.active) {
    border-color: {$ap['hex']} !important;
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
    background: {$ap['softAlpha']} !important;
}
body.admin-theme .btn-filter:hover:not(.active) .chip-count,
body.admin-theme .route-filter-btn:hover:not(.active) .chip-count,
body.admin-theme .admin-chip:hover:not(.active) .chip-count {
    background: {$ap['soft']} !important;
    color: {$ap['dark']} !important;
    -webkit-text-fill-color: {$ap['dark']} !important;
}

/* Admin Cards & Highlights (preserving vehicle type and fare colors) */
body.admin-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type]):not(.blue-accent):not(.gold-accent):not(.success-accent)::before {
    background: linear-gradient(90deg, {$ap['hex']}, {$ap['dark']}) !important;
}
body.admin-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type]):not(.blue-accent):not(.gold-accent):not(.success-accent) .stat-card-icon {
    background: {$ap['soft']} !important;
    color: {$ap['hex']} !important;
}
body.admin-theme .modern-card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body.admin-theme .card-modern:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body.admin-theme .card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover,
body.admin-theme .stat-card:hover,
body.admin-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type]):hover {
    border-color: {$ap['soft']} !important;
    box-shadow: 0 8px 25px {$ap['soft']} !important;
}
body.admin-theme .stat-card-link:not(.stat-card) {
    color: {$ap['hex']} !important;
}
body.admin-theme .stat-card-link:not(.stat-card):hover {
    color: {$ap['dark']} !important;
}
body.admin-theme .modern-card-header .modern-card-title i:not(.text-danger):not(.text-warning):not(.text-success),
body.admin-theme .card-header-modern .card-title-modern i:not(.text-danger):not(.text-warning):not(.text-success),
body.admin-theme .card-header .card-title i:not(.text-danger):not(.text-warning):not(.text-success) {
    color: {$ap['hex']} !important;
}
body.admin-theme .form-control:focus,
body.admin-theme .form-select:focus {
    border-color: {$ap['hex']} !important;
    box-shadow: 0 0 0 3px {$ap['soft']} !important;
}
body.admin-theme .pagination .page-item.active .page-link {
    background-color: {$ap['hex']} !important;
    border-color: {$ap['hex']} !important;
    color: {$ap['on']} !important;
}

/* --- Common Dropdown Menus (Management, Records, etc.) --- */
body.admin-theme .dropdown-content,
body.staff-theme .dropdown-content,
body.admin-theme .nav-menu .dropdown-content,
body.staff-theme .nav-menu .dropdown-content {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15) !important;
    border-radius: 12px !important;
    padding: 6px 0 !important;
    z-index: 100001 !important;
    min-width: 200px !important;
}
body.admin-theme .dropdown-content a,
body.staff-theme .dropdown-content a,
body.admin-theme .nav-menu .dropdown-content a,
body.staff-theme .nav-menu .dropdown-content a {
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
body.staff-theme .dropdown-content a i,
body.admin-theme .nav-menu .dropdown-content a i,
body.staff-theme .nav-menu .dropdown-content a i {
    color: #64748b !important;
    -webkit-text-fill-color: #64748b !important;
    font-size: 14px !important;
    width: 18px !important;
    text-align: center !important;
    opacity: 1 !important;
}
body.admin-theme .dropdown-content a:hover,
body.admin-theme .nav-menu .dropdown-content a:hover {
    background: #f1f5f9 !important;
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
}
body.admin-theme .dropdown-content a:hover i,
body.admin-theme .nav-menu .dropdown-content a:hover i {
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
}
body.admin-theme .dropdown-content a.active,
body.admin-theme .nav-menu .dropdown-content a.active {
    background: {$ap['soft']} !important;
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
    font-weight: 700 !important;
}
body.admin-theme .dropdown-content a.active i,
body.admin-theme .nav-menu .dropdown-content a.active i {
    color: {$ap['hex']} !important;
    -webkit-text-fill-color: {$ap['hex']} !important;
}
body.staff-theme .dropdown-content a:hover,
body.staff-theme .nav-menu .dropdown-content a:hover {
    background: #f1f5f9 !important;
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
}
body.staff-theme .dropdown-content a:hover i,
body.staff-theme .nav-menu .dropdown-content a:hover i {
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
}
body.staff-theme .dropdown-content a.active,
body.staff-theme .nav-menu .dropdown-content a.active {
    background: {$sp['soft']} !important;
    color: {$sp['hex']} !important;
    -webkit-text-fill-color: {$sp['hex']} !important;
    font-weight: 700 !important;
}
body.staff-theme .dropdown-content a.active i,
body.staff-theme .nav-menu .dropdown-content a.active i {
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
<script id="app-dynamic-slideshow-script">
(function() {
    window.applyLiveBgMode = function(mode, bgUrl, slides) {
        mode = mode || 'slideshow';
        bgUrl = bgUrl || '{$bgImg}';
        slides = (Array.isArray(slides) && slides.length) ? slides : {$slidesJson};
        if (!slides || !slides.length) {
            if (bgUrl) slides = [bgUrl];
        }

        var bgStyle = document.getElementById('app-live-bg-overrides');
        if (!bgStyle) {
            bgStyle = document.createElement('style');
            bgStyle.id = 'app-live-bg-overrides';
            document.head.appendChild(bgStyle);
        }

        if (mode === 'single' || slides.length <= 1) {
            var target = bgUrl || (slides.length ? slides[0] : '');
            var rule = "body:not(.auth-page)::before { content: '' !important; position: fixed !important; inset: 0 !important; width: 100vw !important; height: 100vh !important; background-repeat: no-repeat !important; background-position: center center !important; background-size: cover !important; background-image: url('" + target + "') !important; opacity: 0.12 !important; animation: none !important; z-index: 0 !important; pointer-events: none !important; } " +
                       "body::after { content: '' !important; position: fixed !important; inset: 0 !important; width: 100vw !important; height: 100vh !important; background-repeat: no-repeat !important; background-position: center center !important; background-size: cover !important; background-image: url('" + target + "') !important; opacity: 0.28 !important; animation: none !important; z-index: 0 !important; pointer-events: none !important; }";
            bgStyle.textContent = rule;
        } else {
            var count = slides.length;
            var duration = count * 6;
            var kf = "@keyframes terminalBgSlideshowLive { ";
            var kfAuth = "@keyframes terminalBgSlideshowAuthLive { ";
            for (var i = 0; i < count; i++) {
                var sUrl = slides[i];
                var sPct = Math.round((i / count) * 1000) / 10;
                var hPct = Math.round(((i + 0.85) / count) * 1000) / 10;
                var fPct = Math.round(((i + 0.95) / count) * 1000) / 10;
                // NOTE: NEVER use !important inside @keyframes blocks as browsers discard it as invalid CSS!
                kf     += sPct + "%, " + hPct + "% { background-image: url('" + sUrl + "'); opacity: 0.12; } ";
                kf     += fPct + "% { opacity: 0.03; } ";
                kfAuth += sPct + "%, " + hPct + "% { background-image: url('" + sUrl + "'); opacity: 0.22; } ";
                kfAuth += fPct + "% { opacity: 0.03; } ";
            }
            kf     += "100% { opacity: 0.12; } } ";
            kfAuth += "100% { opacity: 0.22; } } ";

            var rule = kf + kfAuth +
                       "body:not(.auth-page)::before { content: '' !important; position: fixed !important; inset: 0 !important; width: 100vw !important; height: 100vh !important; background-repeat: no-repeat !important; background-position: center center !important; background-size: cover !important; opacity: 0.12 !important; animation: terminalBgSlideshowLive " + duration + "s infinite ease-in-out !important; z-index: 0 !important; pointer-events: none !important; } " +
                       "body::after { content: '' !important; position: fixed !important; inset: 0 !important; width: 100vw !important; height: 100vh !important; background-repeat: no-repeat !important; background-position: center center !important; background-size: cover !important; opacity: 0.22 !important; animation: terminalBgSlideshowAuthLive " + duration + "s infinite ease-in-out !important; z-index: 0 !important; pointer-events: none !important; }";
            bgStyle.textContent = rule;
        }
    };

    window._appSlideshowEngine = {
        setMode: function(m, u, s) {
            window.applyLiveBgMode(m, u, s);
        }
    };
})();
</script>
CSS;
        return $css;
    }
}

if (! function_exists('app_has_custom_bg')) {
    function app_has_custom_bg(): bool
    {
        $customBg = get_system_setting('app_background_image');
        if (! empty($customBg)) {
            if (str_starts_with($customBg, 'http://') || str_starts_with($customBg, 'https://')) {
                return true;
            }
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
            if (str_starts_with($customLogo, 'http://') || str_starts_with($customLogo, 'https://')) {
                return true;
            }
            $relPath = ltrim(str_replace(['\\'], '/', $customLogo), '/');
            return is_file(FCPATH . $relPath);
        }
        return false;
    }
}


