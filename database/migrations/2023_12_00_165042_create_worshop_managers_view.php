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
        CREATE OR REPLACE VIEW workshop_managers_view AS
        SELECT u.id u_id, u.username, u.email, u.role_id, u.active,
        CONCAT(wm.name,
                ' ',
                wm.paternal_last_name,
                ' ',
                wm.maternal_last_name) AS full_name, wm.*,
        r.role, r.read, r.create, r.update, r.delete, r.more_permissions
        FROM workshop_managers wm
        INNER JOIN users u ON wm.user_id=u.id
        INNER JOIN roles r ON u.role_id=r.id
        WHERE u.active=1;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS workshop_managers_view');
    }
};