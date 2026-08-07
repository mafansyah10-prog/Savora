<x-guest-layout>
    <div class="mb-6 text-xs text-gray-400 leading-relaxed uppercase tracking-wider font-semibold">
        Lupa kata sandi Anda? Tidak masalah. Cukup masukkan alamat email Anda dan kami akan mengirimkan link reset kata sandi agar Anda dapat mengatur ulang kata sandi Anda.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address — floating label -->
        <div class="relative mt-2">
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                placeholder=" "
                class="peer block w-full rounded-xl border border-gray-800 bg-[#0f1115] px-4 pt-5 pb-2 text-sm text-gray-200 focus:border-[#e2c86e] focus:ring-1 focus:ring-[#e2c86e] focus:outline-none transition duration-300"
            />
            <label
                for="email"
                class="absolute left-4 top-3.5 text-xs font-semibold uppercase tracking-widest text-gray-500 transition-all duration-200
                       peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-xs peer-placeholder-shown:text-gray-500
                       peer-focus:top-1.5 peer-focus:text-[10px] peer-focus:text-[#e2c86e]
                       peer-[:not(:placeholder-shown)]:top-1.5 peer-[:not(:placeholder-shown)]:text-[10px] peer-[:not(:placeholder-shown)]:text-[#e2c86e]"
            >
                Alamat Email
            </label>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="w-full justify-center">
                Kirim Link Reset Kata Sandi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
