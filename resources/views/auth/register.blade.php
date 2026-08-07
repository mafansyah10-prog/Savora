<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1">
                <input
                    id="password"
                    :type="show ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="bg-[#0f1115] border-gray-800 text-gray-300 focus:border-[#e2c86e] focus:ring-[#e2c86e] rounded-xl shadow-sm transition duration-300 block w-full pl-4 pr-11 py-2.5"
                />
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-600 hover:text-[#e2c86e] transition-colors duration-200 focus:outline-none"
                    :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                    <span x-show="!show"><i data-lucide="eye" class="w-4 h-4"></i></span>
                    <span x-show="show" x-cloak><i data-lucide="eye-off" class="w-4 h-4"></i></span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div class="relative mt-1">
                <input
                    id="password_confirmation"
                    :type="show ? 'text' : 'password'"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="bg-[#0f1115] border-gray-800 text-gray-300 focus:border-[#e2c86e] focus:ring-[#e2c86e] rounded-xl shadow-sm transition duration-300 block w-full pl-4 pr-11 py-2.5"
                />
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-600 hover:text-[#e2c86e] transition-colors duration-200 focus:outline-none"
                    :aria-label="show ? 'Sembunyikan konfirmasi' : 'Tampilkan konfirmasi'">
                    <span x-show="!show"><i data-lucide="eye" class="w-4 h-4"></i></span>
                    <span x-show="show" x-cloak><i data-lucide="eye-off" class="w-4 h-4"></i></span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="text-xs text-gray-500 uppercase tracking-widest font-semibold hover:text-[#e2c86e] transition-colors duration-200 focus:outline-none" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
