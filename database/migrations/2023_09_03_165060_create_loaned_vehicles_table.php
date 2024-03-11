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
        Schema::create('loaned_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_vehicle_id')->constrained('assigned_vehicles', 'id')->comment("folio de asignacion de vehiculo");
            $table->foreignId('requesting_user_id')->constrained('users', 'id')->comment("usuario que solicita o se le presta el vehiculo");
            $table->text('reason')->nullable()->comment('motivo del prestamo de la unidad');
            $table->decimal('initial_km', 10, 2);
            $table->dateTime('loan_date');
            $table->boolean('active_loan')->default(true)->comment('prestamo activo');
            $table->decimal('delivery_km', 10, 2)->nullable();
            $table->text('delivery_comments')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->boolean('active')->default(true)->comment('registro activo');
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loaned_vehicles');
    }
};
