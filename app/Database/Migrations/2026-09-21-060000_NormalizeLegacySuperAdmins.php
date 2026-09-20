<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeLegacySuperAdmins extends Migration
{
    public function up()
    {
        $superAdmins = $this->db->table('users')
            ->select('id, username, email')
            ->where('role', 'super_admin')
            ->get()
            ->getResultArray();

        if (count($superAdmins) < 2) {
            return;
        }

        $legacyDomains = ['@tlm.local', '@ttm.local'];
        $isLegacy = static function (array $user) use ($legacyDomains): bool {
            $identity = strtolower((string) ($user['username'] ?? '') . ' ' . (string) ($user['email'] ?? ''));
            foreach ($legacyDomains as $domain) {
                if (str_contains($identity, $domain)) {
                    return true;
                }
            }

            return false;
        };

        $hasRealSuperAdmin = false;
        foreach ($superAdmins as $superAdmin) {
            if (!$isLegacy($superAdmin)) {
                $hasRealSuperAdmin = true;
                break;
            }
        }

        if (!$hasRealSuperAdmin) {
            return;
        }

        foreach ($superAdmins as $superAdmin) {
            if ($isLegacy($superAdmin)) {
                $this->db->table('users')
                    ->where('id', (int) $superAdmin['id'])
                    ->update([
                        'role' => 'admin',
                        'status' => 'archived',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }
        }
    }

    public function down()
    {
        // Deliberately irreversible: reactivating a known demo account or
        // restoring duplicate privileges would weaken authorization.
    }
}
