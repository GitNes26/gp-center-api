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
        Schema::create('inventory_records', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction', ["Entrada", "Salida"]);
            $table->foreignId('material_id')->constrained('inventory', 'id');
            $table->decimal('quantity')->comment("positiva para entradas, negativa para salida");
            $table->string('unit')->comment("unidad de medida (lts, metros, pzas, kgs...)");

            $table->foreignId('user_id')->constrained('users', 'id');
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
        Schema::dropIfExists('inventory_records');
    }
};
