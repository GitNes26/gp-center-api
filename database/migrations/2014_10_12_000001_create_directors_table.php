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
        Schema::create('directors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users','id');
            $table->string('avatar')->nullable();
            $table->string('name');
            $table->string('paternal_last_name');
            $table->string('maternal_last_name');
            $table->string('phone');
            $table->string('license_number');
            $table->date('license_due_date')->nullable();
            $table->string('img_license')->nullable();
            $table->string('payroll_number')->default('No Aplica');
            $table->foreignId('department_id')->constrained('departments','id')->default(1);
            $table->integer('community_id')->default(0)->comment("este dato viene de una API que por medio del C.P. nos arroja de estado a colonia");
            $table->string('street');
            $table->string('num_ext')->default("S/N");
            $table->string('num_int')->nullable()->default("S/N");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('directors');
    }
};