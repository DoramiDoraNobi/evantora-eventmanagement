@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-[#ddcfb8] bg-[#fffaf1] px-4 py-3 text-[#2b2118] shadow-sm focus:border-[#6f8f72] focus:ring-[#6f8f72]']) }}>
