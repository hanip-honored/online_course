<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            <i class="fas fa-star mr-2"></i>Personalized Recommendations
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($message)
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-6 py-4 rounded-lg mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-2xl mr-3 mt-1"></i>
                        <div>
                            <p class="font-semibold mb-2">{{ $hasRatings ? 'Information' : 'Get Started' }}</p>
                            <p>{{ $message }}</p>
                            @if (!$hasRatings)
                                <a href="{{ route('courses.index') }}"
                                    class="inline-block mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-book mr-2"></i>Browse Courses
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($hasRatings && count($recommendations) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-magic text-purple-600 mr-2"></i>Courses Recommended for You
                    </h3>
                    <p class="text-gray-600 mb-4">Based on your rating history and preferences using SVD algorithm</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($recommendations as $rec)
                        @php $course = $rec['course']; @endphp
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                            <div
                                class="h-48 bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center relative">
                                <i class="fas fa-book text-6xl text-white opacity-50"></i>
                                <div
                                    class="absolute top-3 right-3 bg-yellow-400 text-gray-900 px-3 py-1 rounded-full font-bold text-sm flex items-center">
                                    <i class="fas fa-star mr-1"></i>{{ number_format($rec['score'], 2) }}
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span
                                        class="px-3 py-1 bg-purple-100 text-purple-600 text-xs font-semibold rounded-full">{{ $course->category }}</span>
                                    <span class="text-gray-500 text-sm">{{ $course->level }}</span>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $course->title }}</h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $course->description }}</p>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span
                                            class="font-semibold">{{ number_format($course->averageRating() ?? 0, 1) }}</span>
                                        <span class="text-gray-500 text-sm ml-1">({{ $course->ratingsCount() }})</span>
                                    </div>
                                    <span class="text-purple-600 font-bold">Rp
                                        {{ number_format($course->price, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center text-gray-500 text-sm mb-4">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    <span>{{ $course->instructor }}</span>
                                </div>
                                <a href="{{ route('courses.show', $course) }}"
                                    class="block w-full text-center px-4 py-2 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Info Box -->
                <div class="mt-8 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-lg p-6">
                    <h4 class="font-bold text-gray-900 mb-3">
                        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>How it works
                    </h4>
                    <p class="text-gray-700 mb-3">
                        Our recommendation system uses <strong>SVD (Singular Value Decomposition)</strong>, a powerful
                        matrix factorization technique that:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Analyzes patterns in your rating history</li>
                        <li>Identifies latent features that connect users and courses</li>
                        <li>Predicts your preferences for courses you haven't rated yet</li>
                        <li>Continuously improves as you rate more courses</li>
                    </ul>
                    <p class="text-gray-600 mt-4 text-sm italic">
                        💡 Tip: Rate more courses to get even better recommendations!
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
