<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            <i class="fas fa-book mr-2"></i>{{ $course->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
                        <div
                            class="h-64 bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                            <i class="fas fa-book text-9xl text-white opacity-50"></i>
                        </div>
                        <div class="p-8">
                            <div class="flex items-center gap-3 mb-4">
                                <span
                                    class="px-4 py-1 bg-purple-100 text-purple-600 font-semibold rounded-full">{{ $course->category }}</span>
                                <span
                                    class="px-4 py-1 bg-gray-100 text-gray-600 rounded-full">{{ $course->level }}</span>
                            </div>

                            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $course->title }}</h1>

                            <div class="flex items-center gap-6 mb-6 text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    <span>{{ $course->instructor }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock mr-2"></i>
                                    <span>{{ $course->duration_hours }} hours</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-star text-yellow-400 mr-1"></i>
                                    <span
                                        class="font-semibold">{{ number_format($course->averageRating() ?? 0, 1) }}</span>
                                    <span class="ml-1">({{ $course->ratingsCount() }} ratings)</span>
                                </div>
                            </div>

                            <h3 class="text-xl font-bold text-gray-900 mb-3">Description</h3>
                            <p class="text-gray-600 leading-relaxed mb-6">{{ $course->description }}</p>

                            <div class="border-t pt-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-500 text-sm">Price</p>
                                        <p class="text-3xl font-bold text-purple-600">Rp
                                            {{ number_format($course->price, 0, ',', '.') }}</p>
                                    </div>
                                    <button
                                        class="px-8 py-3 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition">
                                        <i class="fas fa-shopping-cart mr-2"></i>Enroll Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ratings Section -->
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Student Reviews</h3>

                        @forelse($course->ratings as $rating)
                            <div class="border-b pb-6 mb-6 last:border-0">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-purple-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $rating->user->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $rating->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="fas fa-star {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                @if ($rating->review)
                                    <p class="text-gray-600">{{ $rating->review }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-8">No reviews yet. Be the first to review!</p>
                        @endforelse
                    </div>
                </div>

                <!-- Sidebar - Rating Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md p-6 sticky top-6">
                        @auth
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Rate this Course</h3>

                            @if ($userRating)
                                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                                    <p class="text-purple-600 font-semibold mb-2">Your Rating</p>
                                    <div class="flex items-center mb-2">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="fas fa-star {{ $i <= $userRating->rating ? 'text-yellow-400' : 'text-gray-300' }} text-xl"></i>
                                        @endfor
                                    </div>
                                    @if ($userRating->review)
                                        <p class="text-gray-600 text-sm">{{ $userRating->review }}</p>
                                    @endif
                                </div>
                                <p class="text-gray-500 text-sm mb-4">Update your rating:</p>
                            @else
                                <p class="text-gray-500 mb-4">Share your experience with this course</p>
                            @endif

                            <form method="POST" action="{{ route('courses.rate', $course) }}">
                                @csrf

                                <div class="mb-4">
                                    <label class="block text-gray-700 font-semibold mb-2">Your Rating</label>
                                    <div class="flex gap-2" id="star-rating">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <button type="button" data-rating="{{ $i }}"
                                                class="star-btn text-3xl {{ $userRating && $i <= $userRating->rating ? 'text-yellow-400' : 'text-gray-400' }} hover:text-yellow-500 transition transform hover:scale-110">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" id="rating-input"
                                        value="{{ $userRating->rating ?? '' }}" required>
                                    @error('rating')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-gray-700 font-semibold mb-2">Your Review (Optional)</label>
                                    <textarea name="review" rows="4"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="Share your thoughts about this course...">{{ $userRating->review ?? '' }}</textarea>
                                    @error('review')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                    class="w-full px-6 py-3 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="fas fa-paper-plane mr-2"></i>Submit Rating
                                </button>
                            </form>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-lock text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-600 mb-3">You need to login to rate this course</p>
                                <a href="{{ route('login') }}"
                                    class="inline-block px-6 py-2 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Login Now
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    @auth
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const stars = document.querySelectorAll('.star-btn');
                const ratingInput = document.getElementById('rating-input');

                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        const rating = this.dataset.rating;
                        ratingInput.value = rating;

                        stars.forEach(s => {
                            if (s.dataset.rating <= rating) {
                                s.classList.remove('text-gray-300');
                                s.classList.add('text-yellow-400');
                            } else {
                                s.classList.remove('text-yellow-400');
                                s.classList.add('text-gray-300');
                            }
                        });
                    });
                });
            });
        </script>
    @endauth
</x-app-layout>
