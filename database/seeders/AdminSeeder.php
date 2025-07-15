<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Esther Babaoye',
                'email' => 'estherbabaoye-urbank@nhsurulere.site',
                'password' => Hash::make('admin0000'),
                'smtp_host' => 'mail.nhsurulere.site',
                'smtp_port' => '587',
                'smtp_username' => 'estherbabaoye-urbank@nhsurulere.site',
                'smtp_password' => Crypt::encrypt('Ba07011061367@'),
                'smtp_encryption' => 'tls',
                'from_email' => 'estherbabaoye-urbank@nhsurulere.site',
                'from_name' => 'Esther from URBank',
            ],
            [
                'name' => 'Labims Babs',
                'email' => 'estherbabaoye70-urbank@nhsurulere.site',
                'password' => Hash::make('admin1000'),
                'smtp_host' => 'mail.nhsurulere.site',
                'smtp_port' => '587',
                'smtp_username' => 'estherbabaoye70-urbank@nhsurulere.site',
                'smtp_password' => Crypt::encrypt('Ba07011061367@'),
                'smtp_encryption' => 'tls',
                'from_email' => 'estherbabaoye70-urbank@nhsurulere.site',
                'from_name' => 'Labims from URBank',
            ],
            [
                'name' => 'Praise Olaseni',
                'email' => 'praiseolaseni-urbank@nhsurulere.site',
                'password' => Hash::make('admin2000'),
                'smtp_host' => 'mail.nhsurulere.site',
                'smtp_port' => '587',
                'smtp_username' => 'praiseolaseni-urbank@nhsurulere.site',
                'smtp_password' => Crypt::encrypt('Ba07011061367@'),
                'smtp_encryption' => 'tls',
                'from_email' => 'praiseolaseni-urbank@nhsurulere.site',
                'from_name' => 'Praise from URBank',
            ],
        ];

        foreach ($admins as $admin) {
            Admin::updateOrInsert(
                ['email' => $admin['email']], // Unique key to check
                array_merge($admin, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
