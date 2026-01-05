<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service class for interacting with the Course Recommender Microservice
 *
 * This service handles all communication with the Python-based recommendation
 * microservice via HTTP API calls.
 */
class RecommenderService
{
    /**
     * Base URL of the recommender microservice
     */
    private string $baseUrl;

    /**
     * HTTP request timeout in seconds
     */
    private int $timeout;

    /**
     * Cache TTL in seconds (default: 1 hour)
     */
    private int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl  = config('services.recommender.url', 'http://localhost:5000');
        $this->timeout  = config('services.recommender.timeout', 30);
        $this->cacheTtl = config('services.recommender.cache_ttl', 3600);
    }

    /**
     * Check if the recommender service is healthy
     *
     * @return array
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/health");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error'   => 'Health check failed',
                'status'  => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Recommender health check failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Train the recommendation model
     *
     * @param array $params Optional training parameters
     * @return array
     */
    public function trainModel(array $params = []): array
    {
        try {
            $response = Http::timeout(120) // Longer timeout for training
                ->post("{$this->baseUrl}/api/train", $params);

            if ($response->successful()) {
                // Clear recommendation cache after training
                Cache::flush();

                Log::info('Model trained successfully', $response->json());

                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            Log::error('Model training failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            $responseData = $response->json();

            return [
                'success' => false,
                'error'   => $responseData['error'] ?? 'Training failed',
                'details' => $responseData['details'] ?? $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Model training exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Get course recommendations for a user
     *
     * @param int $userId
     * @param int $topN Number of recommendations
     * @param bool $excludeRated Exclude already rated courses
     * @return array
     */
    public function getRecommendations(int $userId, int $topN = 5, bool $excludeRated = true): array
    {
        try {
            // Check cache first
            $cacheKey = "recommendations.user.{$userId}.top.{$topN}.exclude.{$excludeRated}";

            if (Cache::has($cacheKey)) {
                Log::info('Returning cached recommendations', ['user_id' => $userId]);
                return Cache::get($cacheKey);
            }

            // Make API request
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/recommend/{$userId}", [
                    'top_n'         => $topN,
                    'exclude_rated' => $excludeRated ? 'true' : 'false',
                ]);

            if ($response->successful()) {
                $data = [
                    'success' => true,
                    'data'    => $response->json(),
                ];

                // Cache the result
                Cache::put($cacheKey, $data, $this->cacheTtl);

                return $data;
            }

            Log::error('Failed to get recommendations', [
                'user_id' => $userId,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);

            return [
                'success' => false,
                'error'   => 'Failed to get recommendations',
                'details' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Recommendation exception', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Predict rating for a specific user-course pair
     *
     * @param int $userId
     * @param int $courseId
     * @return array
     */
    public function predictRating(int $userId, int $courseId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/predict", [
                    'user_id'   => $userId,
                    'course_id' => $courseId,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error'   => 'Prediction failed',
                'details' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Rating prediction exception', [
                'user_id'   => $userId,
                'course_id' => $courseId,
                'error'     => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Clear all recommendation caches
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::flush();
        Log::info('Recommendation cache cleared');
    }

    /**
     * Get model metrics (RMSE, MAE, etc.)
     *
     * @return array
     */
    public function getModelMetrics(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/metrics");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            Log::warning('Metrics request failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'error'   => 'Failed to get metrics',
                'status'  => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Get model metrics exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
