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
        Schema::create('work_order_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('part_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2); // Costo unitario
            $table->decimal('unit_price', 10, 2); // Precio unitario al cliente
            $table->decimal('subtotal_cost', 10, 2); // Subtotal costo
            $table->decimal('subtotal_price', 10, 2); // Subtotal precio
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
        Schema::dropIfExists('work_order_parts');
    }
};
