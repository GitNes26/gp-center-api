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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('email');
            $table->string('password');
            $table->foreignId('role_id')->constrained('roles','id');
            // $table->string('phone')->default('No Aplica');
            // $table->string('license_number')->default('No Aplica');
            // $table->date('license_due_date')->nullable();
            // $table->string('payroll_number')->default('No Aplica');
            // $table->foreignId('department_id')->constrained('departments','id')->default(1);

            // $table->string('name')->nullable()->default('No Aplica');
            // $table->string('paternal_last_name')->nullable()->default('No Aplica');
            // $table->string('maternal_last_name')->nullable()->default('No Aplica');
            // $table->integer('community_id')->default(0)->comment("este dato viene de una API que por medio del C.P. nos arroja de estado a colonia");
            // $table->string('street')->default('No Aplica');
            // $table->string('num_ext')->default("S/N");
            // $table->string('num_int')->nullable()->default("S/N");

            $table->boolean('active')->default(true);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};