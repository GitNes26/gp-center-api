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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users', 'id');
            $table->foreignId('role_id')->constrained('roles', 'id');
            $table->string('payroll_number')->default('No Aplica');
            $table->text('others_permissions')->nullable();
            $table->string('avatar')->nullable();
            $table->string('name');
            $table->string('paternal_last_name');
            $table->string('maternal_last_name');
            $table->string('phone')->nullable();
            $table->string('license_number')->nullable();
            $table->string('license_type')->nullable();
            $table->date('license_due_date')->nullable();
            $table->string('img_license')->nullable();
            $table->string('img_firm')->nullable();
            $table->integer('department_id')->nullable()->comment("este dato viene de una API externa");
            $table->string('department')->nullable()->comment("este dato viene de una API que al ingresar el num. de empleado se obtiene");
            $table->integer('community_id')->default(0)->comment("este dato viene de una API que por medio del C.P. nos arroja de estado a colonia");
            $table->string('street');
            $table->string('num_ext')->default("S/N");
            $table->string('num_int')->nullable()->default("S/N");
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
        Schema::dropIfExists('employees');
    }
};