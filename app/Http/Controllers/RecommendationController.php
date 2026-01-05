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

                $result = $this->recommenderService->getRecommendations(
                    userId: $user->id,
                    topN: $topN,
                    excludeRated: true
                );

                if (! $result['success']) {
                    \Log::error('Recommendations failed', $result);
                    return response()->json([
                        'success' => false,
                        'error'   => 'Failed to get recommendations',
                        'message' => $result['error'] ?? 'Unknown error',
                        'details' => $result['details'] ?? null,
                    ], 200);
                }

                return response()->json($result['data']);
            } catch (\Exception $e) {
                \Log::error('Exception in recommendations', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'error'   => 'System error',
                    'message' => $e->getMessage(),
                ], 200);
            }
        }

        // Otherwise, show the view
        return view('recommendations');
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
