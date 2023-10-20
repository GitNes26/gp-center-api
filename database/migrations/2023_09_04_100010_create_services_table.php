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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->integer('folio');
            $table->foreignId('vehicle_id')->constrained('vehicles', 'id');
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->text('pre_diagnosis')->comment("lo que el usuario le dice al mecanico");
            $table->text('final_diagnosis')->nullable()->comment("lo que el mecanico determina despues de revisarlo");
            $table->string('evidence_img_path')->nullable()->comment("por si desea subir foto NO CONTEMPLADO AUN");
            $table->foreignId('mechanic_id')->constrained('users', 'id');
            $table->enum('status',["Abierta","En Revisión", "Cerrada"])->default("Abierta");

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
        Schema::dropIfExists('services');
    }
};
