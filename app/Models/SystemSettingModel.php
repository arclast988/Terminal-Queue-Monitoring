<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSettingModel extends Model
{
    protected $table            = 'system_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'setting_key',
        'setting_value',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false;

    /**
     * Get a setting by key, with optional fallback.
     */
    public function getSetting(string $key, ?string $default = null): ?string
    {
        $row = $this->where('setting_key', $key)->first();
        return ($row && $row['setting_value'] !== null) ? (string) $row['setting_value'] : $default;
    }

    /**
     * Set/update a setting value by key.
     */
    public function setSetting(string $key, ?string $value): bool
    {
        $existing = $this->where('setting_key', $key)->first();
        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $updated = (bool) $this->update($existing['id'], [
                'setting_value' => $value,
                'updated_at'    => $now,
            ]);
        } else {
            $updated = (bool) $this->insert([
                'setting_key'   => $key,
                'setting_value' => $value,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        // Clear settings cache if available
        if (function_exists('cache')) {
            cache()->delete('system_settings');
        }

        return $updated;
    }

    /**
     * Batch update an array of key => value settings.
     */
    public function setMultiple(array $settings): void
    {
        foreach ($settings as $k => $v) {
            $this->setSetting((string) $k, $v !== null ? (string) $v : null);
        }
    }
}
