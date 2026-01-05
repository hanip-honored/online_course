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
        // Create 25 test users (lebih banyak untuk data yang lebih kaya)
        User::factory(25)->create();

        // Create test admin user
        User::factory()->create([
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create beberapa user spesifik untuk testing
        User::factory()->create([
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);

        User::factory()->create([
            'name'  => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        User::factory()->create([
            'name'  => 'Bob Wilson',
            'email' => 'bob@example.com',
        ]);

        // Seed courses
        $this->call([
            CourseSeeder::class,
            RatingSeeder::class,
        ]);
    }
}
