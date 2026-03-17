<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSentreRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sentre_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sentre_user_id');
            $table->enum('type', ['tramite', 'concentracion', 'baja', 'historico']);
            $table->string('record_id')->nullable();

            // Campos de la tabla principal
            $table->string('expediente')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('anio_creacion')->nullable();
            $table->string('ubicacion_fisica')->nullable();
            $table->string('no_caja')->nullable();
            $table->string('fecha_inicio')->nullable();
            $table->string('fecha_final')->nullable();
            $table->string('tiempo_conservacion')->nullable();
            $table->string('fecha_transferencia')->nullable();
            $table->string('clasificacion')->nullable();
            $table->string('caracter_documental')->nullable();

            // Campos adicionales de detalles
            $table->string('no_legajos')->nullable();
            $table->string('no_hojas')->nullable();
            $table->string('preservacion')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->foreign('sentre_user_id')->references('id')->on('sentre_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sentre_records');
    }
}
