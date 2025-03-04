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
            $table->string('stock_number');
            $table->foreignId('brand_id')->constrained('brands', 'id');
            $table->foreignId('model_id')->constrained('models', 'id');
            $table->integer('year');
            $table->date('registration_date')->comment('fecha de alta del vehiculo (no en el sistema, si no en la empresa)');
            $table->foreignId('vehicle_status_id')->constrained('vehicle_status', 'id');
            // $table->decimal('km', 11, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('acceptable_license_type')->nullable();
            // $table->string('plates')->comment('placas asignadas al carro');
            $table->string('img_preview')->nullable();
            $table->string('img_right')->nullable();
            $table->string('img_back')->nullable();
            $table->string('img_left')->nullable();
            $table->string('img_front')->nullable();
            $table->string("serial_number");
            $table->string("img_serial_number")->nullable();
            $table->boolean("visible_serial_number")->default(true);
            $table->string("circulation_card");
            $table->string("img_circulation_card")->nullable();
            $table->string("insurance_policy");
            $table->string("img_insurance_policy")->nullable();
            $table->boolean("violated")->nullable()->comment("el vehículo esta infraccionado");
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