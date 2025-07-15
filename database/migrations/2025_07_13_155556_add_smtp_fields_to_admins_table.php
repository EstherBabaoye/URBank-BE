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
    Schema::table('admins', function (Blueprint $table) {
        $table->string('smtp_host')->nullable();
        $table->string('smtp_port')->nullable();
        $table->string('smtp_username')->nullable();
        $table->string('smtp_password')->nullable(); // We'll encrypt this
        $table->string('smtp_encryption')->nullable();
        $table->string('from_email')->nullable(); // e.g. urbank-admin@site
        $table->string('from_name')->nullable();  // e.g. Esther URBank
    });
}

public function down()
{
    Schema::table('admins', function (Blueprint $table) {
        $table->dropColumn([
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
            'smtp_encryption', 'from_email', 'from_name'
        ]);
    });
}

};
