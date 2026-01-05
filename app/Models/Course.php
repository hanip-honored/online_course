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
}
