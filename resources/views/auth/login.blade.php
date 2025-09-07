{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
    @push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Raleway', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }
        [x-cloak]{ display:none !important; }
    </style>
    @endpush

    @php
        $KK_NAVY     = '#03314C';
        $KK_BLUE     = '#076BA8';
        $KK_BLUE_ALT = '#DAEEFF';
    @endphp

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
         style="color: {{ $KK_NAVY }}; background-image: linear-gradient(to bottom right, #eaeaea, #ffffff 35%, {{ $KK_BLUE_ALT }} 100%);">

        <div class="w-full max-w-md mx-auto">
            <div class="rounded-3xl ring-1 ring-black/5 shadow-lg p-8 md:p-10"
                 style="background: rgba(255,255,255,0.95);">
                
                {{-- Logo --}}
                <div class="mb-6 flex justify-center">
                    <x-authentication-card-logo class="w-50 h-50" />
                </div>

                {{-- Title --}}
                <h1 class="text-3xl font-extrabold text-center mb-6">Welcome back</h1>

                {{-- Validation errors --}}
                <x-validation-errors class="mb-4" />

                @session('status')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ $value }}
                    </div>
                @endsession

                {{-- Login form --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-label for="email" value="Email" class="font-semibold" />
                        <x-input id="email"
                                 class="mt-1 block w-full rounded-xl"
                                 type="email"
                                 name="email"
                                 :value="old('email')"
                                 required
                                 autofocus
                                 autocomplete="username" />
                    </div>

                    <div>
                        <x-label for="password" value="Password" class="font-semibold" />
                        <x-input id="password"
                                 class="mt-1 block w-full rounded-xl"
                                 type="password"
                                 name="password"
                                 required
                                 autocomplete="current-password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="flex items-center">
                            <x-checkbox id="remember_me" name="remember" />
                            <span class="ms-2 text-sm text-gray-600">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-sm font-medium"
                               style="color: {{ $KK_BLUE }};">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <div class="pt-4 flex justify-end">
                        <x-button class="px-6 py-2 rounded-xl shadow font-semibold"
                                  style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                            Log in
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
