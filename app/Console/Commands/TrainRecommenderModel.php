<?php
namespace App\Console\Commands;

use App\Services\RecommenderService;
use Illuminate\Console\Command;

class TrainRecommenderModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recommender:train {--force : Force training even if recently trained}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Train the course recommendation model with latest ratings data';

    protected RecommenderService $recommenderService;

    /**
     * Create a new command instance.
     */
    public function __construct(RecommenderService $recommenderService)
    {
        parent::__construct();
        $this->recommenderService = $recommenderService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting recommendation model training...');

        // Check service health first
        $health = $this->recommenderService->healthCheck();

        if (! $health['success']) {
            $this->error('❌ Recommender microservice is not available!');
            $this->error('   Please start the microservice: cd python && start_api_server.bat');
            return 1;
        }

        $this->info('✓ Microservice is online');
        $this->newLine();

        // Start training
        $this->info('Training model... This may take a while.');

        $startTime = microtime(true);
        $result    = $this->recommenderService->trainModel();
        $duration  = round(microtime(true) - $startTime, 2);

        if ($result['success']) {
            $this->newLine();
            $this->info('✅ Model trained successfully!');
            $this->info("   Duration: {$duration} seconds");

            if (isset($result['data']['data_size'])) {
                $this->info("   Training data: {$result['data']['data_size']} ratings");
            }

            if (isset($result['data']['metrics'])) {
                $metrics = $result['data']['metrics'];
                $this->info("   RMSE: " . round($metrics['rmse'], 4));
                $this->info("   MAE: " . round($metrics['mae'], 4));
            }

            // Clear cache
            $this->info('🗑️  Clearing recommendation cache...');
            $this->recommenderService->clearCache();
            $this->info('✓ Cache cleared');

            $this->newLine();
            $this->info('🎉 All done! Recommendations are now up-to-date.');

            return 0;
        } else {
            $this->error('❌ Training failed!');
            $this->error('   Error: ' . ($result['error'] ?? 'Unknown error'));

            if (isset($result['details'])) {
                $details = is_array($result['details'])
                    ? json_encode($result['details'], JSON_PRETTY_PRINT)
                    : $result['details'];
                $this->error('   Details: ' . $details);
            }

            return 1;
        }
    }
}
