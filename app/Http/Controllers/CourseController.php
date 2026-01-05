<?php
namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::query();

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('instructor', 'like', '%' . $request->search . '%');
            });
        }

        $courses = $query->paginate(9);

        // Get all categories for filter
        $categories = Course::distinct()->pluck('category');

        // Get recommendations for logged in users with ratings
        $recommendedCourseIds = [];
        $hasRatings           = false;

        if (auth()->check() && ! $request->has('search') && ! $request->has('category')) {
            $user       = auth()->user();
            $hasRatings = $user->ratings()->exists();

            if ($hasRatings) {
                // Call Python script untuk get recommendations
                $batchPath = base_path('python/run_recommender.bat');
                $command   = "\"{$batchPath}\" {$user->id} 6";

                try {
                    $result = Process::timeout(120)
                        ->env([
                            'JOBLIB_MULTIPROCESSING' => '0',
                            'LOKY_MAX_CPU_COUNT'     => '1',
                        ])
                        ->run($command);

                    if ($result->successful()) {
                        $recommendedCourses = json_decode($result->output(), true);

                        if (! empty($recommendedCourses)) {
                            // Get recommended course IDs
                            $recommendedCourseIds = array_column($recommendedCourses, 'id');

                            // Get recommended courses with scores
                            $recCourses = Course::whereIn('id', $recommendedCourseIds)->get();

                            // Map with scores
                            $recommendationsWithScore = [];
                            foreach ($recommendedCourses as $rec) {
                                $course = $recCourses->firstWhere('id', $rec['id']);
                                if ($course) {
                                    $course->recommendation_score = $rec['score'];
                                    $recommendationsWithScore[]   = $course;
                                }
                            }

                            // Get other courses (not in recommendations)
                            $otherCourses = $courses->filter(function ($course) use ($recommendedCourseIds) {
                                return ! in_array($course->id, $recommendedCourseIds);
                            });

                            // Merge: recommendations first, then other courses
                            $mergedCourses = collect($recommendationsWithScore)->concat($otherCourses);

                            // Replace courses collection
                            $courses->setCollection($mergedCourses);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Exception in recommendations', ['error' => $e->getMessage()]);
                }
            }
        }

        return view('courses.index', compact('courses', 'categories', 'recommendedCourseIds', 'hasRatings'));
    }

    public function show(Course $course)
    {
        // Get course with ratings
        $course->load('ratings.user');

        // Check if current user has rated this course
        $userRating = null;
        if (auth()->check()) {
            $userRating = Rating::where('user_id', auth()->id())
                ->where('course_id', $course->id)
                ->first();
        }

        return view('courses.show', compact('course', 'userRating'));
    }

    /**
     * Redirect to new microservice-based recommendations
     * @deprecated Use RecommendationController instead
     */
    public function recommendations()
    {
        // Redirect to new microservice-based recommendations
        return redirect()->route('recommendations.index');
    }
}
