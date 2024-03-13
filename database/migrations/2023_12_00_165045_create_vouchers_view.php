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
        CREATE OR REPLACE VIEW vouchers_view AS
        SELECT v.*, CONCAT(v.name,' ',v.paternal_last_name,' ',v.maternal_last_name) 'requested_fullname' , ua.username 'username_approved', uc.username 'username_canceled'
        FROM vouchers v
        LEFT JOIN users ua ON v.approved_by=ua.id
        LEFT JOIN users uc ON v.canceled_by=uc.id
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