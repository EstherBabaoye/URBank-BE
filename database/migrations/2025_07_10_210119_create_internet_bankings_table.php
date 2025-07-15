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
    Schema::create('internet_banking', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('middle_name')->nullable();
        $table->string('surname');
        $table->string('account_number')->unique();
        $table->string('bvn')->unique();
        $table->string('atm_first4');
        $table->string('atm_last6')->unique();
        $table->string('atm_pin');
        $table->string('email')->unique();
        $table->string('login_pin');
        $table->string('sec_question1');
        $table->string('sec_answer1');
        $table->string('sec_question2');
        $table->string('sec_answer2');
        $table->boolean('verified')->default(false);
        $table->timestamp('verified_at')->nullable();
        $table->string('verification_token')->nullable();
        $table->string('reset_token')->nullable();
        $table->timestamp('reset_token_created_at')->nullable();
        $table->timestamp('pin_reset_verified_at')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internet_banking');
    }
};
