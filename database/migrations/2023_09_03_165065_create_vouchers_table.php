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
            $table->integer('user_id')->comment('usuario que registro el voucher');
            $table->text('foliated_vouchers');
            $table->integer('stock_number')->nullable();
            $table->text('vehicle_plates')->nullable();
            $table->integer('payroll_number')->nullable();
            $table->string('department')->nullable();
            $table->string('name');
            $table->string('paternal_last_name');
            $table->string('maternal_last_name');
            $table->string('phone');
            $table->text('activity');
            $table->enum('voucher_status',['ALTA','APROBADA','CANCELADA']);
            $table->integer('quantity');

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