<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;

class TransferSuperAdmin extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'admin:transfer-super-admin';
    protected $description = 'Transfer the super admin role to another user. The current super admin is demoted to admin.';
    protected $usage       = 'admin:transfer-super-admin [user_id]';
    protected $arguments   = [
        'user_id' => 'The ID of the user who will become the new super admin',
    ];

    public function run(array $params)
    {
        $userId = $params[0] ?? null;
        if (!$userId || !is_numeric($userId)) {
            CLI::error('Usage: php spark admin:transfer-super-admin <user_id>');
            return;
        }

        $userId = (int) $userId;
        $model = new UserModel();
        $targetUser = $model->find($userId);

        if (!$targetUser) {
            CLI::error("User with ID {$userId} not found.");
            return;
        }

        if ($targetUser['role'] === 'super_admin') {
            CLI::error("User '{$targetUser['username']}' is already a super admin.");
            return;
        }

        // Find the current super admin
        $currentSuperAdmin = $model->where('role', 'super_admin')->first();
        if (!$currentSuperAdmin) {
            CLI::error('No super admin account found. Run the AddSuperAdminRole migration first.');
            return;
        }

        CLI::write('Current super admin: ' . $currentSuperAdmin['username'] . ' (ID: ' . $currentSuperAdmin['id'] . ')', 'yellow');
        CLI::write('Target user:         ' . $targetUser['username'] . ' (ID: ' . $targetUser['id'] . ', role: ' . $targetUser['role'] . ')', 'yellow');
        CLI::write('');

        $confirmed = CLI::prompt('Transfer super admin role? This will demote the current super admin to admin', ['y', 'n']);
        if ($confirmed !== 'y') {
            CLI::write('Transfer cancelled.', 'yellow');
            return;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Demote current super admin to admin
        $model->update($currentSuperAdmin['id'], ['role' => 'admin']);

        // Promote target user to super_admin
        $model->update($userId, ['role' => 'super_admin']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            CLI::error('Transfer failed. Database transaction error.');
            return;
        }

        $model = new UserModel();
        $logModel = new \App\Models\LogModel();
        try {
            $logModel->insert([
                'user_id'  => $currentSuperAdmin['id'],
                'action'   => 'Transfer Super Admin',
                'details'  => 'Super admin transferred from "' . $currentSuperAdmin['username'] . '" (ID: ' . $currentSuperAdmin['id'] . ') to "' . $targetUser['username'] . '" (ID: ' . $userId . ')',
            ]);
        } catch (\Throwable $e) {
        }

        CLI::write('Super admin role has been transferred successfully.', 'green');
        CLI::write('  Previous: ' . $currentSuperAdmin['username'] . ' is now admin', 'cyan');
        CLI::write('  New:      ' . $targetUser['username'] . ' is now super_admin', 'cyan');
    }
}
