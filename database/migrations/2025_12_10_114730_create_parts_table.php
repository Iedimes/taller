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
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Código del repuesto
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0); // Precio de compra
            $table->decimal('sale_price', 10, 2)->default(0); // Precio de venta
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0); // Stock mínimo
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
        Schema::dropIfExists('parts');
    }
};
