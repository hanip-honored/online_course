import os
import sys
import json
import types
import mysql.connector
import numpy as np
import pandas as pd

# Set environment to disable multiprocessing before importing surprise
os.environ['JOBLIB_MULTIPROCESSING'] = '0'
os.environ['LOKY_MAX_CPU_COUNT'] = '1'

# Stub joblib to avoid importing asyncio/windows_events
joblib_stub = types.SimpleNamespace(
    dump=lambda *a, **k: None,
    load=lambda *a, **k: None,
    Parallel=None,
    delayed=lambda f, *a, **k: f,
    Memory=None,
)
sys.modules['joblib'] = joblib_stub
sys.modules['joblib.parallel'] = joblib_stub
sys.modules['joblib.memory'] = joblib_stub

# Patch import *before* loading surprise to skip model_selection (avoids asyncio on Windows)
import builtins
original_import = builtins.__import__

def custom_import(name, *args, **kwargs):
    if 'surprise.model_selection' in name:
        return type('module', (), {})()
    return original_import(name, *args, **kwargs)

builtins.__import__ = custom_import
from surprise import SVD, Dataset, Reader, accuracy
builtins.__import__ = original_import

from dotenv import load_dotenv

# Load environment variables from Laravel .env file
load_dotenv('../.env')

class CourseRecommender:
    """
    Model-Based Collaborative Filtering using SVD from Surprise Library

    This recommender uses the Surprise library's SVD algorithm which implements
    matrix factorization using Stochastic Gradient Descent (SGD).

    Key Features:
    - Train-test split for evaluation
    - RMSE/MAE metrics for model evaluation
    - GridSearchCV for hyperparameter tuning
    - Individual prediction capability
    """

    def __init__(self):
        self.connection = None
        self.svd_model = None
        self.trainset = None
        self.best_params = None
        self.last_rmse = None  # Store last training RMSE
        self.connect_db()

        # Try to load existing model
        self.load_model()

    def connect_db(self):
        """Connect to MySQL database using Laravel .env credentials"""
        try:
            self.connection = mysql.connector.connect(
                host=os.getenv('DB_HOST', '127.0.0.1'),
                user=os.getenv('DB_USERNAME', 'root'),
                password=os.getenv('DB_PASSWORD', ''),
                database=os.getenv('DB_DATABASE', 'online_course_recommender'),
                port=int(os.getenv('DB_PORT', 3306)),
                use_pure=True  # Use pure Python implementation to avoid DLL issues
            )
        except Exception as e:
            print(f"Error connecting to database: {e}", file=sys.stderr)
            sys.exit(1)

    def get_ratings_data(self):
        """Fetch all ratings from database"""
        cursor = self.connection.cursor()
        query = """
            SELECT user_id, course_id, rating
            FROM ratings
        """
        cursor.execute(query)
        results = cursor.fetchall()
        cursor.close()

        # Convert to DataFrame
        df = pd.DataFrame(results, columns=['user_id', 'course_id', 'rating'])
        return df

    def prepare_surprise_dataset(self, ratings_df):
        """
        Prepare data for Surprise library
        Surprise expects: user_id, item_id, rating
        """
        # Define rating scale (1-5 or 1-10 depending on your system)
        reader = Reader(rating_scale=(1, 5))

        # Load data into Surprise Dataset
        data = Dataset.load_from_df(
            ratings_df[['user_id', 'course_id', 'rating']],
            reader
        )
        return data

    def manual_train_test_split(self, data, test_size=0.25, random_state=42):
        """
        Manual train-test split to avoid importing model_selection
        which causes asyncio issues on Windows
        """
        import random
        random.seed(random_state)
        np.random.seed(random_state)

        # Build full trainset first
        full_trainset = data.build_full_trainset()

        # Get all ratings
        all_ratings = [(uid, iid, rating) for (uid, iid, rating) in full_trainset.all_ratings()]

        # Shuffle
        random.shuffle(all_ratings)

        # Split
        split_index = int(len(all_ratings) * (1 - test_size))
        train_ratings = all_ratings[:split_index]
        test_ratings = all_ratings[split_index:]

        # Build trainset from train_ratings
        reader = Reader(rating_scale=(1, 5))
        train_df = pd.DataFrame(train_ratings, columns=['user_id', 'course_id', 'rating'])
        train_data = Dataset.load_from_df(train_df, reader)
        trainset = train_data.build_full_trainset()

        # Build testset format: list of (uid, iid, real_rating)
        testset = [(uid, iid, rating) for (uid, iid, rating) in test_ratings]

        return trainset, testset

    def manual_grid_search(self, data, param_grid, test_size=0.25, random_state=42):
        """Small manual grid search without joblib/model_selection (sequential)."""
        best_rmse = float('inf')
        best_params = None

        for params in param_grid:
            try:
                trainset, testset = self.manual_train_test_split(data, test_size=test_size, random_state=random_state)
                model = SVD(**params)
                model.fit(trainset)
                preds = model.test(testset)
                rmse = accuracy.rmse(preds, verbose=False)
                if rmse < best_rmse:
                    best_rmse = rmse
                    best_params = params
            except Exception as e:
                print(f"Tuning error with params {params}: {e}", file=sys.stderr)
                continue

        return best_params, best_rmse

    def train_and_evaluate_model(self, perform_tuning=True):
        """
        Train SVD model with optional hyperparameter tuning
        Returns: trained model, RMSE score

        Uses manual grid search (sequential) to avoid joblib/asyncio issues.
        """
        # Get ratings data
        ratings_df = self.get_ratings_data()

        if ratings_df.empty or len(ratings_df) < 10:
            print("Not enough rating data to train model", file=sys.stderr)
            return None, None

        # Prepare Surprise dataset
        data = self.prepare_surprise_dataset(ratings_df)

        # Split data into train and test sets (75% train, 25% test)
        trainset, testset = self.manual_train_test_split(data, test_size=0.25, random_state=42)

        if perform_tuning:
            # Compact grid to keep latency reasonable
            param_grid = [
                {'n_epochs': 5, 'lr_all': 0.002, 'n_factors': 80},
                {'n_epochs': 8, 'lr_all': 0.003, 'n_factors': 100},
                {'n_epochs': 12, 'lr_all': 0.005, 'n_factors': 120},
            ]
            best_params, best_rmse = self.manual_grid_search(data, param_grid, test_size=0.25, random_state=42)
            if best_params:
                self.best_params = best_params
                print(f"Manual tuning best RMSE: {best_rmse:.4f} with {best_params}", file=sys.stderr)

        # Use tuned or default parameters
        if self.best_params:
            self.svd_model = SVD(**self.best_params)
        else:
            self.svd_model = SVD(n_epochs=8, lr_all=0.003, n_factors=100)

        # Train on trainset
        self.svd_model.fit(trainset)

        # Evaluate on testset
        predictions = self.svd_model.test(testset)
        rmse = accuracy.rmse(predictions, verbose=False)

        # Store RMSE for metrics endpoint
        self.last_rmse = rmse

        print(f"Model trained. RMSE: {rmse:.4f}", file=sys.stderr)

        # Train final model on full dataset for production use
        full_trainset = data.build_full_trainset()
        self.svd_model.fit(full_trainset)
        self.trainset = full_trainset

        # Save model to file
        self.save_model()

        return self.svd_model, rmse

    def save_model(self):
        """Save model to pickle file"""
        try:
            import pickle
            model_path = 'model_svd.pkl'
            with open(model_path, 'wb') as f:
                pickle.dump({
                    'model': self.svd_model,
                    'trainset': self.trainset,
                    'rmse': self.last_rmse,
                    'best_params': self.best_params
                }, f)
            print(f"Model saved to {model_path}", file=sys.stderr)
        except Exception as e:
            print(f"Warning: Could not save model: {e}", file=sys.stderr)

    def load_model(self):
        """Load model from pickle file"""
        try:
            import pickle
            model_path = 'model_svd.pkl'
            if os.path.exists(model_path):
                with open(model_path, 'rb') as f:
                    data = pickle.load(f)
                    self.svd_model = data['model']
                    self.trainset = data['trainset']
                    self.last_rmse = data.get('rmse')
                    self.best_params = data.get('best_params')
                print(f"Model loaded from {model_path}", file=sys.stderr)
                return True
        except Exception as e:
            print(f"Could not load model: {e}", file=sys.stderr)
        return False

    def get_course_info(self, course_id):
        """Get course information"""
        cursor = self.connection.cursor(dictionary=True)
        query = "SELECT * FROM courses WHERE id = %s"
        cursor.execute(query, (course_id,))
        result = cursor.fetchone()
        cursor.close()
        return result

    def get_user_rated_courses(self, user_id):
        """Get courses that user has already rated"""
        cursor = self.connection.cursor()
        query = "SELECT course_id FROM ratings WHERE user_id = %s"
        cursor.execute(query, (user_id,))
        results = cursor.fetchall()
        cursor.close()
        return [row[0] for row in results]

    def get_all_courses(self):
        """Get all course IDs from database"""
        cursor = self.connection.cursor()
        query = "SELECT id FROM courses"
        cursor.execute(query)
        results = cursor.fetchall()
        cursor.close()
        return [row[0] for row in results]

    def predict_rating(self, user_id, course_id, verbose=False):
        """
        Predict rating for a specific user-course pair
        Similar to: svd_model.predict(uid=1.0, iid=541, verbose=True)
        """
        if self.svd_model is None:
            self.train_and_evaluate_model()

        try:
            prediction = self.svd_model.predict(user_id, course_id, verbose=verbose)
            return prediction
        except Exception as e:
            print(f"Prediction error: {e}", file=sys.stderr)
            return None

    def svd_based_recommendation(self, user_id, top_n=5):
        """
        Get top N course recommendations for a user using trained SVD model
        Returns list of tuples: (course_id, predicted_rating_score)
        """
        # Ensure model is trained
        if self.svd_model is None:
            self.train_and_evaluate_model()

        if self.svd_model is None:
            return self.get_popular_courses(top_n)

        # Get all courses
        all_courses = self.get_all_courses()

        # Get courses user has already rated
        user_rated_courses = self.get_user_rated_courses(user_id)

        # Filter out already rated courses
        courses_to_predict = [c for c in all_courses if c not in user_rated_courses]

        if not courses_to_predict:
            return self.get_popular_courses(top_n)

        # Predict ratings for all unrated courses
        predictions = []
        for course_id in courses_to_predict:
            try:
                pred = self.svd_model.predict(user_id, course_id)
                predictions.append((course_id, pred.est))
            except Exception as e:
                # Skip courses that can't be predicted
                continue

        # Sort by predicted rating (descending) and get top N
        predictions.sort(key=lambda x: x[1], reverse=True)
        top_recommendations = predictions[:top_n]

        return top_recommendations

    def get_popular_courses(self, top_n=5):
        """Get most popular courses based on average rating with scores"""
        cursor = self.connection.cursor()
        query = """
            SELECT course_id, AVG(rating) as avg_rating, COUNT(*) as rating_count
            FROM ratings
            GROUP BY course_id
            HAVING rating_count >= 2
            ORDER BY avg_rating DESC, rating_count DESC
            LIMIT %s
        """
        cursor.execute(query, (top_n,))
        results = cursor.fetchall()
        cursor.close()

        # Return as list of tuples (course_id, score)
        return [(int(row[0]), float(row[1])) for row in results]

    def get_recommendations(self, user_id, top_n=5):
        """
        Main method to get recommendations for a user using SVD
        Returns list of course details with scores
        """
        # Get recommended course IDs with scores using SVD
        course_id_scores = self.svd_based_recommendation(user_id, top_n)

        # If no recommendations, return popular courses
        if not course_id_scores:
            course_id_scores = self.get_popular_courses(top_n)

        # Get course details with scores
        recommendations = []
        for course_id, score in course_id_scores:
            course_info = self.get_course_info(course_id)
            if course_info:
                course_info['score'] = score
                recommendations.append(course_info)

        return recommendations

    def retrain_model(self, perform_tuning=False):
        """
        Retrain the model (useful when new ratings are added)
        Can optionally perform hyperparameter tuning
        """
        return self.train_and_evaluate_model(perform_tuning=perform_tuning)

    def close(self):
        """Close database connection"""
        if self.connection:
            self.connection.close()

def main():
    """Main function to handle CLI calls from Laravel"""
    if len(sys.argv) < 2:
        print("Usage: python recommender.py <user_id> [top_n]", file=sys.stderr)
        sys.exit(1)

    user_id = int(sys.argv[1])
    top_n = int(sys.argv[2]) if len(sys.argv) > 2 else 5

    # Initialize recommender
    recommender = CourseRecommender()

    try:
        # Get recommendations
        recommendations = recommender.get_recommendations(user_id, top_n)

        # Output as JSON
        print(json.dumps(recommendations, default=str))

    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)

    finally:
        recommender.close()

if __name__ == "__main__":
    main()
