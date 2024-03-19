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
        Schema::create('voucher_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers','id');
            $table->text('vehicle')->nullable();
            $table->text('vehicle_plates')->nullable();
            $table->integer('requested_amount');
            $table->integer('payroll_number')->nullable();
            $table->string('department')->nullable();
            $table->string('name');
            $table->string('paternal_last_name');
            $table->string('maternal_last_name');
            $table->string('phone');

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
        Schema::dropIfExists('voucher_details');
    }
};
