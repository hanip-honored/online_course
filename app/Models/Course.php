<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title',
        'description',
        'instructor',
        'category',
        'image',
        'duration_hours',
        'level',
        'price',
    ];

    // Relationship: Course has many ratings
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // Helper: Get average rating
    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    // Helper: Get total ratings count
    public function ratingsCount()
    {
        return $this->ratings()->count();
    }

    /**
     * Get popular courses based on average rating and rating count
     * Prioritizes courses with higher average ratings and more ratings
     *
     * @param int $limit Number of courses to return
     * @param int $minRatings Minimum number of ratings to be considered
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPopularCourses(int $limit = 10, int $minRatings = 3)
    {
        return self::select('courses.id', 'courses.title', 'courses.category', 'courses.description', 'courses.created_at', 'courses.updated_at')
            ->selectRaw('AVG(ratings.rating) as avg_rating')
            ->selectRaw('COUNT(ratings.id) as ratings_count')
            ->leftJoin('ratings', 'courses.id', '=', 'ratings.course_id')
            ->groupBy('courses.id', 'courses.title', 'courses.category', 'courses.description', 'courses.created_at', 'courses.updated_at')
            ->havingRaw('COUNT(ratings.id) >= ?', [$minRatings])
            ->orderByDesc('avg_rating')
            ->orderByDesc('ratings_count')
            ->limit($limit)
            ->get()
            ->map(function ($course) {
                $course->predicted_rating = $course->avg_rating;
                return $course;
            });
    }
}
