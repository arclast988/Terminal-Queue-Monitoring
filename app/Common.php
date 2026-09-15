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
        if ($forceRefresh && $cache) {
            $cache->delete('system_settings');
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
            'theme_guest_primary'    => '#1E40AF',
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
     * Return array of 5 background picture URLs for the auth/login slideshow.
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

if (! function_exists('app_theme_css')) {
    /**
     * Outputs custom dynamic CSS variables for guest, dispatcher, and admin themes.
     */
    function app_theme_css(): string
    {
        $guestPrimary = get_system_setting('theme_guest_primary', '#1E40AF');
        $guestNavBg   = get_system_setting('theme_guest_nav_bg', '#ffffff');
        $guestNavText = get_system_setting('theme_guest_nav_text', '#1c2430');

        $staffPrimary = get_system_setting('theme_staff_primary', '#15803d');
        $staffNavBg   = get_system_setting('theme_staff_nav_bg', '#15803d');
        $staffNavText = get_system_setting('theme_staff_nav_text', '#ffffff');

        $adminPrimary = get_system_setting('theme_admin_primary', '#B71C1C');
        $adminNavBg   = get_system_setting('theme_admin_nav_bg', '#B71C1C');
        $adminNavText = get_system_setting('theme_admin_nav_text', '#ffffff');

        $css = <<<CSS
<style id="app-dynamic-themes">
body.guest-theme, html body.guest-theme {
    --primary: {$guestPrimary} !important;
    --nav-bg: {$guestNavBg} !important;
    --nav-text: {$guestNavText} !important;
    --nav-accent: {$guestPrimary} !important;
    --nav-cta-bg: {$guestPrimary} !important;
}
body.staff-theme, html body.staff-theme {
    --primary: {$staffPrimary} !important;
    --nav-bg: {$staffNavBg} !important;
    --nav-text: {$staffNavText} !important;
    --nav-drawer-bg: {$staffNavBg} !important;
    --nav-cta-text: {$staffPrimary} !important;
}
body.admin-theme, html body.admin-theme {
    --primary: {$adminPrimary} !important;
    --nav-bg: {$adminNavBg} !important;
    --nav-text: {$adminNavText} !important;
    --nav-drawer-bg: {$adminNavBg} !important;
    --nav-cta-text: {$adminPrimary} !important;
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


