<?php
namespace Database\Seeders;

use App\Models\Course;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada users dan courses
        $users   = User::all();
        $courses = Course::all();

        if ($users->count() == 0 || $courses->count() == 0) {
            $this->command->info('Please create users first before seeding ratings.');
            return;
        }

        $this->command->info("Creating ratings for {$users->count()} users and {$courses->count()} courses...");

        $totalRatings = 0;

        // Generate random ratings (setiap user rating beberapa courses secara random)
        foreach ($users as $user) {
            // Setiap user akan rating 5-15 courses secara random (lebih banyak untuk data yang kaya)
            $numRatings    = rand(5, min(15, $courses->count()));
            $coursesToRate = $courses->random($numRatings);

            foreach ($coursesToRate as $course) {
                Rating::create([
                    'user_id'   => $user->id,
                    'course_id' => $course->id,
                    'rating'    => $this->generateRealisticRating(), // Rating lebih realistis
                    'review'    => $this->generateRandomReview(),
                ]);
                $totalRatings++;
            }
        }

        $this->command->info("✓ Created {$totalRatings} ratings successfully!");
    }

    /**
     * Generate realistic rating distribution
     * Most ratings are 4-5, some 3, few 1-2
     */
    private function generateRealisticRating()
    {
        $rand = rand(1, 100);

        if ($rand <= 40) {
            return 5;
        }
        // 40% rating 5
        if ($rand <= 75) {
            return 4;
        }
        // 35% rating 4
        if ($rand <= 90) {
            return 3;
        }
        // 15% rating 3
        if ($rand <= 97) {
            return 2;
        }
                  // 7% rating 2
        return 1; // 3% rating 1
    }

    private function generateRandomReview()
    {
        $reviews = [
            'Great course! Very informative and well-structured.',
            'Excellent instructor, learned a lot.',
            'Good content but could use more examples.',
            'Very helpful for beginners.',
            'Advanced topics explained clearly.',
            'Worth the investment.',
            'Highly recommended!',
            null, // Some ratings without review
            null,
        ];

        return $reviews[array_rand($reviews)];
    }
}
