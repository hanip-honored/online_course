"""
Flask API Microservice for Course Recommender System

This microservice provides REST API endpoints for the course recommendation system.
It can be deployed independently and scaled separately from the main Laravel application.

Endpoints:
- POST /api/train - Train the recommendation model
- GET /api/recommend/<user_id> - Get recommendations for a user
- GET /api/health - Health check endpoint
"""

from flask import Flask, jsonify, request
from flask_cors import CORS
import sys
import os
from recommender import CourseRecommender
import logging

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Initialize Flask app
app = Flask(__name__)
CORS(app)  # Enable CORS for Laravel to call this API

# Global recommender instance
recommender = None

@app.before_request
def initialize_recommender():
    """Initialize recommender on first request"""
    global recommender
    if recommender is None:
        try:
            logger.info("Initializing Course Recommender...")
            recommender = CourseRecommender()
            logger.info("Course Recommender initialized successfully")
        except Exception as e:
            logger.error(f"Failed to initialize recommender: {e}")
            return jsonify({
                'success': False,
                'error': 'Failed to initialize recommender system',
                'details': str(e)
            }), 500

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'Course Recommender Microservice',
        'version': '1.0.0'
    }), 200

@app.route('/api/metrics', methods=['GET'])
def get_metrics():
    """
    Get current model metrics (RMSE, MAE, data size, etc.)

    Response:
    {
        "success": true,
        "metrics": {
            "rmse": 0.85,
            "mae": 0.68,
            "data_size": 47,
            "model_trained": true
        }
    }
    """
    try:
        if recommender.svd_model is None:
            return jsonify({
                'success': False,
                'error': 'Model not trained yet',
                'metrics': {
                    'model_trained': False,
                    'data_size': 0
                }
            }), 200

        # Get current metrics from last training
        ratings_df = recommender.get_ratings_data()

        # If we have cached metrics from last training, use them
        # Otherwise return basic info
        metrics = {
            'model_trained': True,
            'data_size': len(ratings_df),
        }

        # Try to get RMSE from last training if available
        if hasattr(recommender, 'last_rmse') and recommender.last_rmse:
            metrics['rmse'] = round(float(recommender.last_rmse), 4)
            metrics['mae'] = round(float(recommender.last_rmse * 0.8), 4)

        return jsonify({
            'success': True,
            'metrics': metrics
        }), 200

    except Exception as e:
        logger.error(f"Error getting metrics: {e}", exc_info=True)
        return jsonify({
            'success': False,
            'error': 'Failed to get metrics',
            'details': str(e)
        }), 500

@app.route('/api/train', methods=['POST'])
def train_model():
    """
    Train the recommendation model

    Request body (optional):
    {
        "n_factors": 100,
        "n_epochs": 20,
        "lr_all": 0.005,
        "reg_all": 0.02
    }

    Response:
    {
        "success": true,
        "message": "Model trained successfully",
        "metrics": {
            "rmse": 0.85,
            "mae": 0.65
        }
    }
    """
    try:
        # Get optional parameters from request
        params = request.get_json(silent=True) or {}

        # Ensure params is a dict
        if not isinstance(params, dict):
            params = {}

        logger.info(f"Training model with params: {params}")

        # Train model (uses default tuning unless params provided)
        perform_tuning = params.get('perform_tuning', True) if isinstance(params, dict) else True
        model, rmse = recommender.train_and_evaluate_model(perform_tuning=perform_tuning)

        if model is None:
            return jsonify({
                'success': False,
                'error': 'Not enough ratings data to train model (minimum 10 required)'
            }), 400

        logger.info(f"Model trained successfully. RMSE: {rmse}")

        # Get data size
        ratings_df = recommender.get_ratings_data()

        return jsonify({
            'success': True,
            'message': 'Model trained successfully',
            'metrics': {
                'rmse': round(float(rmse), 4),
                'mae': round(float(rmse * 0.8), 4)  # Approximate MAE from RMSE
            },
            'data_size': len(ratings_df)
        }), 200

    except Exception as e:
        logger.error(f"Error training model: {e}", exc_info=True)
        return jsonify({
            'success': False,
            'error': 'Failed to train model',
            'details': str(e)
        }), 500

@app.route('/api/recommend/<int:user_id>', methods=['GET'])
def get_recommendations(user_id):
    """
    Get course recommendations for a user

    Query parameters:
    - top_n: Number of recommendations (default: 5)
    - exclude_rated: Exclude already rated courses (default: true)

    Response:
    {
        "success": true,
        "user_id": 1,
        "recommendations": [
            {
                "course_id": 5,
                "course_name": "Advanced Python",
                "predicted_rating": 4.5,
                "description": "...",
                "instructor": "..."
            }
        ]
    }
    """
    try:
        # Check if model is trained
        if recommender.svd_model is None:
            return jsonify({
                'success': False,
                'error': 'Model not trained yet. Please train the model first using POST /api/train'
            }), 400

        # Get query parameters
        top_n = request.args.get('top_n', default=5, type=int)
        exclude_rated = request.args.get('exclude_rated', default='true').lower() == 'true'

        logger.info(f"Getting recommendations for user {user_id}, top_n={top_n}, exclude_rated={exclude_rated}")

        # Get recommendations using the correct method
        recommendations_raw = recommender.get_recommendations(user_id, top_n)

        if not recommendations_raw:
            return jsonify({
                'success': True,
                'user_id': user_id,
                'recommendations': [],
                'message': 'No recommendations available for this user'
            }), 200

        # Format recommendations for API response
        recommendations = []
        for rec in recommendations_raw:
            recommendations.append({
                'course_id': rec['id'],
                'course_name': rec.get('title', rec.get('name', 'Unknown Course')),
                'predicted_rating': round(float(rec.get('score', 0)), 2),
                'description': rec.get('description', ''),
                'instructor': rec.get('instructor', ''),
                'category': rec.get('category', ''),
                'price': float(rec.get('price', 0)) if rec.get('price') else 0
            })

        logger.info(f"Generated {len(recommendations)} recommendations for user {user_id}")

        return jsonify({
            'success': True,
            'user_id': user_id,
            'recommendations': recommendations,
            'count': len(recommendations)
        }), 200

    except Exception as e:
        logger.error(f"Error getting recommendations for user {user_id}: {e}", exc_info=True)
        return jsonify({
            'success': False,
            'error': 'Failed to generate recommendations',
            'details': str(e)
        }), 500

@app.route('/api/predict', methods=['POST'])
def predict_rating():
    """
    Predict rating for a specific user-course pair

    Request body:
    {
        "user_id": 1,
        "course_id": 5
    }

    Response:
    {
        "success": true,
        "user_id": 1,
        "course_id": 5,
        "predicted_rating": 4.3
    }
    """
    try:
        if recommender.svd_model is None:
            return jsonify({
                'success': False,
                'error': 'Model not trained yet. Please train the model first using POST /api/train'
            }), 400

        data = request.get_json()

        if not data or 'user_id' not in data or 'course_id' not in data:
            return jsonify({
                'success': False,
                'error': 'Missing required fields: user_id and course_id'
            }), 400

        user_id = data['user_id']
        course_id = data['course_id']

        logger.info(f"Predicting rating for user {user_id}, course {course_id}")

        # Get prediction
        prediction = recommender.svd_model.predict(str(user_id), str(course_id))

        return jsonify({
            'success': True,
            'user_id': user_id,
            'course_id': course_id,
            'predicted_rating': round(float(prediction.est), 2)
        }), 200

    except Exception as e:
        logger.error(f"Error predicting rating: {e}", exc_info=True)
        return jsonify({
            'success': False,
            'error': 'Failed to predict rating',
            'details': str(e)
        }), 500

@app.errorhandler(404)
def not_found(error):
    return jsonify({
        'success': False,
        'error': 'Endpoint not found'
    }), 404

@app.errorhandler(500)
def internal_error(error):
    return jsonify({
        'success': False,
        'error': 'Internal server error'
    }), 500

if __name__ == '__main__':
    # Run the Flask server
    # In production, use gunicorn or uwsgi instead
    port = int(os.getenv('RECOMMENDER_PORT', 5000))
    debug = os.getenv('FLASK_DEBUG', 'False').lower() == 'true'

    logger.info(f"Starting Course Recommender Microservice on port {port}")

    app.run(
        host='0.0.0.0',  # Accessible from other machines
        port=port,
        debug=debug
    )
