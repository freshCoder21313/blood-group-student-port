<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'admin@school.edu'],
            [
                'phone' => '0700000000',
                'password' => bcrypt('password'),
                'status' => 'active',
                'role' => 'admin'
            ]
        );

        User::firstOrCreate(
            ['email' => 'tructtpk03625@gmail.com'],
            [
                'phone' => '0347018582',
                'password' => bcrypt('password'), // Ensure password is set if created
                'status' => 'active',
                'role' => 'student'
            ]
        );

        $this->call([
            ProgramSeeder::class,
            AcademicBlockSeeder::class,
            ApplicationSeeder::class,
        ]);
    }
}
