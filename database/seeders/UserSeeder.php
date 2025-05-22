<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Création du Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@genius.ci',
            'phone' => '+2250704750465',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'pin' => Hash::make('1234'),
            'pin_attempts' => 0,
            'pin_changed_at' => now()   ,
            'statut' => 'actif',    
            'role' => 'super_admin',
            'preferences' => [
                'langue' => 'fr',
                'fuseau_horaire' => 'Africa/Abidjan',
                'notifications' => [
                    'email' => true,
                    'sms' => true,
                    'push' => true
                ]
            ]
        ]);
        $superAdmin->assignRole('super_admin');

        // Création d'un admin d'entreprise
        $admin = User::create([
            'name' => 'Support IT',
            'email' => 'support@genius.ci',
            'phone' => '+22527222628',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'pin' => Hash::make('2345'),
            'pin_attempts' => 0,
            'pin_changed_at' => now(),
            'statut' => 'actif',
            'role' => 'support',
            'preferences' => [
                'langue' => 'fr',
                'fuseau_horaire' => 'Africa/Abidjan',
                'notifications' => [
                    'email' => true,
                    'sms' => true,
                    'push' => true
                ]
            ]
        ]);
        $admin->assignRole('support');
    }
}
