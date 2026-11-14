@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'h-12 border-[#A9B5B2] bg-white text-tinta placeholder:text-gray-500 focus:border-hijau focus:ring-hijau rounded-lg shadow-sm']) }}>
