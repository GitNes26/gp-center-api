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
        Schema::create('delivered_vehicles', function (Blueprint $table) {
            $table->id();
            $table->integer('accident_folio')->nullable();
            $table->foreignId('assigned_vehicle_id')->constrained('assigned_vehicles','id');
            $table->text('reason')->nullable()->comment('motivo por el cual se devuelve la unidad');
            $table->dateTime('date')->nullable();
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('delivered_vehicles');
    }
};
