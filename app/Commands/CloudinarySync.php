<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CloudinaryService;

class CloudinarySync extends BaseCommand
{
    protected $group       = 'Cloudinary';
    protected $name        = 'cloudinary:sync';
    protected $description = 'Syncs existing local uploaded images to Cloudinary and updates database URLs.';
    protected $usage       = 'cloudinary:sync';

    public function run(array $params)
    {
        $cloudinary = new CloudinaryService();
        if (! $cloudinary->isConfigured()) {
            CLI::error('Cloudinary is not configured or disabled. Please check your .env settings.');
            return;
        }

        CLI::write('Starting Cloudinary media synchronization...', 'yellow');

        $db = \Config\Database::connect();
        $totalMigrated = 0;

        // 1. Sync Vehicles
        if ($db->tableExists('vehicles') && $db->fieldExists('photo', 'vehicles')) {
            $vehicles = $db->table('vehicles')
                ->where('photo IS NOT NULL')
                ->where('photo !=', '')
                ->get()
                ->getResultArray();

            $vehCount = 0;
            foreach ($vehicles as $v) {
                $photo = trim((string) $v['photo']);
                if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
                    continue;
                }

                $localPath = FCPATH . ltrim(str_replace(['\\'], '/', $photo), '/');
                if (is_file($localPath)) {
                    $slug = preg_replace('/[^a-zA-Z0-9]/', '_', $v['plate_number'] ?? 'veh');
                    $cRes = $cloudinary->uploadLocalFile($localPath, 'vehicles', 'veh_' . strtolower($slug) . '_' . time());
                    if ($cRes && ! empty($cRes['secure_url'])) {
                        $db->table('vehicles')->where('id', $v['id'])->update(['photo' => $cRes['secure_url']]);
                        $vehCount++;
                        CLI::write("  ✓ Synced vehicle photo for [{$v['plate_number']}] -> {$cRes['secure_url']}", 'green');
                    }
                }
            }
            $totalMigrated += $vehCount;
            CLI::write("Synced {$vehCount} vehicle photo(s).", 'light_cyan');
        }

        // 2. Sync Vehicle Types
        if ($db->tableExists('vehicle_types') && $db->fieldExists('photo', 'vehicle_types')) {
            $types = $db->table('vehicle_types')
                ->where('photo IS NOT NULL')
                ->where('photo !=', '')
                ->get()
                ->getResultArray();

            $vtCount = 0;
            foreach ($types as $t) {
                $photo = trim((string) $t['photo']);
                if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
                    continue;
                }

                $localPath = FCPATH . ltrim(str_replace(['\\'], '/', $photo), '/');
                if (is_file($localPath)) {
                    $slug = $t['slug'] ?? 'type';
                    $cRes = $cloudinary->uploadLocalFile($localPath, 'vehicle_types', 'vt_' . strtolower($slug) . '_' . time());
                    if ($cRes && ! empty($cRes['secure_url'])) {
                        $db->table('vehicle_types')->where('id', $t['id'])->update(['photo' => $cRes['secure_url']]);
                        $vtCount++;
                        CLI::write("  ✓ Synced vehicle type photo for [{$t['name']}] -> {$cRes['secure_url']}", 'green');
                    }
                }
            }
            $totalMigrated += $vtCount;
            CLI::write("Synced {$vtCount} vehicle type photo(s).", 'light_cyan');
        }

        // 3. Sync Users Profile Avatars
        if ($db->tableExists('users') && $db->fieldExists('profile_image', 'users')) {
            $users = $db->table('users')
                ->where('profile_image IS NOT NULL')
                ->where('profile_image !=', '')
                ->get()
                ->getResultArray();

            $userCount = 0;
            foreach ($users as $u) {
                $img = trim((string) $u['profile_image']);
                if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                    continue;
                }

                $localPath = FCPATH . ltrim(str_replace(['\\'], '/', $img), '/');
                if (is_file($localPath)) {
                    $cRes = $cloudinary->uploadLocalFile($localPath, 'avatars', 'avatar_' . $u['id'] . '_' . time());
                    if ($cRes && ! empty($cRes['secure_url'])) {
                        $db->table('users')->where('id', $u['id'])->update(['profile_image' => $cRes['secure_url']]);
                        $userCount++;
                        CLI::write("  ✓ Synced avatar for [{$u['username']}] -> {$cRes['secure_url']}", 'green');
                    }
                }
            }
            $totalMigrated += $userCount;
            CLI::write("Synced {$userCount} user avatar(s).", 'light_cyan');
        }

        // 4. Sync System Settings Media
        if ($db->tableExists('system_settings') && $db->fieldExists('setting_value', 'system_settings')) {
            $settings = $db->table('system_settings')
                ->where('setting_value IS NOT NULL')
                ->where('setting_value !=', '')
                ->get()
                ->getResultArray();

            $settingCount = 0;
            $mediaKeys = ['app_logo', 'app_background_image', 'app_login_card_image'];
            foreach ($settings as $s) {
                $k = $s['setting_key'];
                $val = trim((string) $s['setting_value']);
                $isMediaKey = in_array($k, $mediaKeys, true) || str_starts_with($k, 'app_bg_slideshow_');

                if ($isMediaKey && ! str_starts_with($val, 'http://') && ! str_starts_with($val, 'https://')) {
                    $localPath = FCPATH . ltrim(str_replace(['\\'], '/', $val), '/');
                    if (is_file($localPath)) {
                        $cRes = $cloudinary->uploadLocalFile($localPath, 'branding', $k . '_' . time());
                        if ($cRes && ! empty($cRes['secure_url'])) {
                            $db->table('system_settings')->where('setting_key', $k)->update(['setting_value' => $cRes['secure_url']]);
                            $settingCount++;
                            CLI::write("  ✓ Synced setting media for [{$k}] -> {$cRes['secure_url']}", 'green');
                        }
                    }
                }
            }
            $totalMigrated += $settingCount;
            CLI::write("Synced {$settingCount} system setting image(s).", 'light_cyan');
        }

        CLI::write("🎉 Synchronization complete! Total of {$totalMigrated} image(s) migrated to Cloudinary.", 'green');
    }
}
