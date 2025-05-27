<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\PlanAbonnementSeeder;
use Database\Seeders\EntrepriseSeeder;
use Database\Seeders\TypeCongeSeeder;
use Database\Seeders\MethodePointageSeeder;
use Database\Seeders\JourTravailSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\EmployeurSeeder;
use Database\Seeders\FilialeSeeder;
use Database\Seeders\DepartementSeeder;
use Database\Seeders\WebPointageSeeder;
use Database\Seeders\SiteSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            //EntrepriseSeeder::class,
            PlanAbonnementSeeder::class,
            //JourTravailSeeder::class,
            //TypeCongeSeeder::class,
            //MethodePointageSeeder::class,
            //WebPointageSeeder::class,
            //DepartementSeeder::class,
            UserSeeder::class,
            //EmployeurSeeder::class,
            //FilialeSeeder::class,
            //SiteSeeder::class,
        ]);
    }
}
