@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-[#0f1115] border-gray-800 text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-xl shadow-sm transition duration-300 px-4 py-2.5']) }}>
