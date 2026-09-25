<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cartitems', function (Blueprint $table) {
            $table->unsignedBigInteger('cart_id');
            $table->foreign('cart_id')
                ->references('id')
                ->on('carts')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cartitems', function (Blueprint $table) {
            //
        });
    }
};
