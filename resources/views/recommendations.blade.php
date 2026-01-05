<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course Recommendations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Model Performance Metrics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">📊 Model Performance (RMSE)</h3>
                    <div id="model-metrics">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-gray-900 mr-3 inline-block">
                        </div>
                        <span>Loading metrics...</span>
                    </div>

                    <!-- Penjelasan RMSE -->
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="font-semibold text-blue-900 mb-2">💡 Apa itu RMSE?</h4>
                        <p class="text-sm text-blue-800 mb-2">
                            <strong>RMSE (Root Mean Square Error)</strong> adalah metrik untuk mengukur akurasi prediksi
                            model rekomendasi.
                        </p>
                        <ul class="text-sm text-blue-700 space-y-1 ml-4">
                            <li>• <strong>Nilai lebih rendah = lebih baik</strong></li>
                            <li>• RMSE < 1.0=Prediksi sangat akurat</li>
                            <li>• RMSE 1.0-2.0 = Prediksi cukup baik</li>
                            <li>• RMSE > 2.0 = Perlu improvement</li>
                        </ul>
                        <p class="text-xs text-blue-600 mt-2">
                            Contoh: Jika RMSE = 0.85, rata-rata error prediksi rating adalah ±0.85 dari rating
                            sebenarnya.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Recommended Courses for You</h3>
                        <button onclick="refreshRecommendations()"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Refresh
                        </button>
                    </div>

                    <div id="recommendations-loading" class="text-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto mb-4"></div>
                        <p class="text-gray-600">Loading recommendations...</p>
                    </div>

                    <div id="recommendations-error"
                        class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <p id="error-message"></p>
                    </div>

                    <div id="recommendations-list" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Recommendations will be inserted here -->
                    </div>

                    <div id="no-recommendations" class="hidden text-center py-8 text-gray-500">
                        <p>No recommendations available yet.</p>
                        <p class="text-sm mt-2">Try rating some courses first!</p>
                    </div>
                </div>
            </div>

            <!-- Admin Controls -->
            @if (auth()->user()->email === 'admin@example.com')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Admin Controls</h3>

                        <div class="flex gap-4">
                            <button onclick="trainModel()"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Train Model
                            </button>

                            <button onclick="clearCache()"
                                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                Clear Cache
                            </button>
                        </div>

                        <div id="admin-result" class="mt-4 hidden">
                            <!-- Admin action results -->
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Check model metrics
        async function checkModelMetrics() {
            try {
                const response = await fetch('/recommendations/metrics');
                const data = await response.json();

                console.log('Metrics response:', data); // Debug log

                const metricsDiv = document.getElementById('model-metrics');

                if (data.success && data.data && data.data.metrics) {
                    const metrics = data.data.metrics;

                    if (metrics.model_trained && metrics.rmse) {
                        // Determine color based on RMSE value
                        let rmseColor = 'green';
                        let rmseText = 'Sangat Baik';
                        if (metrics.rmse > 2.0) {
                            rmseColor = 'red';
                            rmseText = 'Perlu Improvement';
                        } else if (metrics.rmse > 1.0) {
                            rmseColor = 'yellow';
                            rmseText = 'Cukup Baik';
                        }

                        metricsDiv.innerHTML = `
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="h-6 w-6 text-${rmseColor}-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-2xl font-bold text-${rmseColor}-600">${metrics.rmse.toFixed(4)}</p>
                                        <p class="text-sm text-gray-500">RMSE Score</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-${rmseColor}-600">${rmseText}</p>
                                    <p class="text-sm text-gray-500">${metrics.data_size} ratings data</p>
                                    ${metrics.mae ? `<p class="text-xs text-gray-400">MAE: ${metrics.mae.toFixed(4)}</p>` : ''}
                                </div>
                            </div>
                        `;
                    } else {
                        metricsDiv.innerHTML = `
                            <div class="flex items-center text-yellow-600">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>Model belum di-train. Silakan train model terlebih dahulu.</span>
                            </div>
                        `;
                    }
                } else {
                    metricsDiv.innerHTML = `
                        <div class="flex items-center text-red-600">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Gagal load metrics. Service mungkin offline.</span>
                        </div>
                    `;
                }
            } catch (error) {
                const metricsDiv = document.getElementById('model-metrics');
                console.error('Metrics error:', error);
                metricsDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="font-semibold text-red-800">❌ Python API Service Offline</p>
                                <p class="text-sm text-red-700 mt-1">Pastikan Python API sudah berjalan di <code class="bg-red-100 px-1 py-0.5 rounded">http://localhost:5000</code></p>
                                <div class="mt-2 text-xs text-red-600 bg-red-100 p-2 rounded">
                                    <p class="font-semibold">Cara menjalankan:</p>
                                    <p class="mt-1"><code>cd python</code></p>
                                    <p><code>python api_server.py</code></p>
                                </div>
                                <button onclick="checkModelMetrics()" class="mt-2 text-xs bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                    🔄 Retry
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        // Check service health
        // Load recommendations
        async function loadRecommendations() {
            const loadingDiv = document.getElementById('recommendations-loading');
            const errorDiv = document.getElementById('recommendations-error');
            const listDiv = document.getElementById('recommendations-list');
            const noRecsDiv = document.getElementById('no-recommendations');

            loadingDiv.classList.remove('hidden');
            errorDiv.classList.add('hidden');
            listDiv.classList.add('hidden');
            noRecsDiv.classList.add('hidden');

            try {
                const response = await fetch('/recommendations?top_n=6');
                const data = await response.json();

                loadingDiv.classList.add('hidden');

                if (data.success && data.recommendations && data.recommendations.length > 0) {
                    listDiv.classList.remove('hidden');
                    listDiv.innerHTML = data.recommendations.map(rec => `
                        <div class="border border-gray-200 rounded-lg p-5 hover:shadow-xl transition bg-white">
                            <h4 class="font-bold text-xl mb-2 text-gray-800">${rec.course_name}</h4>
                            <div class="flex items-center mb-3">
                                <span class="text-yellow-500 font-semibold text-lg mr-2">★ ${rec.predicted_rating.toFixed(1)}</span>
                                <span class="text-gray-500 text-sm">Predicted for you</span>
                            </div>
                            ${rec.category ? `<span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mb-3">${rec.category}</span>` : ''}
                            <p class="text-gray-600 text-sm mb-3 leading-relaxed">${rec.description ? rec.description.substring(0, 120) + '...' : 'Discover this course recommended just for you!'}</p>
                            ${rec.instructor ? `<p class="text-gray-500 text-xs mb-4"><span class="font-semibold">Instructor:</span> ${rec.instructor}</p>` : ''}
                            <a href="/courses/${rec.course_id}"
                               class="inline-block bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 text-sm">
                                View Course Details →
                            </a>
                        </div>
                    `).join('');
                } else {
                    noRecsDiv.classList.remove('hidden');
                }
            } catch (error) {
                loadingDiv.classList.add('hidden');
                errorDiv.classList.remove('hidden');
                document.getElementById('error-message').textContent =
                    'Failed to load recommendations. Please make sure the microservice is running.';
            }
        }

        // Refresh recommendations
        function refreshRecommendations() {
            loadRecommendations();
        }

        // Train model (admin)
        async function trainModel() {
            const resultDiv = document.getElementById('admin-result');
            resultDiv.classList.remove('hidden');
            resultDiv.innerHTML = `
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-700 mr-3"></div>
                        Training model... This may take a while.
                    </div>
                </div>
            `;

            try {
                const response = await fetch('/recommendations/train', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();

                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            <p class="font-semibold">✓ Model trained successfully!</p>
                            ${data.metrics ? `
                                                <p class="text-sm mt-1">RMSE: ${data.metrics.rmse.toFixed(4)}, MAE: ${data.metrics.mae.toFixed(4)}</p>
                                                <p class="text-sm">Training data: ${data.data_size} ratings</p>
                                            ` : ''}
                        </div>
                    `;
                    // Refresh metrics and recommendations after training
                    setTimeout(() => {
                        checkModelMetrics();
                        loadRecommendations();
                    }, 1000);
                } else {
                    resultDiv.innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <p class="font-semibold">✗ Training failed</p>
                            <p class="text-sm">${data.message || 'Unknown error'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <p class="font-semibold">✗ Error: ${error.message}</p>
                    </div>
                `;
            }
        }

        // Clear cache (admin)
        async function clearCache() {
            const resultDiv = document.getElementById('admin-result');
            resultDiv.classList.remove('hidden');

            try {
                const response = await fetch('/recommendations/clear-cache', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();

                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            ✓ Cache cleared successfully!
                        </div>
                    `;
                    loadRecommendations();
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        ✗ Error: ${error.message}
                    </div>
                `;
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkModelMetrics();
            loadRecommendations();
        });
    </script>
</x-app-layout>
