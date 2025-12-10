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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->date('entry_date');
            $table->date('estimated_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'delivered', 'cancelled'])->default('pending');
            $table->text('description'); // Descripción del trabajo
            $table->decimal('labor_cost', 10, 2)->default(0); // Costo de mano de obra
            $table->decimal('parts_cost', 10, 2)->default(0); // Costo de repuestos
            $table->decimal('total_cost', 10, 2)->default(0); // Costo total
            $table->decimal('total_price', 10, 2)->default(0); // Precio total al cliente
            $table->decimal('profit', 10, 2)->default(0); // Utilidad
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('work_orders');
    }
};
