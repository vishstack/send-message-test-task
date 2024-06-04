<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method is called when the seeder is executed. It creates a specified number of user records
     * using the User model factory.
     *
     * @return void
     */
    public function run(): void
    {
        // Create 10 user records using the User model factory.
        \App\Models\User::factory()->count(10)->create();
    }
}
