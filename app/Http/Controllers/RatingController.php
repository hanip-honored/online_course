<?php
namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        // Create or update rating
        Rating::updateOrCreate(
            [
                'user_id'   => auth()->id(),
                'course_id' => $course->id,
            ],
            [
                'rating' => $request->rating,
                'review' => $request->review,
            ]
        );

        return redirect()
            ->route('courses.index')
            ->with('success', 'Rating berhasil disimpan! Lihat rekomendasi course untuk Anda di bagian atas.');
    }
}
