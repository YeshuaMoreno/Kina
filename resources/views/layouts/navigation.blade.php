@php
    $navLink = fn ($active) => $active
        ? 'text-malva border-b-2 border-rosa'
        : 'text-malva/70 hover:text-malva border-b-2 border-transparent';
@endphp

<nav x-data="{ open: false }" class="bg-tiza/90 backdrop-blur border-b border-lavanda/20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <a href="{{ route('dashboard') }}" class="shrink-0 flex items-center gap-2">
                    <x-application-logo class="h-8 w-8 text-malva" />
                    <span class="font-serif text-xl font-semibold text-malva hidden sm:block">Kina</span>
                </a>

                <div class="hidden sm:flex sm:ms-10 sm:space-x-6 items-center text-sm font-semibold">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center h-16 px-1 {{ $navLink(request()->routeIs('dashboard')) }}">Inicio</a>
                    <a href="{{ route('descubrir.index') }}" class="inline-flex items-center h-16 px-1 {{ $navLink(request()->routeIs('descubrir.*')) }}">Descubrir</a>
                    <a href="{{ route('solicitudes.index') }}" class="inline-flex items-center h-16 px-1 {{ $navLink(request()->routeIs('solicitudes.*')) }}">Solicitudes</a>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-xl text-malva/80 hover:text-malva hover:bg-lavanda/10 transition">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="ms-1 fill-current h-4 w-4" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Mi perfil y configuración</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Cerrar sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-malva/70 hover:text-malva hover:bg-lavanda/10 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-lavanda/20">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Inicio</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('descubrir.index')" :active="request()->routeIs('descubrir.*')">Descubrir</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('solicitudes.index')" :active="request()->routeIs('solicitudes.*')">Solicitudes</x-responsive-nav-link>
        </div>
        <div class="pt-4 pb-1 border-t border-lavanda/20">
            <div class="px-4">
                <div class="font-semibold text-base text-malva">{{ Auth::user()->name }}</div>
                <div class="text-sm text-lavanda">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Mi perfil y configuración</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        Cerrar sesión
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
