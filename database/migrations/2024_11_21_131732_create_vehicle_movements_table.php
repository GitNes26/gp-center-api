<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('vehicle_movements_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id')->comment("usuario que realizo el movimiento");
            $table->foreignId('vehicle_id')->constrained('vehicles', 'id');
            $table->foreignId('vehicle_status_id')->constrained('vehicle_status', 'id');
            $table->foreignId('active_user_id')->constrained('users', 'id')->comment("usuario responsable");
            $table->decimal('km', 11, 2);
            $table->text('comments')->nullable();
            $table->string("table_assoc")->nullable()->comment("nombre de la tabla asociada por el movimiento de status para ligar información");
            $table->integer("table_assoc_register_id")->nullable()->comment("ID del registro de la tabla asociada para obtener datos relacionados");

            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });
        // Schema::create('vehicle_movements', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('user_id')->constrained('users', 'id')->comment("usuario que realizo el movimiento");
        //     $table->foreignId('old_vehicle_status_id')->constrained('vehicle_status', 'id');
        //     // $table->boolean("need_approved");
        //     // $table->foreignId('approved_by')->constrained('users', 'id')->nullable()->comment("usuario que aprobo el movimiento");
        //     // $table->dateTime('approved_at')->nullable();
        //     $table->foreignId('vehicle_id')->constrained('vehicles', 'id');
        //     $table->string("table_assoc")->nullable()->comment("nombre de la tabla asociada por el movimiento de status para ligar información");
        //     $table->integer("table_assoc_register_id")->nullable()->comment("ID del registro de la tabla asociada para obtener datos relacionados");
        //     $table->text('comments')->nullable();
        //     $table->boolean('active')->default(true);
        //     $table->timestamps();
        //     $table->dateTime('deleted_at')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_movements');
    }
};