<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kina — Conecta a tu propio ritmo</title>
    <meta name="description" content="Kina es un espacio para personas neurodivergentes e introvertidas donde conectar amistad, pareja o comunidad a tu propio ritmo.">

    <link rel="icon" type="image/svg+xml" href="{{ asset('brand/favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|nunito:400,500,600,700|inter:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-tiza text-malva antialiased">

    {{-- ===================== NAVBAR ===================== --}}
    <header
        x-data="{ open: false, scrolled: false }"
        x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 8)"
        class="fixed inset-x-0 top-0 z-40 transition-colors duration-300"
        :class="scrolled ? 'bg-tiza/85 backdrop-blur border-b border-lavanda/20' : 'bg-transparent'"
    >
        <nav class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="#inicio" class="flex items-center gap-2.5" aria-label="Kina — inicio">
                    <x-application-logo class="w-8 h-8 text-malva" />
                    <span class="font-serif text-2xl font-semibold text-malva">Kina</span>
                </a>

                {{-- Links desktop --}}
                <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-malva/80">
                    <a href="#como-funciona" class="hover:text-malva transition">Cómo funciona</a>
                    <a href="#sintonia" class="hover:text-malva transition">Áreas de Sintonía</a>
                    <a href="#privacidad" class="hover:text-malva transition">Privacidad</a>
                </div>

                <div class="hidden md:flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-malva hover:opacity-80 transition">Ir a mi espacio →</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-malva/80 hover:text-malva transition">Entrar</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-malva text-tiza text-sm font-semibold hover:bg-dark transition">Crear mi espacio</a>
                    @endauth
                </div>

                {{-- Botón menú móvil --}}
                <button @click="open = !open" class="md:hidden p-2 -mr-2 text-malva" aria-label="Abrir menú">
                    <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6"/></svg>
                </button>
            </div>
        </nav>

        {{-- Menú móvil --}}
        <div x-show="open" x-cloak x-transition class="md:hidden bg-tiza/95 backdrop-blur border-b border-lavanda/20">
            <div class="px-5 py-4 space-y-3 text-sm font-semibold text-malva">
                <a href="#como-funciona" @click="open=false" class="block py-1">Cómo funciona</a>
                <a href="#sintonia" @click="open=false" class="block py-1">Áreas de Sintonía</a>
                <a href="#privacidad" @click="open=false" class="block py-1">Privacidad</a>
                <div class="pt-3 flex flex-col gap-2 border-t border-lavanda/20">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex justify-center px-4 py-2 rounded-xl bg-malva text-tiza">Ir a mi espacio</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex justify-center px-4 py-2 rounded-xl border border-lavanda/40">Entrar</a>
                        <a href="{{ route('register') }}" class="inline-flex justify-center px-4 py-2 rounded-xl bg-malva text-tiza">Crear mi espacio</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- ===================== HERO ===================== --}}
    <section id="inicio" class="relative overflow-hidden pt-32 pb-20 sm:pt-40 sm:pb-28">
        {{-- Órbita decorativa --}}
        <div class="pointer-events-none absolute -top-24 -right-24 w-[28rem] h-[28rem] opacity-40 hidden sm:block" aria-hidden="true">
            <svg viewBox="0 0 400 400" fill="none" class="w-full h-full">
                <circle cx="200" cy="200" r="150" stroke="#9A8C98" stroke-width="2" fill="none"/>
                <circle cx="200" cy="200" r="100" stroke="#C9ADA7" stroke-width="2" fill="none"/>
                <circle cx="200" cy="50" r="14" fill="#C9ADA7"/>
                <circle cx="300" cy="200" r="9" fill="#9A8C98"/>
            </svg>
        </div>

        <div class="relative max-w-6xl mx-auto px-5 sm:px-8">
            <div class="max-w-2xl">
                <x-badge variant="rosa" class="mb-6">Un espacio distinto para conectar</x-badge>
                <h1 class="font-serif text-4xl sm:text-6xl font-semibold leading-[1.1] text-dark">
                    Conecta a tu<br>propio ritmo.
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-malva/80 leading-relaxed max-w-xl">
                    Kina es un espacio calmado para personas neurodivergentes, introvertidas o con
                    estilos de comunicación distintos. Encuentra amistad, pareja o comunidad
                    según tus intereses, tu intención y cómo te gusta comunicarte.
                </p>
                <div class="mt-9 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-malva text-tiza font-semibold hover:bg-dark transition">
                        Crear mi espacio
                    </a>
                    <a href="#manifiesto" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white/60 border border-lavanda/40 text-malva font-semibold hover:bg-lavanda/10 transition">
                        Leer el manifiesto
                    </a>
                </div>
                <p class="mt-5 text-sm text-lavanda">Solo para mayores de 18 años · Sin swipes · Sin prisas.</p>
            </div>
        </div>
    </section>

    {{-- ===================== MANIFIESTO ===================== --}}
    <section id="manifiesto" class="scroll-mt-20 py-16 sm:py-24 bg-white/40 border-y border-lavanda/20">
        <div class="max-w-3xl mx-auto px-5 sm:px-8 text-center">
            <p class="font-serif text-2xl sm:text-3xl leading-relaxed text-dark">
                Creemos que conectar no debería sentirse como una entrevista ni como una carrera.
                Aquí no hay swipes ni relojes. Hay personas que se toman su tiempo,
                que dicen las cosas a su manera y que merecen encontrarse
                <span class="text-malva">sin tener que fingir otra versión de sí mismas.</span>
            </p>
        </div>
    </section>

    {{-- ===================== CÓMO FUNCIONA ===================== --}}
    <section id="como-funciona" class="scroll-mt-20 py-20 sm:py-28">
        <div class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="max-w-2xl mb-14">
                <h2 class="font-serif text-3xl sm:text-4xl font-semibold text-dark">Cómo funciona</h2>
                <p class="mt-3 text-malva/75 text-lg">Cuatro pasos tranquilos. Tú marcas el ritmo.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['01', 'Crea tu espacio', 'Cuentas lo que te interesa, tu intención y cómo prefieres comunicarte. Sin diagnósticos obligatorios.'],
                    ['02', 'Descubre con calma', 'Ves personas afines en scroll vertical, con tarjetas cuidadas. Nada de decidir en un segundo.'],
                    ['03', 'Conecta cuando quieras', 'Envías una solicitud. Si ambos aceptan, se abre un chat. Antes no, para que nadie te escriba sin permiso.'],
                    ['04', 'A tu ritmo', 'Puedes tardar en responder, pausar tu perfil o mostrar solo lo que decidas. Aquí está bien ir despacio.'],
                ] as [$num, $titulo, $texto])
                    <x-card class="h-full">
                        <div class="font-serif text-2xl text-rosa mb-3">{{ $num }}</div>
                        <h3 class="font-serif text-xl font-semibold text-dark mb-2">{{ $titulo }}</h3>
                        <p class="text-sm text-malva/75 leading-relaxed">{{ $texto }}</p>
                    </x-card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== ÁREAS DE SINTONÍA ===================== --}}
    <section id="sintonia" class="scroll-mt-20 py-20 sm:py-28 bg-white/40 border-y border-lavanda/20">
        <div class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="max-w-2xl mb-14">
                <h2 class="font-serif text-3xl sm:text-4xl font-semibold text-dark">Áreas de Sintonía</h2>
                <p class="mt-3 text-malva/75 text-lg">
                    No te damos un porcentaje frío. Te mostramos en qué coinciden, con palabras
                    que sí dicen algo.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'Coinciden en varios intereses',
                    'Buena compatibilidad de comunicación',
                    'Buscan algo similar',
                    'Ambos prefieren hablar por chat',
                    'Ambos prefieren planes tranquilos',
                    'Podría ser buena conexión',
                ] as $sintonia)
                    <div class="flex items-center gap-3 rounded-2xl border border-lavanda/20 bg-tiza/60 px-5 py-4">
                        <span class="shrink-0 w-2.5 h-2.5 rounded-full bg-rosa"></span>
                        <span class="font-semibold text-malva">{{ $sintonia }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== PRIVACIDAD Y SEGURIDAD ===================== --}}
    <section id="privacidad" class="scroll-mt-20 py-20 sm:py-28">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="font-serif text-3xl sm:text-4xl font-semibold text-dark">Privacidad y seguridad</h2>
                <p class="mt-5 text-lg text-malva/80 leading-relaxed">
                    Kina protege tu información con controles de privacidad, acceso restringido y
                    buenas prácticas de seguridad. Tus etiquetas sensibles solo se muestran si tú lo decides.
                </p>
                <div class="mt-8 flex flex-wrap gap-2">
                    <x-badge variant="malva">Etiquetas ocultas por defecto</x-badge>
                    <x-badge variant="malva">Tú eliges quién te ve</x-badge>
                    <x-badge variant="malva">Bloquear y reportar</x-badge>
                    <x-badge variant="malva">Elimina tu cuenta cuando quieras</x-badge>
                </div>
            </div>

            <div class="grid gap-4">
                @foreach ([
                    ['Solo mayores de 18', 'Confirmación de edad obligatoria al registrarte.'],
                    ['Tú controlas tu visibilidad', 'Muestra tu perfil y tus etiquetas a nadie, solo a tus conexiones o al público.'],
                    ['Sin mensajes sin permiso', 'Nadie puede escribirte si no hay una conexión aceptada.'],
                    ['Consentimiento claro', 'Pedimos tu consentimiento explícito antes de tratar datos sensibles.'],
                ] as [$titulo, $texto])
                    <x-card padding="p-5">
                        <h3 class="font-semibold text-dark">{{ $titulo }}</h3>
                        <p class="mt-1 text-sm text-malva/75">{{ $texto }}</p>
                    </x-card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== BETA / WAITLIST (visual) ===================== --}}
    <section id="beta" class="scroll-mt-20 pb-24">
        <div class="max-w-4xl mx-auto px-5 sm:px-8">
            <div class="rounded-3xl bg-malva text-tiza px-6 py-12 sm:px-14 sm:py-16 text-center relative overflow-hidden">
                <div class="pointer-events-none absolute -bottom-16 -left-10 w-64 h-64 opacity-20" aria-hidden="true">
                    <svg viewBox="0 0 200 200" fill="none" class="w-full h-full">
                        <circle cx="100" cy="100" r="80" stroke="#F2E9E4" stroke-width="2"/>
                        <circle cx="100" cy="20" r="8" fill="#C9ADA7"/>
                    </svg>
                </div>
                <div class="relative">
                    <x-badge variant="rosa" class="mb-5">Beta privada</x-badge>
                    <h2 class="font-serif text-3xl sm:text-4xl font-semibold">Kina está abriendo poco a poco</h2>
                    <p class="mt-4 text-tiza/80 max-w-xl mx-auto">
                        Estamos construyendo este espacio con cuidado. Crea tu cuenta y sé de las
                        primeras personas en habitarlo.
                    </p>

                    {{-- Waitlist solo visual (aún sin backend/tabla) --}}
                    <form class="mt-8 flex flex-col sm:flex-row gap-3 max-w-md mx-auto" onsubmit="return false">
                        <input type="email" disabled placeholder="tu@correo.com"
                               class="flex-1 rounded-xl border border-tiza/30 bg-tiza/10 px-4 py-3 text-tiza placeholder-tiza/50 focus:outline-none">
                        <button type="button" disabled
                                class="rounded-xl bg-tiza/25 px-5 py-3 font-semibold text-tiza/70 cursor-not-allowed">
                            Muy pronto
                        </button>
                    </form>
                    <p class="mt-4 text-sm text-tiza/70">
                        Mientras tanto, ya puedes
                        <a href="{{ route('register') }}" class="underline decoration-rosa underline-offset-4 hover:text-white">crear tu espacio</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="border-t border-lavanda/20 py-12">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="w-7 h-7 text-malva" />
                <div>
                    <p class="font-serif text-lg font-semibold text-malva leading-none">Kina</p>
                    <p class="text-xs text-lavanda mt-1">Conecta a tu propio ritmo</p>
                </div>
            </div>
            <div class="flex items-center gap-6 text-sm text-malva/70">
                <a href="#privacidad" class="hover:text-malva transition">Privacidad</a>
                <a href="#como-funciona" class="hover:text-malva transition">Cómo funciona</a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="hover:text-malva transition">Mi espacio</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-malva transition">Entrar</a>
                @endauth
            </div>
        </div>
        <p class="mt-8 text-center text-xs text-lavanda">© {{ date('Y') }} Kina · Hecho con calma.</p>
    </footer>

</body>
</html>
