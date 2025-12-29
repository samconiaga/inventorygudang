<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-10 bg-gray-100">
        <div class="w-full max-w-md">
            {{-- BRAND --}}
            <div class="text-center mb-6">
                <div class="mx-auto h-12 w-12 rounded-xl bg-red-600 flex items-center justify-center shadow">
                    <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none">
                        <path d="M3 7l9-4 9 4-9 4-9-4Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M3 7v10l9 4 9-4V7" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 11v10" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <h1 class="mt-3 text-2xl font-bold text-gray-900">Inventory Gudang</h1>
                <p class="mt-1 text-sm text-gray-500">Silakan login untuk melanjutkan.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input
                            id="email"
                            class="block mt-1 w-full"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="nama@perusahaan.com"
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Password (show/hide) --}}
                    <div>
                        <x-input-label for="password" :value="__('Password')" />

                        <div class="relative mt-1">
                            <x-text-input
                                id="password"
                                class="block w-full pr-12"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan password"
                            />

                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
                                aria-label="Toggle password visibility"
                            >
                                <svg id="iconEye" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="2"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <svg id="iconEyeOff" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18" stroke="currentColor" stroke-width="2"/>
                                    <path d="M10.58 10.58A3 3 0 0 0 12 15a3 3 0 0 0 2.42-4.42" stroke="currentColor" stroke-width="2"/>
                                    <path d="M9.88 5.09A10.45 10.45 0 0 1 12 5c6.5 0 10 7 10 7a18.73 18.73 0 0 1-4.21 5.19" stroke="currentColor" stroke-width="2"/>
                                    <path d="M6.11 6.11C3.76 8.05 2 12 2 12s3.5 7 10 7c1.01 0 1.96-.17 2.83-.47" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Remember + Forgot --}}
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox"
                                class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500"
                                name="remember">
                            <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-red-600 hover:text-red-700 font-medium"
                               href="{{ route('password.request') }}">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                        <span>Log In</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-width="2"/>
                            <path d="M4 12h11" stroke="currentColor" stroke-width="2"/>
                            <path d="M20 4v16" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                </form>
            </div>

            <p class="text-center text-xs text-gray-500 mt-5">
                © {{ date('Y') }} Inventory Gudang — Internal System
            </p>
        </div>
    </div>

    <script>
        (function () {
            const btn = document.getElementById('togglePassword');
            const input = document.getElementById('password');
            const eye = document.getElementById('iconEye');
            const eyeOff = document.getElementById('iconEyeOff');

            btn.addEventListener('click', function () {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                eye.classList.toggle('hidden', isHidden);
                eyeOff.classList.toggle('hidden', !isHidden);
            });
        })();
    </script>
</x-guest-layout>
