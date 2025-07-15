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
    Schema::create('failed_accounts', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('surname');
        $table->string('email');
        $table->string('bvn');
        $table->string('phone');
        $table->string('passport_photo')->nullable();
        $table->string('utility_bill')->nullable();
        $table->string('uploaded_id_file')->nullable();
        $table->string('rejection_reason')->nullable();
        $table->timestamp('rejected_at');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_accounts');
    }
};
