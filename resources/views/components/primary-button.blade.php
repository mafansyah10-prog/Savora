<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-2.5 bg-orange-400 hover:bg-orange-500 text-black text-xs font-bold rounded-full transition duration-300 shadow-md uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 active:bg-orange-600']) }}>
    {{ $slot }}
</button>
