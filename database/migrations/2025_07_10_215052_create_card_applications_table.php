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
    Schema::create('card_applications', function (Blueprint $table) {
        $table->id();
        $table->string('account_number')->unique();
        $table->string('first_name');
        $table->string('middle_name')->nullable();
        $table->string('surname');
        $table->string('email');
        $table->string('phone');
        $table->string('card_type');
        $table->string('sub_card_type');
        $table->string('reason')->nullable();
        $table->string('other_reason')->nullable();
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->string('rejection_reason')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_applications');
    }
};
