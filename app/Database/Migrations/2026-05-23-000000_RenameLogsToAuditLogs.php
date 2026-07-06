<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameLogsToAuditLogs extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$this->tableExists('logs') || $this->tableExists('audit_logs')) {
            return;
        }

        $db->query('RENAME TABLE `logs` TO `audit_logs`');

        if ($this->foreignKeyExists('audit_logs', 'logs_user_id_foreign')) {
            $db->query('ALTER TABLE `audit_logs` DROP FOREIGN KEY `logs_user_id_foreign`');
            $db->query(
                'ALTER TABLE `audit_logs`
                 ADD CONSTRAINT `audit_logs_user_id_foreign`
                 FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
                 ON DELETE SET NULL ON UPDATE CASCADE'
            );
        }

        if ($this->indexExists('audit_logs', 'idx_logs_timestamp')) {
            $db->query('ALTER TABLE `audit_logs` RENAME INDEX `idx_logs_timestamp` TO `idx_audit_logs_timestamp`');
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if (!$this->tableExists('audit_logs') || $this->tableExists('logs')) {
            return;
        }

        $db->query('RENAME TABLE `audit_logs` TO `logs`');

        if ($this->foreignKeyExists('logs', 'audit_logs_user_id_foreign')) {
            $db->query('ALTER TABLE `logs` DROP FOREIGN KEY `audit_logs_user_id_foreign`');
            $db->query(
                'ALTER TABLE `logs`
                 ADD CONSTRAINT `logs_user_id_foreign`
                 FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
                 ON DELETE SET NULL ON UPDATE CASCADE'
            );
        }

        if ($this->indexExists('logs', 'idx_audit_logs_timestamp')) {
            $db->query('ALTER TABLE `logs` RENAME INDEX `idx_audit_logs_timestamp` TO `idx_logs_timestamp`');
        }
    }

    private function tableExists(string $table): bool
    {
        $db = \Config\Database::connect();
        $row = $db->query(
            'SELECT COUNT(1) AS c
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        )->getRow();
        return (int) ($row->c ?? 0) > 0;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $db = \Config\Database::connect();
        $row = $db->query(
            'SELECT COUNT(1) AS c
             FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $indexName]
        )->getRow();
        return (int) ($row->c ?? 0) > 0;
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $db = \Config\Database::connect();
        $row = $db->query(
            'SELECT COUNT(1) AS c
             FROM information_schema.table_constraints
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND constraint_name = ?
               AND constraint_type = \'FOREIGN KEY\'',
            [$table, $constraintName]
        )->getRow();
        return (int) ($row->c ?? 0) > 0;
    }
}
