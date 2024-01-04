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
        CREATE OR REPLACE VIEW drivers_view AS
        SELECT u.id u_id, u.username, u.email, u.role_id, u.active,
         d.*,
        r.role, r.read, r.create, r.update, r.delete, r.more_permissions,
        dep.department
        FROM drivers d
        INNER JOIN users u ON d.user_id=u.id
        INNER JOIN roles r ON u.role_id=r.id
        INNER JOIN departments dep ON d.department_id=dep.id
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
        DB::statement('DROP VIEW IF EXISTS drivers_view');
    }
};
