<x-guest-layout>
    <div class="w-full max-w-md mx-auto bg-white rounded-lg shadow-md p-6">

        <h2 class="text-2xl font-bold text-center text-red-600 mb-2">
            Inventory Gudang
        </h2>

        <p class="text-center text-gray-500 mb-6">
            Lupa password? Masukkan email untuk reset password.
        </p>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <x-input-label for="email" value="Email" />
                <x-text-input
                    id="email"
                    class="block mt-1 w-full"
                    type="email"
                    name="email"
                    required
                    autofocus
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Submit -->
            <button
                type="submit"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded"
            >
                Kirim Link Reset Password
            </button>

            <!-- Back to login -->
            <div class="text-center mt-4">
                <a href="{{ route('login') }}"
                   class="text-sm text-gray-500 hover:text-red-600">
                    ← Kembali ke Login
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
