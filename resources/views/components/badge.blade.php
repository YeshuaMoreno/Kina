@props(['variant' => 'neutral'])

@php
    $variants = [
        'neutral' => 'bg-lavanda/15 text-malva border-lavanda/30 dark:text-tiza',
        'rosa' => 'bg-rosa/25 text-dark border-rosa/40',
        'malva' => 'bg-malva/10 text-malva border-malva/25 dark:text-tiza',
        'success' => 'bg-success/15 text-success border-success/30',
    ];
    $classes = $variants[$variant] ?? $variants['neutral'];
@endphp

{{-- Chip / badge Kina --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-sans font-semibold border ' . $classes]) }}>
    {{ $slot }}
</span>
