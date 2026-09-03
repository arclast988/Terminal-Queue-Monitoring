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

        $db->query('ALTER TABLE logs RENAME TO audit_logs');

        if ($this->constraintExists('logs_user_id_foreign')) {
            $db->query('ALTER TABLE audit_logs DROP CONSTRAINT logs_user_id_foreign');
            $db->query(
                'ALTER TABLE audit_logs
                 ADD CONSTRAINT audit_logs_user_id_foreign
                 FOREIGN KEY (user_id) REFERENCES users(id)
                 ON DELETE SET NULL ON UPDATE CASCADE'
            );
        }

        if ($this->indexExists('idx_logs_timestamp')) {
            $db->query('ALTER INDEX idx_logs_timestamp RENAME TO idx_audit_logs_timestamp');
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if (!$this->tableExists('audit_logs') || $this->tableExists('logs')) {
            return;
        }

        $db->query('ALTER TABLE audit_logs RENAME TO logs');

        if ($this->constraintExists('audit_logs_user_id_foreign')) {
            $db->query('ALTER TABLE logs DROP CONSTRAINT audit_logs_user_id_foreign');
            $db->query(
                'ALTER TABLE logs
                 ADD CONSTRAINT logs_user_id_foreign
                 FOREIGN KEY (user_id) REFERENCES users(id)
                 ON DELETE SET NULL ON UPDATE CASCADE'
            );
        }

        if ($this->indexExists('idx_audit_logs_timestamp')) {
            $db->query('ALTER INDEX idx_audit_logs_timestamp RENAME TO idx_logs_timestamp');
        }
    }

    private function tableExists(string $table): bool
    {
        $db = \Config\Database::connect();
        return $db->tableExists($table);
    }

    private function indexExists(string $indexName): bool
    {
        $db = \Config\Database::connect();
        $row = $db->query(
            'SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND indexname = ?',
            [$indexName]
        )->getRowArray();
        return (bool) $row;
    }

    private function constraintExists(string $constraintName): bool
    {
        $db = \Config\Database::connect();
        $row = $db->query(
            "SELECT 1 FROM information_schema.table_constraints
             WHERE constraint_schema = current_schema()
               AND constraint_name = ?
               AND constraint_type = 'FOREIGN KEY'",
            [$constraintName]
        )->getRowArray();
        return (bool) $row;
    }
}
