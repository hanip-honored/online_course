<?php
namespace App\Jobs;

use App\Services\RecommenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk melatih ulang model rekomendasi secara asinkron
 *
 * Job ini dipanggil otomatis ketika ada rating baru untuk
 * memastikan rekomendasi selalu up-to-date.
 */
class TrainRecommenderModel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan jika gagal
     */
    public $tries = 3;

    /**
     * Timeout dalam detik (2 menit)
     */
    public $timeout = 120;

    /**
     * Parameter training (opsional)
     */
    protected array $params;

    /**
     * Create a new job instance.
     */
    public function __construct(array $params = [])
    {
        // Jika perform_tuning tidak diset, default ke false untuk training lebih cepat
        $this->params = array_merge([
            'perform_tuning' => false, // Skip hyperparameter tuning untuk speed
        ], $params);
    }

    /**
     * Execute the job.
     */
    public function handle(RecommenderService $recommenderService): void
    {
        Log::info('Auto-training model dimulai setelah rating baru...', $this->params);

        try {
            $result = $recommenderService->trainModel($this->params);

            if ($result['success']) {
                Log::info('Model berhasil di-train otomatis!', [
                    'metrics' => $result['data']['metrics'] ?? null,
                ]);
            } else {
                Log::warning('Training model gagal', [
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                // Throw exception untuk retry
                throw new \Exception($result['error'] ?? 'Training failed');
            }
        } catch (\Exception $e) {
            Log::error('Error saat auto-training model', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw untuk queue retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job TrainRecommenderModel gagal setelah ' . $this->tries . ' percobaan', [
            'error' => $exception->getMessage(),
        ]);
    }
}
