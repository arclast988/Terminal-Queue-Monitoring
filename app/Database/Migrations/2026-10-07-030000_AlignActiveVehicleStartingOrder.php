<?php

namespace App\Database\Migrations;

use App\Models\VehicleModel;
use CodeIgniter\Database\Migration;

class AlignActiveVehicleStartingOrder extends Migration
{
    public function up()
    {
        (new VehicleModel($this->db))->normalizeDispatchOrders();
    }

    public function down()
    {
        // Preserve the corrected starting positions and departure history.
    }
}
