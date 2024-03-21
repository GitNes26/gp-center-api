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
        CREATE OR REPLACE VIEW vouchers_view_prev AS
        SELECT v.*,
        -- CONCAT(v.name,' ',v.paternal_last_name,' ',v.maternal_last_name) 'creditor_fullname',
        ua.username 'username_approved', uv.username 'username_viewed', uc.username 'username_canceled',
        ur.role_id 'requested_role_id'
        FROM vouchers v
        INNER JOIN users ur ON v.requested_by=ur.id
        LEFT JOIN users uv ON v.viewed_by=uv.id
        LEFT JOIN users ua ON v.approved_by=ua.id
        LEFT JOIN users uc ON v.canceled_by=uc.id
        WHERE v.active=1
        ;
        ");

        DB::statement("
        CREATE OR REPLACE VIEW vouchers_view AS
        SELECT vvp.*, u.username 'requested_fullname', 'JEFE DEL DEPARTAMENTO DE CONTROL VEHÍCULAR' as 'workstation', NULL 'img_firm', 
        NULL 'requested_payroll_number', NULL 'requested_department', NULL 'requested_phone'
        FROM vouchers_view_prev vvp INNER JOIN users u ON vvp.requested_by=u.id WHERE vvp.requested_role_id IN (1,7)
        UNION
        SELECT vvp.*, dv.full_name 'requested_fullname', CONCAT('DIRECTOR DEL DEPARTAMENTO DE ',dv.department) 'workstation', dv.img_firm, 
        dv.payroll_number 'requested_payroll_number', dv.department 'requested_department', dv.phone 'requested_phone'
        FROM vouchers_view_prev vvp INNER JOIN directors_view dv ON vvp.requested_by=dv.user_id WHERE vvp.requested_role_id=5
        UNION
        SELECT vvp.*, vrv.full_name 'requested_fullname', CONCAT('DIRECTOR DEL DEPARTAMENTO DE ',vrv.department) 'workstation', vrv.img_firm, 
        vrv.payroll_number 'requested_payroll_number', vrv.department 'requested_department', vrv.phone 'requested_phone'
        FROM vouchers_view_prev vvp INNER JOIN voucher_requesters_view vrv ON vvp.requested_by=vrv.user_id WHERE vvp.requested_role_id=8
        ;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // DB::statement('DROP VIEW IF EXISTS vouchers_view');
    }
};
