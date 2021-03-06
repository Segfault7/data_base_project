<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaqueteVehiculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paquete_vehiculo', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('paquete_id');
            $table->string('patente');

            $table->foreign('paquete_id')->references('id')->on('paquete');
            $table->foreign('patente')->references('patente')->on('vehiculo');
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
        Schema::dropIfExists('paquete_vehiculo');
    }
}
