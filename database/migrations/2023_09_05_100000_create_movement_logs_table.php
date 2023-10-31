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
        Schema::create('movement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id');
            $table->enum('action', ["Registro", "Modificaciòn","Desactivaciòn","Eliminaciòn"]);
            $table->string('table')->comment("nombre de la tabla que se modifico");
            $table->string('column')->nullable()->comment("nombre de la columna modificado");
            $table->integer('id_register')->comment("id del registro modificado");
            $table->string('previous_value')->nullable()->comment("valor anterior; si es nuevo, sera null");
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
        Schema::dropIfExists('movement_logs');
    }
};