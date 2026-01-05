# Online Course Recommender System 🎓

Sistem rekomendasi course online berbasis Laravel dan Python dengan **auto-training model** menggunakan Collaborative Filtering (SVD).

## ✨ Fitur Utama

-   🤖 **Auto-Training Model** - Model otomatis di-train ketika ada rating baru
-   📊 **Collaborative Filtering** - Menggunakan SVD (Singular Value Decomposition)
-   🔄 **Asynchronous Training** - Training berjalan di background menggunakan Laravel Queue
-   📈 **Real-time Recommendations** - Rekomendasi selalu up-to-date
-   🎯 **Personalized** - Rekomendasi disesuaikan dengan preferensi setiap user
-   🐍 **Python Microservice** - API terpisah untuk scalability

## 🏗️ Arsitektur

```
Laravel (Frontend & Backend)
    ↓
Rating System
    ↓
Observer → Queue Job
    ↓
Python Microservice (Flask API)
    ↓
SVD Recommender Model
    ↓
MySQL Database
```

## 🚀 Quick Start

### 1. Setup Laravel

```bash
# Clone repository
git clone <repository-url>
cd online_course

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=online_course_recommender
DB_USERNAME=root
DB_PASSWORD=

# Queue configuration
QUEUE_CONNECTION=database

# Recommender service URL
RECOMMENDER_URL=http://localhost:5000
```

### 2. Setup Database

```bash
# Create database
mysql -u root -p
CREATE DATABASE online_course_recommender;
exit

# Run migrations
php artisan migrate

# Seed data (optional)
php artisan db:seed
```

### 3. Setup Python Microservice

```bash
cd python

# Install dependencies
pip install -r requirements_api.txt

# Test API server
python api_server.py
```

API akan berjalan di `http://localhost:5000`

### 4. Setup Queue Worker (PENTING untuk Auto-Training!)

Buka terminal baru:

```bash
php artisan queue:work --tries=3
```

### 5. Run Laravel

```bash
# Terminal baru
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## 🤖 Auto-Training Model

Fitur utama sistem ini adalah **auto-training otomatis**. Setiap kali user memberi rating:

1. ✅ Rating disimpan ke database
2. 📢 `RatingObserver` mendeteksi rating baru
3. 📤 `TrainRecommenderModel` Job dikirim ke queue
4. ⏱️ Job dieksekusi setelah 2 detik delay
5. 🧠 Model di-train dengan data terbaru
6. ✨ Rekomendasi langsung up-to-date!

**📖 Dokumentasi lengkap**: [AUTO_TRAINING.md](AUTO_TRAINING.md)

### Quick Test Auto-Training

```bash
# Terminal 1: Queue Worker
php artisan queue:work

# Terminal 2: Python API
cd python
python api_server.py

# Terminal 3: Laravel
php artisan serve

# Buka browser, login, dan beri rating pada course
# Cek log untuk melihat auto-training berjalan!
```

## 📁 Struktur Proyek

```
├── app/
│   ├── Jobs/
│   │   └── TrainRecommenderModel.php    # Job untuk auto-training
│   ├── Observers/
│   │   └── RatingObserver.php           # Deteksi rating baru
│   ├── Services/
│   │   └── RecommenderService.php       # API client
│   └── Models/
│       ├── Course.php
│       ├── Rating.php
│       └── User.php
├── python/
│   ├── api_server.py                     # Flask API
│   ├── recommender.py                    # SVD Model
│   └── requirements_api.txt
├── AUTO_TRAINING.md                      # Dokumentasi auto-training
└── README.md                             # Dokumentasi ini
```

## 🔧 Konfigurasi

### Fast Training vs Full Training

**Fast Training** (Default untuk auto-training):

-   ⚡ Waktu: ~5-10 detik
-   🎯 Akurasi: Good
-   📝 `perform_tuning = false`

**Full Training** (Manual/Scheduled):

-   🐢 Waktu: ~30-60 detik
-   🎯 Akurasi: Best
-   📝 `perform_tuning = true`

### Menonaktifkan Auto-Training

Edit [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php):

```php
public function boot(): void
{
    // Comment baris ini untuk nonaktifkan auto-training
    // Rating::observe(RatingObserver::class);
}
```

## 📊 API Endpoints

### Python Microservice (Port 5000)

```bash
# Health check
GET http://localhost:5000/api/health

# Train model
POST http://localhost:5000/api/train
{
    "perform_tuning": false
}

# Get recommendations
GET http://localhost:5000/api/recommend/1?top_n=5

# Predict rating
POST http://localhost:5000/api/predict
{
    "user_id": 1,
    "course_id": 5
}
```

### Laravel API (Port 8000)

```bash
# Train via Laravel
POST http://localhost:8000/api/recommender/train

# Get recommendations via Laravel
GET http://localhost:8000/api/recommender/recommend/1?top_n=5
```

## 📈 Monitoring

### Cek Log Auto-Training

```bash
tail -f storage/logs/laravel.log
```

Expected output:

```
[2026-01-05 10:30:15] 📊 Rating created, memulai auto-training...
[2026-01-05 10:30:17] 🤖 Auto-training model dimulai...
[2026-01-05 10:30:25] ✅ Model berhasil di-train! RMSE: 0.85
```

### Cek Queue Status

```bash
# List failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Flush failed jobs
php artisan queue:flush
```

## 🎯 Best Practices

### Development

-   ✅ Nonaktifkan auto-training (training manual saja)
-   ✅ Gunakan `sync` queue: `QUEUE_CONNECTION=sync`

### Production

-   ✅ Aktifkan auto-training
-   ✅ Gunakan `database` atau `redis` queue
-   ✅ Setup Supervisor untuk queue worker
-   ✅ Schedule full training malam hari:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new TrainRecommenderModel(['perform_tuning' => true]))
        ->dailyAt('02:00');
}
```

## 🐛 Troubleshooting

### Queue job tidak berjalan

```bash
# Cek queue worker
ps aux | grep "queue:work"

# Restart queue worker
php artisan queue:restart
```

### Python API tidak bisa diakses

```bash
# Cek Python API running
curl http://localhost:5000/api/health

# Restart API
cd python
python api_server.py
```

### Training gagal

```bash
# Cek Python dependencies
cd python
pip install -r requirements_api.txt

# Cek database connection di Python
python -c "from recommender import CourseRecommender; r = CourseRecommender()"
```

## 📚 Dokumentasi Tambahan

-   [AUTO_TRAINING.md](AUTO_TRAINING.md) - Detail auto-training system
-   [FILE_DOCUMENTATION.md](FILE_DOCUMENTATION.md) - Penjelasan setiap file
-   [PRESENTATION_GUIDE.md](PRESENTATION_GUIDE.md) - Panduan presentasi
-   [SCHEDULED_TRAINING.md](SCHEDULED_TRAINING.md) - Setup scheduled training
-   [python/README.md](python/README.md) - Python microservice docs

## 🛠️ Tech Stack

-   **Backend**: Laravel 11
-   **Frontend**: Blade, TailwindCSS
-   **ML/AI**: Python, Scikit-surprise (SVD)
-   **API**: Flask
-   **Database**: MySQL
-   **Queue**: Laravel Queue (Database/Redis)
-   **Cache**: Laravel Cache

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing
