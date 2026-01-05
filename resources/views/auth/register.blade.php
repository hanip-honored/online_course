<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h2>
        <p class="text-gray-600">Join thousands of learners today</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" class="text-gray-700 font-semibold" />
            <div class="relative mt-2">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-user text-gray-400"></i>
                </div>
                <x-text-input id="name"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    type="text" name="name" :value="old('name')" placeholder="Enter your full name" required
                    autofocus autocomplete="name" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-gray-700 font-semibold" />
            <div class="relative mt-2">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400"></i>
                </div>
                <x-text-input id="email"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    type="email" name="email" :value="old('email')" placeholder="Enter your email" required
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
                    type="password" name="password" placeholder="Create a strong password" required
                    autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700 font-semibold" />
            <div class="relative mt-2">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400"></i>
                </div>
                <x-text-input id="password_confirmation"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    type="password" name="password_confirmation" placeholder="Confirm your password" required
                    autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Terms & Conditions -->
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input id="terms" type="checkbox"
                    class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500" required>
            </div>
            <div class="ml-3 text-sm">
                <label for="terms" class="text-gray-600">
                    I agree to the
                    <a href="#" class="text-purple-600 hover:text-purple-800 font-medium">Terms and Conditions</a>
                    and
                    <a href="#" class="text-purple-600 hover:text-purple-800 font-medium">Privacy Policy</a>
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="space-y-4">
            <button type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition transform hover:scale-[1.02] active:scale-[0.98]">
                <i class="fas fa-user-plus mr-2"></i>
                Create Account
            </button>

            <!-- Login Link -->
            <div class="text-center">
                <span class="text-gray-600">Already have an account? </span>
                <a href="{{ route('login') }}" class="text-purple-600 hover:text-purple-800 font-semibold">
                    Sign in
                </a>
            </div>
        </div>
    </form>

    <!-- Social Registration (Optional) -->
    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or sign up with</span>
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
