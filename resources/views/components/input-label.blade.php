@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-sans font-semibold text-sm text-malva dark:text-tiza']) }}>
    {{ $value ?? $slot }}
</label>
