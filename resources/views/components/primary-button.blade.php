<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-malva border border-transparent rounded-xl font-sans font-semibold text-sm text-tiza tracking-normal hover:bg-dark focus:bg-dark active:bg-dark focus:outline-none focus:ring-2 focus:ring-lavanda focus:ring-offset-2 focus:ring-offset-tiza dark:focus:ring-offset-dark disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
