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
        Schema::create('measurement_units', function (Blueprint $table) {
            $table->id();
            $table->string('abbr')->comment("abrebviacion unidad de medida (lts, metros, pzas, kgs,...)");
            $table->string('unit')->comment("unidad de medida (litros, metros, piezas, kilogramos,...)");
            $table->text('description')->nullable();
            $table->string('conversion')->comment("escribir la formula para convertir las unidades a la unidad base, ");
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
        Schema::dropIfExists('measurement_units');
    }
};
