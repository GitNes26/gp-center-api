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
        Schema::create('vouchers_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id')->comment("usuario que creo la confioguración");
            $table->string('director_from')->comment("Director del departamento de Control Vehicular");
            $table->string('department_from')->comment("Departamento de Control Vehicular");
            $table->string('director_to_1')->comment("Director 1 a quien se dirije el oficio");
            $table->string('department_to_1')->comment("Departamento del director 1");
            $table->string('director_to_2')->comment("Director 2 a quien se dirije el oficio");
            $table->string('department_to_2')->comment("Departamento del director 2");
            $table->string('img_stamp')->nullable();
            $table->string('img_date_stamp')->nullable();

            $table->text('comments')->nullable();
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
        Schema::dropIfExists('vouchers_settings');
    }
};
