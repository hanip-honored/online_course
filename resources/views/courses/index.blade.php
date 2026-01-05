<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                <i class="fas fa-book mr-2"></i>Browse Courses
            </h2>
            <a href="{{ route('recommendations.index') }}"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-star mr-2"></i>My Recommendations
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Search & Filter -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form method="GET" action="{{ route('courses.index') }}" class="grid md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search courses..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <select name="category"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                    {{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="px-6 py-2 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition shadow-md">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </form>
            </div>

            <!-- Courses Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($courses as $course)
                    @php
                        $isRecommended = in_array($course->id, $recommendedCourseIds);
                    @endphp
                    <div
                        class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition {{ $isRecommended ? 'ring-2 ring-orange-400' : '' }}">
                        <div
                            class="relative h-48 bg-gradient-to-br {{ $isRecommended ? 'from-orange-400 to-orange-600' : 'from-purple-500 to-indigo-600' }} flex items-center justify-center">
                            <i class="fas fa-book text-6xl text-white opacity-50"></i>
                            @if ($isRecommended && isset($course->recommendation_score))
                                <div
                                    class="absolute top-2 right-2 bg-yellow-400 text-gray-900 px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                    <i
                                        class="fas fa-star mr-1"></i>{{ number_format($course->recommendation_score, 2) }}
                                </div>
                            @endif
                        </div>
                        <div class="p-6">
                            @if ($isRecommended)
                                <div class="mb-3 flex items-center">
                                    <span
                                        class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-orange-500 to-orange-600 text-white text-xs font-bold rounded-full shadow-md">
                                        <i class="fas fa-magic mr-1"></i>Rekomendasi untuk Anda
                                    </span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between mb-2">
                                <span
                                    class="px-3 py-1 {{ $isRecommended ? 'bg-orange-100 text-orange-600' : 'bg-purple-100 text-purple-600' }} text-xs font-semibold rounded-full">{{ $course->category }}</span>
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
                                <span class="{{ $isRecommended ? 'text-orange-600' : 'text-purple-600' }} font-bold">Rp
                                    {{ number_format($course->price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center text-gray-500 text-sm mb-4">
                                <i class="fas fa-user-tie mr-2"></i>
                                <span>{{ $course->instructor }}</span>
                            </div>
                            <a href="{{ route('courses.show', $course) }}"
                                class="block w-full text-center px-4 py-2 {{ $isRecommended ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700' : 'bg-purple-600 hover:bg-purple-700' }} text-white font-semibold rounded-lg transition">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-3 text-center py-12">
                        <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">No courses found</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $courses->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
