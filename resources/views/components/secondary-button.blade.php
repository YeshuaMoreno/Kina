<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-white/60 dark:bg-transparent border border-lavanda/40 rounded-xl font-sans font-semibold text-sm text-malva dark:text-tiza tracking-normal hover:bg-lavanda/10 focus:outline-none focus:ring-2 focus:ring-lavanda focus:ring-offset-2 focus:ring-offset-tiza dark:focus:ring-offset-dark disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
