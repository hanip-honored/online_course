<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LearnHub - Your Learning Journey Starts Here</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>

<body class="antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-graduation-cap text-3xl text-purple-600"></i>
                    <span class="text-2xl font-bold gradient-text">LearnHub</span>
                </div>

                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-indigo-700 transition transform hover:scale-105">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-purple-600 font-medium transition">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}"
                            class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-indigo-700 transition transform hover:scale-105">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20 lg:py-32">
        <div class="absolute inset-0 gradient-primary opacity-5"></div>
        <div class="container mx-auto px-6 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Learn Without <span class="gradient-text">Limits</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Discover thousands of courses from expert instructors. Build your skills and advance your career
                        with personalized recommendations.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('register') }}"
                            class="px-8 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-indigo-700 transition transform hover:scale-105 shadow-lg">
                            <i class="fas fa-rocket mr-2"></i>
                            Start Learning Now
                        </a>
                        <a href="#courses"
                            class="px-8 py-4 bg-white text-purple-600 font-semibold rounded-lg hover:bg-gray-50 transition border-2 border-purple-600">
                            <i class="fas fa-search mr-2"></i>
                            Explore Courses
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 mt-12">
                        <div>
                            <div class="text-3xl font-bold text-purple-600">10K+</div>
                            <div class="text-gray-600">Courses</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-purple-600">50K+</div>
                            <div class="text-gray-600">Students</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-purple-600">500+</div>
                            <div class="text-gray-600">Instructors</div>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <div class="floating relative">
                        <div class="bg-white rounded-2xl shadow-2xl p-8 relative z-10">
                            <div class="flex items-center space-x-4 mb-6">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-code text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900">Web Development</h3>
                                    <p class="text-gray-500 text-sm">12 courses available</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Progress</span>
                                    <span class="text-purple-600 font-semibold">75%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 h-2 rounded-full"
                                        style="width: 75%"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Decorative Elements -->
                        <div class="absolute -top-6 -right-6 w-32 h-32 bg-purple-200 rounded-full opacity-50"></div>
                        <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-indigo-200 rounded-full opacity-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Choose LearnHub?</h2>
                <p class="text-xl text-gray-600">Everything you need to succeed in your learning journey</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="p-8 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl hover:shadow-xl transition">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-brain text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">AI-Powered Recommendations</h3>
                    <p class="text-gray-600">Get personalized course suggestions based on your learning history and
                        interests.</p>
                </div>

                <div class="p-8 bg-gradient-to-br from-pink-50 to-rose-50 rounded-2xl hover:shadow-xl transition">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-chalkboard-teacher text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Expert Instructors</h3>
                    <p class="text-gray-600">Learn from industry professionals with years of real-world experience.</p>
                </div>

                <div class="p-8 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl hover:shadow-xl transition">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-certificate text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Certificates</h3>
                    <p class="text-gray-600">Earn recognized certificates upon completion to boost your career.</p>
                </div>

                <div class="p-8 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl hover:shadow-xl transition">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-mobile-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Learn Anywhere</h3>
                    <p class="text-gray-600">Access courses on any device, anytime, anywhere at your own pace.</p>
                </div>

                <div class="p-8 bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl hover:shadow-xl transition">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Community Support</h3>
                    <p class="text-gray-600">Join a vibrant community of learners and get help when you need it.</p>
                </div>

                <div class="p-8 bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl hover:shadow-xl transition">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-infinity text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Lifetime Access</h3>
                    <p class="text-gray-600">Get unlimited access to all course materials, even after completion.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Categories -->
    <section id="courses" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Popular Categories</h2>
                <p class="text-xl text-gray-600">Explore our wide range of courses</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="group bg-white p-6 rounded-xl hover:shadow-lg transition cursor-pointer">
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <i class="fas fa-code text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Web Development</h3>
                    <p class="text-gray-600 text-sm mb-3">120 courses</p>
                    <span class="text-purple-600 text-sm font-medium group-hover:underline">Explore →</span>
                </div>

                <div class="group bg-white p-6 rounded-xl hover:shadow-lg transition cursor-pointer">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <i class="fas fa-chart-line text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Data Science</h3>
                    <p class="text-gray-600 text-sm mb-3">85 courses</p>
                    <span class="text-blue-600 text-sm font-medium group-hover:underline">Explore →</span>
                </div>

                <div class="group bg-white p-6 rounded-xl hover:shadow-lg transition cursor-pointer">
                    <div
                        class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <i class="fas fa-palette text-2xl text-pink-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Design</h3>
                    <p class="text-gray-600 text-sm mb-3">95 courses</p>
                    <span class="text-pink-600 text-sm font-medium group-hover:underline">Explore →</span>
                </div>

                <div class="group bg-white p-6 rounded-xl hover:shadow-lg transition cursor-pointer">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <i class="fas fa-bullhorn text-2xl text-green-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Marketing</h3>
                    <p class="text-gray-600 text-sm mb-3">70 courses</p>
                    <span class="text-green-600 text-sm font-medium group-hover:underline">Explore →</span>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-primary">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-4xl font-bold text-white mb-6">Ready to Start Learning?</h2>
            <p class="text-xl text-white opacity-90 mb-8 max-w-2xl mx-auto">
                Join thousands of students already learning on LearnHub. Start your journey today!
            </p>
            <a href="{{ route('register') }}"
                class="inline-block px-8 py-4 bg-white text-purple-600 font-bold rounded-lg hover:bg-gray-100 transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-user-plus mr-2"></i>
                Create Free Account
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-graduation-cap text-2xl text-purple-400"></i>
                        <span class="text-xl font-bold">LearnHub</span>
                    </div>
                    <p class="text-gray-400">Empowering learners worldwide with quality education.</p>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Company</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-purple-400 transition">About Us</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Careers</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Press</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Resources</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-purple-400 transition">Blog</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Help Center</a></li>
                        <li><a href="#" class="hover:text-purple-400 transition">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#"
                            class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} LearnHub. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>
