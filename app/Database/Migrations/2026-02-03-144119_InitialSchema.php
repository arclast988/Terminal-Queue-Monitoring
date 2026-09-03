<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitialSchema extends Migration
{
    public function up()
    {
        // 1. Users Table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'username' => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'operator'],
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');

        // 2. Terminals Table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'location' => ['type' => 'VARCHAR', 'constraint' => 255],
            'capacity' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('terminals');

        // 3. Routes Table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'origin' => ['type' => 'VARCHAR', 'constraint' => 100],
            'destination' => ['type' => 'VARCHAR', 'constraint' => 100],
            'fare' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'vehicle_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'van'],
            'terminal_id' => ['type' => 'INT', 'constraint' => 11],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('terminal_id', 'terminals', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('routes');

        // 4. Vehicles Table
         $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'plate_number' => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true],
            'type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'default_route_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'capacity' => ['type' => 'INT', 'constraint' => 11],
            'owner_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'scheduled_departure_time' => ['type' => 'TIME', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('default_route_id', 'routes', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('vehicles');

        // 5. Vehicle Assignments Table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'vehicle_id' => ['type' => 'INT', 'constraint' => 11],
            'operator_user_id' => ['type' => 'INT', 'constraint' => 11],
            'assigned_date' => ['type' => 'DATE'],
            'shift_start' => ['type' => 'TIME'],
            'shift_end' => ['type' => 'TIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('vehicle_id', 'vehicles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('operator_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('vehicle_assignments');

        // 6. Queue Table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'vehicle_id' => ['type' => 'INT', 'constraint' => 11],
            'route_id' => ['type' => 'INT', 'constraint' => 11],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'waiting'],
            'current_passengers' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'position' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'arrival_time' => ['type' => 'TIMESTAMP', 'null' => true],
            'departure_time' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('vehicle_id', 'vehicles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('route_id', 'routes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('queue');

        // 7. Trip Status History Table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'queue_id' => ['type' => 'INT', 'constraint' => 11],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50],
            'timestamp' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('queue_id', 'queue', 'id', 'CASCADE', 'CASCADE');
        // updated_by_user_id can be null if system update, or foreign key to users
        $this->forge->addForeignKey('updated_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('trip_status_history');

        // 8. Audit Logs Table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 255],
            'details' => ['type' => 'TEXT'],
            'timestamp' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('audit_logs');
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs', true);
        $this->forge->dropTable('logs', true);
        $this->forge->dropTable('trip_status_history', true);
        $this->forge->dropTable('queue', true);
        $this->forge->dropTable('vehicle_assignments', true);
        $this->forge->dropTable('routes', true);
        $this->forge->dropTable('vehicles', true);
        $this->forge->dropTable('terminals', true);
        $this->forge->dropTable('users', true);
    }
}
