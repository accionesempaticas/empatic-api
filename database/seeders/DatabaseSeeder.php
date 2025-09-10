<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeders para Producción
        $this->call(AdminUserSeeder::class);
        $this->call(DocumentTemplatesTableSeeder::class);

        // Seeders solo para Desarrollo (no se ejecutan en producción)
        // $this->call(PersonSeeder::class);
        // $this->call(TestUsersSeeder::class);
    }
}
