<?php

namespace App\Database\Migrations;

use App\Models\VehicleModel;
use CodeIgniter\Database\Migration;

class EnsureUniqueVehicleRouteOrder extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('vehicles') && $this->db->tableExists('routes')
            && $this->db->fieldExists('dispatch_order', 'vehicles')) {
            (new VehicleModel($this->db))->normalizeDispatchOrders();
        }
    }

    public function down()
    {
        // Preserve the corrected route order.
    }
}
