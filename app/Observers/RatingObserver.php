<?php
namespace App\Observers;

use App\Jobs\TrainRecommenderModel;
use App\Models\Rating;
use Illuminate\Support\Facades\Log;

/**
 * Observer untuk model Rating
 *
 * Otomatis trigger training model ketika rating dibuat atau diupdate
 */
class RatingObserver
{
    /**
     * Dipanggil setelah rating dibuat
     */
    public function created(Rating $rating): void
    {
        $this->triggerAutoTraining('created', $rating);
    }

    /**
     * Dipanggil setelah rating diupdate
     */
    public function updated(Rating $rating): void
    {
        // Hanya trigger training jika rating value berubah
        if ($rating->wasChanged('rating')) {
            $this->triggerAutoTraining('updated', $rating);
        }
    }

    /**
     * Trigger auto-training model
     */
    private function triggerAutoTraining(string $event, Rating $rating): void
    {
        Log::info("📊 Rating {$event}, memulai auto-training model...", [
            'user_id'   => $rating->user_id,
            'course_id' => $rating->course_id,
            'rating'    => $rating->rating,
        ]);

        // Dispatch job ke queue untuk training asinkron
        // Menggunakan delay 2 detik untuk menghindari multiple training simultan
        TrainRecommenderModel::dispatch()->delay(now()->addSeconds(2));
    }
}
