# 📚 Course Recommender System - File Documentation

## 🎯 Sistem Overview

**Arsitektur:** Microservice dengan Flask API + Laravel Frontend
**Algoritma:** Collaborative Filtering menggunakan SVD (Singular Value Decomposition)
**Database:** MySQL untuk menyimpan ratings

---

## 📁 File Structure dan Fungsinya

### 🐍 Python Microservice (Recommendation Engine)

#### **Core Files - WAJIB ADA**

1. **`recommender.py`** ⭐ UTAMA

    - **Fungsi:** Core recommendation engine
    - **Algoritma:** SVD (Singular Value Decomposition) dari Surprise library
    - **Isi:**
        - `CourseRecommender` class
        - `train_and_evaluate_model()` - Train model dengan rating data
        - `get_recommendations()` - Generate rekomendasi untuk user
        - `predict_rating()` - Prediksi rating user untuk course tertentu
    - **Input:** Rating data dari MySQL (user_id, course_id, rating)
    - **Output:** List rekomendasi dengan predicted rating

2. **`api_server.py`** ⭐ UTAMA

    - **Fungsi:** Flask REST API microservice
    - **Endpoints:**
        - `GET /api/health` - Health check
        - `POST /api/train` - Train/retrain model
        - `GET /api/recommend/<user_id>` - Get recommendations
        - `POST /api/predict` - Predict rating
    - **Port:** 5000
    - **CORS:** Enabled untuk Laravel

3. **`start_api_server.bat`**

    - **Fungsi:** Script untuk start Flask server
    - **Cara pakai:** Double-click atau run di terminal
    - **Requirement:** Conda environment 'online_course'

4. **`requirements_api.txt`**
    - **Fungsi:** Python dependencies untuk microservice
    - **Packages:**
        - Flask, flask-cors - Web server
        - scikit-surprise - Collaborative filtering
        - pandas, numpy - Data processing
        - mysql-connector-python - Database

#### **Supporting Files**

5. **`explain_recommendations.py`**

    - **Fungsi:** Tool untuk explain kenapa course direkomendasikan
    - **Output:** JSON dengan rating history, category preferences, reasons
    - **Untuk:** Analisis dan demo ke dosen

6. **`test_api.ps1` + `test_api.bat`**
    - **Fungsi:** Testing script untuk API endpoints
    - **Opsional:** Bisa dihapus atau keep untuk demo

#### **Documentation**

7. **`README_MICROSERVICE.md`**

    - Dokumentasi lengkap microservice
    - API endpoints
    - Deployment guide

8. **`QUICKSTART.md`**
    - Quick start guide
    - Testing instructions

---

### 🎨 Laravel Application (Frontend & Integration)

#### **Backend Integration**

1. **`app/Services/RecommenderService.php`** ⭐ UTAMA

    - **Fungsi:** Service class untuk call Python microservice
    - **Methods:**
        - `getRecommendations()` - Get recommendations via HTTP
        - `trainModel()` - Trigger training
        - `healthCheck()` - Check service status
        - `clearCache()` - Clear cache
    - **Features:** Caching (1 jam), error handling, logging

2. **`app/Http/Controllers/RecommendationController.php`** ⭐ UTAMA

    - **Fungsi:** Controller untuk handle recommendation requests
    - **Routes:**
        - `GET /recommendations` - View atau JSON
        - `POST /recommendations/train` - Train model
        - `GET /recommendations/health` - Health check
        - `POST /recommendations/clear-cache` - Clear cache

3. **`app/Console/Commands/TrainRecommenderModel.php`** ⭐ UTAMA

    - **Fungsi:** Artisan command untuk scheduled training
    - **Command:** `php artisan recommender:train`
    - **Output:** Progress, metrics, duration

4. **`routes/console.php`**

    - **Fungsi:** Scheduled task configuration
    - **Schedule:** Train model setiap jam
    - **Auto:** `php artisan schedule:work`

5. **`config/services.php`**
    - **Fungsi:** Configuration untuk microservice connection
    - **Config:** URL, timeout, cache TTL

#### **Frontend**

6. **`resources/views/recommendations.blade.php`** ⭐ UTAMA
    - **Fungsi:** UI untuk display recommendations
    - **Features:**
        - Service status indicator
        - Recommendations grid dengan AJAX
        - Admin controls (train, clear cache)
        - Real-time loading states

---

## 🔄 Workflow Training & Recommendation

### **Flow Diagram:**

```
┌─────────────────────────────────────────────────────────────┐
│                      USER RATES COURSE                      │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│              Save Rating to MySQL Database                  │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│         Every Hour: Schedule Triggers Training              │
│         (php artisan schedule:work)                         │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Laravel: TrainRecommenderModel Command Executed         │
│    (app/Console/Commands/TrainRecommenderModel.php)        │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Laravel → HTTP POST → Flask API (:5000/api/train)       │
│    (app/Services/RecommenderService.php)                   │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Flask API: api_server.py handles request                │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Python: recommender.py                                   │
│    1. Connect to MySQL                                      │
│    2. Fetch all ratings (user_id, course_id, rating)       │
│    3. Build rating matrix                                   │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    SVD Algorithm (Surprise Library)                         │
│    1. Matrix Factorization: User × Course vectors          │
│    2. Train with SGD optimization                           │
│    3. Evaluate RMSE/MAE on test set                        │
│    4. Store trained model in memory                         │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Return: {"success": true, "rmse": 1.0, "mae": 0.8}     │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Laravel: Clear recommendation cache                      │
└─────────────────────────────────────────────────────────────┘


                    RECOMMENDATION FLOW
                           ↓
┌─────────────────────────────────────────────────────────────┐
│    User visits /recommendations page                        │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Browser AJAX → Laravel RecommendationController         │
│    GET /recommendations?top_n=6                             │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Laravel → HTTP GET → Flask API                           │
│    (:5000/api/recommend/10?top_n=6)                        │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Python: recommender.py                                   │
│    1. Get all courses user hasn't rated                     │
│    2. Use trained SVD model to predict ratings              │
│    3. Sort by predicted rating DESC                         │
│    4. Get course details from MySQL                         │
│    5. Return top N courses                                  │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Return JSON: [                                           │
│      {course_id: 5, course_name: "...",                    │
│       predicted_rating: 4.5, category: "..."}              │
│    ]                                                        │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    Laravel caches result (1 hour)                          │
│    Returns JSON to browser                                  │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│    JavaScript displays recommendations in UI                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🧠 Algoritma Collaborative Filtering (SVD)

### **Input Data:**

```
user_id | course_id | rating
--------|-----------|-------
   1    |     5     |   4
   1    |     8     |   5
   2    |     5     |   3
   2    |    12     |   5
```

### **SVD Process:**

1. **Matrix Factorization:**

    ```
    Rating Matrix (sparse) = User Matrix × Course Matrix
    R (m×n) ≈ U (m×k) × V (k×n)
    ```

2. **Training:**

    - Optimize dengan Stochastic Gradient Descent (SGD)
    - Minimize error: `(actual_rating - predicted_rating)²`
    - Parameters: n_factors=100, n_epochs=20

3. **Prediction:**

    ```python
    predicted_rating = user_vector · course_vector + biases
    ```

4. **Evaluation:**
    - RMSE (Root Mean Square Error) - lower is better
    - MAE (Mean Absolute Error)

### **Output:**

-   Predicted rating untuk setiap (user, course) pair
-   Top-N courses dengan highest predicted ratings

---

## 🚀 Cara Menjalankan Sistem

### **Start Microservice:**

```bash
cd python
start_api_server.bat
```

### **Start Laravel:**

```bash
php artisan serve
```

### **Start Scheduler (Optional):**

```bash
php artisan schedule:work
```

### **Manual Training:**

```bash
php artisan recommender:train
```

### **Test API:**

```bash
curl http://localhost:5000/api/health
```

---

## 📊 Files untuk Presentasi Dosen

### **Demo Files:**

1. `python/explain_recommendations.py` - Jelaskan rekomendasi
2. `python/test_api.ps1` - Test semua endpoints
3. `SCHEDULED_TRAINING.md` - Dokumentasi scheduling

### **Core Files to Show:**

1. `python/recommender.py` - Algoritma SVD
2. `python/api_server.py` - REST API
3. `app/Services/RecommenderService.php` - Integration
4. `resources/views/recommendations.blade.php` - UI

### **Architecture Diagram:**

```
┌──────────────┐      HTTP API      ┌─────────────────┐
│   Browser    │ ←──────────────→   │  Laravel App    │
│              │                     │   (Port 8000)   │
└──────────────┘                     └────────┬────────┘
                                              │ HTTP
                                              ↓
                                     ┌─────────────────┐
                                     │  Flask API      │
                                     │   (Port 5000)   │
                                     │  recommender.py │
                                     └────────┬────────┘
                                              │ SQL
                                              ↓
                                     ┌─────────────────┐
                                     │  MySQL Database │
                                     │   (Ratings)     │
                                     └─────────────────┘
```

---

## ✅ Checklist untuk Demo:

-   [ ] Flask microservice running (port 5000)
-   [ ] Laravel app running (port 8000)
-   [ ] Model trained (check: `php artisan recommender:train`)
-   [ ] Database has ratings data
-   [ ] Test recommendations: `/recommendations`
-   [ ] Show explanation: `python explain_recommendations.py 10`
-   [ ] Show scheduler: `php artisan schedule:work`

**Good luck with your presentation! 🎓**
