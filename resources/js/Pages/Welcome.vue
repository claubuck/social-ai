<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});
</script>

<template>
    <Head title="Social AI — Automatiza tus redes con IA" />
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <!-- Navigation -->
        <header class="fixed left-0 right-0 top-0 z-50 border-b border-slate-800/50 bg-slate-950/80 backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <Link href="/" class="flex items-center gap-2 font-semibold text-white">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                    </span>
                    Social AI
                </Link>
                <nav class="flex items-center gap-2">
                    <template v-if="$page.props.auth?.user">
                        <Link
                            :href="route('dashboard')"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
                        >
                            Iniciar sesión
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-slate-950"
                        >
                            Crear cuenta
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main>
            <!-- Hero -->
            <section class="relative overflow-hidden pt-28 pb-20 sm:pt-36 sm:pb-28">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-20%,rgba(16,185,129,0.15),transparent)]" />
                <div class="relative mx-auto max-w-6xl px-4 sm:px-6">
                    <div class="mx-auto max-w-3xl text-center">
                        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                            Publica en redes sociales
                            <span class="text-emerald-400">con inteligencia artificial</span>
                        </h1>
                        <p class="mt-6 text-lg leading-relaxed text-slate-400 sm:text-xl">
                            Genera contenido viral, programa publicaciones y publica en Instagram, Facebook, LinkedIn y Twitter desde una sola plataforma. Diseñado para equipos y empresas.
                        </p>
                        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-500 px-6 py-3.5 text-base font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-slate-950 sm:w-auto"
                            >
                                Empezar gratis
                            </Link>
                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-700 bg-slate-800/50 px-6 py-3.5 text-base font-medium text-slate-200 transition hover:border-slate-600 hover:bg-slate-800 sm:w-auto"
                            >
                                Iniciar sesión
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section class="border-t border-slate-800/50 py-20 sm:py-28">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <h2 class="text-center text-3xl font-bold text-white sm:text-4xl">
                        Todo lo que necesitas para tu presencia en redes
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-center text-slate-400">
                        Multi-tenant, multi-plataforma y listo para escalar con tu empresa.
                    </p>
                    <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 transition hover:border-slate-700">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-white">Contenido con IA</h3>
                            <p class="mt-2 text-sm text-slate-400">
                                Genera posts optimizados por plataforma con un tema. Emojis, hashtags y tono adaptados a Instagram, Facebook, LinkedIn o Twitter.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 transition hover:border-slate-700">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-white">Programación</h3>
                            <p class="mt-2 text-sm text-slate-400">
                                Programa publicaciones con fecha y hora. El sistema publica automáticamente cuando llega el momento.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 transition hover:border-slate-700">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-white">Multi-plataforma</h3>
                            <p class="mt-2 text-sm text-slate-400">
                                Conecta cuentas de Instagram, Facebook, LinkedIn y Twitter. Publica en todas desde un solo lugar.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 transition hover:border-slate-700">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-white">Por empresa</h3>
                            <p class="mt-2 text-sm text-slate-400">
                                Estructura multi-tenant: cada empresa tiene sus usuarios, cuentas sociales y publicaciones.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="border-t border-slate-800/50 py-20 sm:py-28">
                <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">
                        ¿Listo para automatizar tu presencia en redes?
                    </h2>
                    <p class="mt-4 text-lg text-slate-400">
                        Crea tu cuenta y empieza a generar y programar contenido en minutos.
                    </p>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="mt-8 inline-flex items-center justify-center rounded-xl bg-emerald-500 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-slate-950"
                    >
                        Crear cuenta gratis
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-800/50 py-8">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <p class="text-sm text-slate-500">
                        © {{ new Date().getFullYear() }} Social AI. Automatización de redes sociales con IA.
                    </p>
                    <div class="flex gap-6 text-sm text-slate-500">
                        <Link :href="route('login')" class="transition hover:text-slate-400">Iniciar sesión</Link>
                        <Link :href="route('register')" class="transition hover:text-slate-400">Registro</Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
