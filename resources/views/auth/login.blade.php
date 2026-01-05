<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back!</h2>
        <p class="text-gray-600">Sign in to continue your learning journey</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-gray-700 font-semibold" />
            <div class="relative mt-2">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400"></i>
                </div>
                <x-text-input id="email"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    type="email" name="email" :value="old('email')" placeholder="Enter your email" required autofocus
                    autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-semibold" />
            <div class="relative mt-2">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400"></i>
                </div>
                <x-text-input id="password"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    type="password" name="password" placeholder="Enter your password" required
                    autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox"
                    class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500" name="remember">
                <span class="ml-2 text-sm text-gray-600 hover:text-gray-800">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-purple-600 hover:text-purple-800 font-medium"
                    href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="space-y-4">
            <button type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition transform hover:scale-[1.02] active:scale-[0.98]">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </button>

            <!-- Register Link -->
            <div class="text-center">
                <span class="text-gray-600">Don't have an account? </span>
                <a href="{{ route('register') }}" class="text-purple-600 hover:text-purple-800 font-semibold">
                    Sign up for free
                </a>
            </div>
        </div>
    </form>

    <!-- Social Login (Optional) -->
    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or continue with</span>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <button
                class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <i class="fab fa-google text-red-500 mr-2"></i>
                <span class="text-sm font-medium text-gray-700">Google</span>
            </button>
            <button
                class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <i class="fab fa-github text-gray-800 mr-2"></i>
                <span class="text-sm font-medium text-gray-700">GitHub</span>
            </button>
        </div>
    </div>
</x-guest-layout>
