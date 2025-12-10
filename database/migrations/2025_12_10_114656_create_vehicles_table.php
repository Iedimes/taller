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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('brand'); // Marca
            $table->string('model'); // Modelo
            $table->string('year'); // Año
            $table->string('plate')->unique(); // Placa
            $table->string('vin')->nullable(); // VIN/Chasis
            $table->string('color')->nullable();
            $table->integer('mileage')->default(0); // Kilometraje
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
