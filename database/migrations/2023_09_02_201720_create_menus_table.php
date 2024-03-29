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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('menu');
            $table->string('caption')->nullable()->comment("disponible solo para los menus padres");
            $table->enum('type', ['group', 'item']);
            $table->integer('belongs_to');
            $table->string('url')->nullable()->comment("disponible solo para los menus hijos");
            $table->string('icon')->nullable()->comment("disponible solo para los menus hijos");
            // $table->string('description')->nullable();
            // $table->string('tag')->nullable();
            // $table->string('file_name')->default('#');
            $table->integer('order')->nullable();
            $table->string('show_counter')->nullable();
            $table->string('others_permissions')->nullable();
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
        Schema::dropIfExists('menus');
    }
};
