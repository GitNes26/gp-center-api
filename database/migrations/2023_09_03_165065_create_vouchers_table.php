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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->integer('requested_by')->comment('usuario que solicita el vale');
            $table->string('internal_folio');
            $table->string('letter_folio', 3)->nullable();
            $table->text('foliated_vouchers')->nullable();

            $table->text('activity');
            $table->enum('voucher_status', ['CREADO', 'ALTA', 'VoBo', 'APROBADA', 'CANCELADA'])->default('ALTA');
            $table->integer('vobo_by')->nullable()->comment('Visto Bueno por');
            $table->dateTime('vobo_at')->nullable()->comment('Visto Bueno el');
            $table->integer('viewed_by')->nullable();
            $table->dateTime('viewed_at')->nullable();
            $table->integer('approved_by')->nullable()->comment("aprobado por");
            $table->integer('approved_amount')->nullable()->comment("cantidad aprobada");
            $table->decimal('approved_liters', 11, 2)->nullable()->comment("cantidad de litros aprobada");
            $table->string('approved_combustible')->nullable()->comment("tipo de combustible aprobada");
            $table->dateTime('approved_at')->nullable()->comment("aprobado el");
            $table->integer('canceled_by')->nullable()->comment("cancelado por");
            $table->text('canceled_comments')->nullable();
            $table->dateTime('canceled_at')->nullable()->comment("cancelado el");
            $table->integer('requester_external')->nullable()->comment("cuando un usuario crea una solicitud por un solicitador");
            $table->foreignId("department_director_id")->constrained("department_directors", "id");

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
        Schema::dropIfExists('vouceh');
    }
};