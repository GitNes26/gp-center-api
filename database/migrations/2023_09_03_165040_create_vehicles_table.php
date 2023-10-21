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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_number');
            $table->foreignId('brand_id')->constrained('brands', 'id');
            $table->foreignId('model_id')->constrained('models', 'id');
            $table->integer('year');
            $table->date('registration_date')->comment('fecha de alta del vehiculo (no en el sistema, si no en la empresa)');
            $table->foreignId('vehicle_status_id')->constrained('vehicle_status', 'id');
            $table->text('description')->nullable();
            // $table->string('plates')->comment('placas asignadas al carro');
            $table->string('img_preview_path')->nullable();
            $table->string('img_front_path')->nullable();
            $table->string('img_rigth_path')->nullable();
            $table->string('img_back_path')->nullable();
            $table->string('img_left_path')->nullable();
            $table->string("insurance_policy");
            $table->string("insurance_policy_path");
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
        Schema::dropIfExists('vehicles');
    }
};
