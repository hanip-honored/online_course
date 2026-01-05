<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-lg p-8 mb-6 text-white">
                <h3 class="text-3xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}!</h3>
                <p class="text-purple-100">Ready to continue your learning journey?</p>
            </div>

            <!-- Quick Stats -->
            <div class="grid md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold">Courses Rated</p>
                            <p class="text-3xl font-bold text-purple-600">{{ auth()->user()->ratings()->count() }}</p>
                        </div>
                        <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-star text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold">Avg Rating Given</p>
                            <p class="text-3xl font-bold text-indigo-600">
                                {{ number_format(auth()->user()->ratings()->avg('rating') ?? 0, 1) }}</p>
                        </div>
                        <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-chart-line text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold">Total Courses</p>
                            <p class="text-3xl font-bold text-pink-600">{{ \App\Models\Course::count() }}</p>
                        </div>
                        <div class="w-14 h-14 bg-pink-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-book text-2xl text-pink-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid md:grid-cols-2 gap-6">
                <a href="{{ route('courses.index') }}"
                    class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition group">
                    <div class="flex items-center">
                        <div
                            class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mr-4 group-hover:scale-110 transition">
                            <i class="fas fa-search text-2xl text-purple-600"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-gray-900 mb-1">Browse Courses</h4>
                            <p class="text-gray-600">Explore our wide range of courses</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('recommendations.index') }}"
                    class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition group">
                    <div class="flex items-center">
                        <div
                            class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mr-4 group-hover:scale-110 transition">
                            <i class="fas fa-magic text-2xl text-indigo-600"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-gray-900 mb-1">Get Recommendations</h4>
                            <p class="text-gray-600">Personalized course suggestions</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
