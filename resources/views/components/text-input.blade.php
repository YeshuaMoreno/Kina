@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white/70 dark:bg-dark/60 border-lavanda/40 dark:border-lavanda/30 text-malva dark:text-tiza placeholder-lavanda/70 focus:border-malva dark:focus:border-lavanda focus:ring-lavanda rounded-xl shadow-sm']) }}>
