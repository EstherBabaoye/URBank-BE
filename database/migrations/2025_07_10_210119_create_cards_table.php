<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('cards', function (Blueprint $table) {
        $table->id();
        $table->string('account_number')->unique();
        $table->string('card_type');
        $table->string('card_number');
        $table->string('card_number_hash');
        $table->string('first4');
        $table->string('last6')->unique(); // ✅ Corrected here
        $table->string('cvv');
        $table->string('card_pin');
        $table->string('expiry_date');
        $table->timestamps();
    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
