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
        CREATE OR REPLACE VIEW mechanics_view AS
        SELECT m.*,
        CONCAT(m.name,
                ' ',
                m.paternal_last_name,
                ' ',
                m.maternal_last_name) AS full_name
        FROM mechanics m
        -- WHERE m.active=1;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS mechanics_view');
    }
};
