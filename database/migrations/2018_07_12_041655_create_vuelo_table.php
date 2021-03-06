<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVueloTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vuelo', function (Blueprint $table) {
            //$table->increments('id');
            $table->primary('nro_vuelo');
            $table->mediumInteger('nro_vuelo');
            $table->string('origen');
            $table->string('destino');
            $table->dateTime('fecha_hora_salida');
            $table->dateTime('fecha_hora_llegada');
            $table->mediumInteger('cantidad_pasajeros');
            $table->mediumInteger('cantidad_equipaje');
            $table->mediumInteger('precio_vuelo');
            $table->string('aerolinea');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vuelo');
    }
}
