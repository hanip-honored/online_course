<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .auth-card {
            backdrop-filter: blur(10px);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

<body class="font-sans antialiased">
    <div class="min-h-screen flex gradient-bg">
        <!-- Left Side - Illustration -->
        <div class="hidden lg:flex lg:w-1/2 items-center justify-center p-12 relative overflow-hidden">
            <div class="absolute inset-0 bg-white opacity-10"></div>
            <div class="relative z-10 text-white text-center">
                <div class="floating mb-8">
                    <i class="fas fa-graduation-cap text-9xl opacity-90"></i>
                </div>
                <h1 class="text-4xl font-bold mb-4">Welcome to LearnHub</h1>
                <p class="text-xl opacity-90">Discover thousands of courses and boost your skills</p>
                <div class="mt-8 flex justify-center space-x-8">
                    <div class="text-center">
                        <div class="text-3xl font-bold">10K+</div>
                        <div class="text-sm opacity-80">Courses</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold">50K+</div>
                        <div class="text-sm opacity-80">Students</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold">500+</div>
                        <div class="text-sm opacity-80">Instructors</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Auth Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Logo for mobile -->
                <div class="lg:hidden text-center mb-8">
                    <i class="fas fa-graduation-cap text-6xl text-white mb-4"></i>
                    <h2 class="text-2xl font-bold text-white">LearnHub</h2>
                </div>

                <div class="auth-card bg-white rounded-2xl shadow-2xl p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>
