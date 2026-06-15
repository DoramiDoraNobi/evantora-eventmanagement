@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-bold text-[#2b2118]']) }}>
    {{ $value ?? $slot }}
</label>
