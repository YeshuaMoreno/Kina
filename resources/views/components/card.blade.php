@props(['padding' => 'p-6'])

{{-- Card Kina: fondo crema translúcido, borde sutil, esquinas suaves, sin sombra pesada. --}}
<div {{ $attributes->merge(['class' => 'bg-white/70 dark:bg-dark/50 border border-lavanda/20 rounded-2xl shadow-sm ' . $padding]) }}>
    {{ $slot }}
</div>
