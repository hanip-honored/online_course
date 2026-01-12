<?php
namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\RecommenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    protected RecommenderService $recommenderService;

    public function __construct(RecommenderService $recommenderService)
    {
        $this->recommenderService = $recommenderService;
    }

    /**
     * Get personalized course recommendations for the authenticated user
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->has('top_n')) {
            try {
                $user = Auth::user();
                $topN = $request->input('top_n', 5);

                // Check if user has any ratings
                $userRatingsCount = \App\Models\Rating::where('user_id', $user->id)->count();

                if ($userRatingsCount === 0) {
                    // User has no ratings, return popular courses
                    $popularCourses = Course::getPopularCourses($topN, 2);

                    return response()->json([
                        'success'         => true,
                        'type'            => 'popular',
                        'recommendations' => $popularCourses->map(function ($course) {
                            return [
                                'course_id'        => $course->id,
                                'predicted_rating' => $course->predicted_rating,
                                'course_name'      => $course->title,
                                'title'            => $course->title,
                                'category'         => $course->category,
                                'description'      => $course->description,
                            ];
                        }),
                    ]);
                }

                $result = $this->recommenderService->getRecommendations(
                    userId: $user->id,
                    topN: $topN,
                    excludeRated: true
                );

                if (! $result['success']) {
                    \Log::error('Recommendations failed', $result);

                    $popularCourses = Course::getPopularCourses($topN, 2);

                    return response()->json([
                        'success'         => true,
                        'type'            => 'popular',
                        'recommendations' => $popularCourses->map(function ($course) {
                            return [
                                'course_id'        => $course->id,
                                'predicted_rating' => $course->predicted_rating,
                                'course_name'      => $course->title,
                                'title'            => $course->title,
                                'category'         => $course->category,
                                'description'      => $course->description,
                            ];
                        }),
                    ]);
                }

                return response()->json($result['data']);
            } catch (\Exception $e) {
                \Log::error('Exception in recommendations', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Fallback to popular courses on exception
                try {
                    $popularCourses = Course::getPopularCourses($request->input('top_n', 5), 2);

                    return response()->json([
                        'success'         => true,
                        'type'            => 'popular',
                        'recommendations' => $popularCourses->map(function ($course) {
                            return [
                                'course_id'        => $course->id,
                                'predicted_rating' => $course->predicted_rating,
                                'course_name'      => $course->title,
                                'title'            => $course->title,
                                'category'         => $course->category,
                                'description'      => $course->description,
                            ];
                        }),
                    ]);
                } catch (\Exception $fallbackException) {
                    return response()->json([
                        'success' => false,
                        'error'   => 'System error',
                        'message' => $e->getMessage(),
                    ], 200);
                }
            }
        }

        // For view requests, get user's rating count
        $user             = Auth::user();
        $userRatingsCount = \App\Models\Rating::where('user_id', $user->id)->count();

        if ($userRatingsCount === 0) {
            // User has no ratings, show popular courses
            $popularCourses = Course::getPopularCourses(10, 2);

            return view('recommendations', [
                'recommendationType' => 'popular',
                'message'            => 'Mulai beri rating pada course untuk mendapatkan rekomendasi yang dipersonalisasi!',
                'courses'            => $popularCourses,
            ]);
        }

        // User has ratings, get personalized recommendations
        $result = $this->recommenderService->getRecommendations($user->id, 10, true);

        if ($result['success']) {
            $recommendations = $result['data']['recommendations'] ?? [];

            // Get full course details
            $courseIds = array_column($recommendations, 'course_id');
            $courses   = Course::whereIn('id', $courseIds)->get()->keyBy('id');

            // Merge predictions with course data
            $recommendedCourses = collect($recommendations)->map(function ($rec) use ($courses) {
                if (isset($courses[$rec['course_id']])) {
                    $course                   = $courses[$rec['course_id']];
                    $course->predicted_rating = $rec['predicted_rating'];
                    return $course;
                }
                return null;
            })->filter();

            return view('recommendations', [
                'recommendationType' => 'personalized',
                'message'            => null,
                'courses'            => $recommendedCourses,
            ]);
        }

        // If recommendation failed, fallback to popular courses
        $popularCourses = Course::getPopularCourses(10, 2);

        return view('recommendations', [
            'recommendationType' => 'popular',
            'message'            => 'Tidak dapat memuat rekomendasi personal. Menampilkan course populer.',
            'courses'            => $popularCourses,
        ]);
    }

    /**
     * Train the recommendation model
     * (Admin only - add middleware as needed)
     */
    public function train(Request $request)
    {
        $params = $request->only(['n_factors', 'n_epochs', 'lr_all', 'reg_all']);

        $result = $this->recommenderService->trainModel($params);

        if (! $result['success']) {
            return response()->json([
                'error'   => 'Failed to train model',
                'message' => $result['error'] ?? 'Unknown error',
            ], 500);
        }

        return response()->json($result['data']);
    }

    /**
     * Predict rating for a specific course
     */
    public function predictRating(Request $request, int $courseId)
    {
        $user = Auth::user();

        $result = $this->recommenderService->predictRating(
            userId: $user->id,
            courseId: $courseId
        );

        if (! $result['success']) {
            return response()->json([
                'error'   => 'Failed to predict rating',
                'message' => $result['error'] ?? 'Unknown error',
            ], 500);
        }

        return response()->json($result['data']);
    }

    /**
     * Health check for recommender service
     */
    public function health()
    {
        $result = $this->recommenderService->healthCheck();

        return response()->json($result, $result['success'] ? 200 : 503);
    }

    /**
     * Get model metrics (RMSE, MAE, etc.)
     */
    public function metrics()
    {
        $result = $this->recommenderService->getModelMetrics();

        return response()->json($result, $result['success'] ? 200 : 503);
    }

    /**
     * Clear recommendation cache
     */
    public function clearCache()
    {
        $this->recommenderService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully',
        ]);
    }
}
