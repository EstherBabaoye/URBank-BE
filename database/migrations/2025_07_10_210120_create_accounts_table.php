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
    Schema::create('accounts', function (Blueprint $table) {
        $table->id();
        $table->string('account_number')->unique();
        $table->string('account_type');
        $table->string('first_name');
        $table->string('middle_name')->nullable();
        $table->string('surname');
        $table->string('email');
        $table->string('phone');
        $table->string('bvn')->unique();
        $table->string('house_number');
        $table->string('street_name');;
        $table->string('city');;
        $table->string('residential_lga')->nullable();
        $table->string('residential_state');
        $table->string('id_type');
        $table->string('id_number')->nullable();
        $table->date('id_issue_date');
        $table->date('id_expiry_date');
        $table->string('passport_photo');
        $table->string('utility_bill');
        $table->string('uploaded_id_file');
        $table->string('card_number_masked')->nullable();
        $table->string('card_expiry')->nullable();
        $table->timestamp('account_created_at');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
