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
        CREATE OR REPLACE VIEW active_assignment_view AS
        SELECT av.id ass_folio, av.vehicle_id ass_vehicle_id, av.date ass_date,
        dir.id dir_id, dir.username dir_username, dir.email dir_email, dir.avatar dir_avatar, dir.name dir_name, dir.paternal_last_name dir_paternal_last_name, dir.maternal_last_name dir_maternal_last_name,
        dir.phone dir_phone, dir.department dir_department
        FROM assigned_vehicles av
        INNER JOIN directors_view dir ON av.user_id=dir.user_id
        ORDER BY av.date AND av.active=1 DESC
        -- LIMIT 1
        ;

        CREATE OR REPLACE VIEW active_loan_view AS
        SELECT lv.id loa_folio, lv.requesting_user_id,
        dri.id dri_id, dri.username dri_username, dri.email dri_email, dri.avatar dri_avatar, dri.name dri_name, dri.paternal_last_name dri_paternal_last_name, dri.maternal_last_name dri_maternal_last_name,
        dri.phone dri_phone, dri.department dri_department
        FROM loaned_vehicles lv
        INNER JOIN active_assignment_view aav ON lv.assigned_vehicle_id=aav.ass_folio
        INNER JOIN drivers_view dri ON lv.requesting_user_id=dri.user_id
        ORDER BY lv.created_at AND lv.active=1 DESC
        -- LIMIT 1
        ;

        CREATE OR REPLACE VIEW vehicle_detail_view AS
        SELECT v.*, b.brand, b.img_path brand_img, m.model,
        vs.vehicle_status, vs.bg_color, vs.letter_black,
        vp.plates, vp.initial_date, vp.due_date, vp.expired,
        aav.*, alv.*
        FROM vehicles v
        INNER JOIN brands b ON v.brand_id=b.id
        INNER JOIN models m ON v.model_id=m.id
        INNER JOIN vehicle_status vs ON v.vehicle_status_id=vs.id
        INNER JOIN vehicle_plates vp ON v.id=vp.vehicle_id AND vp.expired=0
        LEFT JOIN active_assignment_view aav ON v.id=aav.ass_vehicle_id
        LEFT JOIN active_loan_view alv ON aav.ass_folio=alv.requesting_user_id;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // DB::statement('DROP VIEW IF EXISTS drivers_view');
    }
};