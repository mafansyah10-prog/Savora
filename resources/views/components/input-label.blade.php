@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-xs uppercase tracking-widest text-gray-400']) }}>
    {{ $value ?? $slot }}
</label>
