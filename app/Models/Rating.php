<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'rating',
        'review',
    ];

    // Relationship: Rating belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Rating belongs to a course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
