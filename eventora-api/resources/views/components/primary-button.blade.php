<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-lg border border-transparent bg-[#8f4f36] px-5 py-3 text-sm font-black text-[#fffaf1] shadow-sm transition hover:bg-[#75402c] focus:outline-none focus:ring-2 focus:ring-[#6f8f72] focus:ring-offset-2 active:bg-[#5f3424]']) }}>
    {{ $slot }}
</button>
