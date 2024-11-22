<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("        
        CREATE OR REPLACE VIEW vw_services AS
        SELECT s.*, v.stock_number, CONCAT(v.brand,' ', v.model,' ', v.year) vehicle, v.brand, v.model, v.year, v.description, v.vehicle_status, v.plates, v.registration_date, ureq.username requested_user, 
        ua.username approved_user, urev.username reviewed_user, urej.username rejected_user, ucv.username confirmed_username
        FROM services as s
        INNER JOIN vehicle_detail_view as v ON s.vehicle_id=v.id
        LEFT JOIN users as ureq ON s.requested_by=ureq.id
        LEFT JOIN users as ua ON s.approved_by=ua.id
        LEFT JOIN users as urev ON s.mechanic_id=urev.id 
        LEFT JOIN users as urej ON s.rejected_by=urej.id
        LEFT JOIN users as ucv ON s.confirmed_by=ucv.id
        WHERE s.active=1;
        ");

        DB::statement("
        CREATE OR REPLACE VIEW vw_services_opened AS
        SELECT * FROM vw_services WHERE status IN ('ABIERTA');
        ");

        DB::statement("
        CREATE OR REPLACE VIEW vw_services_approved AS
        SELECT * FROM vw_services WHERE status IN ('APROBADA')
        ORDER BY approved_at;
        ");

        DB::statement("
        CREATE OR REPLACE VIEW vw_services_in_reviewed AS
        SELECT * FROM vw_services WHERE status IN ('EN REVISIÓN','APROBADA POR CV')
        ORDER BY reviewed_at;
        ");

        DB::statement("
        CREATE OR REPLACE VIEW vw_services_rejected AS
        SELECT * FROM vw_services WHERE status IN ('RECHAZADA','RECHAZADA POR CV')
        ORDER BY rejected_at;
        ");

        DB::statement("
        CREATE OR REPLACE VIEW vw_services_closed AS
        SELECT * FROM vw_services WHERE status IN ('CERRADA')
        ORDER BY closed_at;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
