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
    Schema::create('account_openings', function (Blueprint $table) {
        $table->id();

        // Personal details
        $table->string('title');
        $table->string('first_name');
        $table->string('middle_name')->nullable();
        $table->string('surname');
        $table->string('mothers_maiden_name');
        $table->string('gender');
        $table->date('dob');
        $table->string('marital_status');
        $table->string('nationality');
        $table->string('state_of_origin');
        $table->string('lga_of_origin');

        // Address
        $table->string('house_number');
        $table->string('street_name');
        $table->string('city');
        $table->string('residential_lga')->nullable();
        $table->string('residential_state');

        // Identity
        $table->string('phone');
        $table->string('email');
        $table->string('id_type');
        $table->string('id_number');
        $table->date('id_issue_date')->nullable();
        $table->date('id_expiry_date')->nullable();
        $table->string('bvn')->unique();
        $table->string('nin')->unique();
        $table->string('tin')->nullable();

        // Employment
        $table->string('employment_status');
        $table->string('employer_name')->nullable();
        $table->string('employer_address')->nullable();
        $table->string('annual_income');

        // Next of kin
        $table->string('nok_first_name');
        $table->string('nok_middle_name')->nullable();
        $table->string('nok_surname');
        $table->string('nok_gender');
        $table->date('nok_dob');
        $table->string('nok_relationship');
        $table->string('nok_phone');
        $table->string('nok_email');
        $table->text('nok_address');

        // Account & Services
        $table->string('account_type');
        $table->string('card_type')->nullable();
        $table->json('electronic_banking');
        $table->json('alert_preference');

        // Mandate
        $table->string('mandate_first_name');
        $table->string('mandate_middle_name')->nullable();
        $table->string('mandate_surname');
        $table->string('mandate_id_type');
        $table->string('mandate_id_number');
        $table->string('mandate_phone');
        $table->string('mandate_signature');
        $table->date('mandate_date');

        // Declaration
        $table->string('declaration_name');
        $table->string('declaration_signature');
        $table->date('declaration_date');

        // Documents
        $table->string('passport_photo');
        $table->string('uploaded_id_file');
        $table->string('utility_bill');

        // Status
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->timestamp('account_created_at');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_openings');
    }
};
