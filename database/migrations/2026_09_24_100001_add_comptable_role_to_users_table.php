<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','manager','entreprise','employeur','support','employee','comptable') NOT NULL DEFAULT 'employeur'");
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return;
        }

        DB::table('users')->where('role', 'comptable')->update(['role' => 'employeur']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','manager','entreprise','employeur','support','employee') NOT NULL DEFAULT 'employeur'");
    }
};
