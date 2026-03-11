<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    accounts: { type: Array, required: true },
    hasCompany: { type: Boolean, default: false },
    platforms: { type: Array, required: true },
    facebookConfigured: { type: Boolean, default: false },
    flash: { type: Object, default: () => ({}) },
});

function remove(id) {
    if (confirm('¿Eliminar esta cuenta social?')) {
        router.delete(route('social-accounts.destroy', id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Cuentas sociales" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Cuentas sociales
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div v-if="flash?.success" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-green-800">
                    {{ flash.success }}
                </div>
                <div v-if="flash?.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-red-800">
                    {{ flash.error }}
                </div>

                <div v-if="!hasCompany" class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-amber-800">
                    No tienes una empresa asignada. Crea una desde
                    <Link :href="route('content-topics.index')" class="font-medium underline">Temas de contenido</Link>
                    para poder gestionar cuentas de redes sociales (Facebook, Instagram, etc.).
                </div>

                <template v-else>
                    <p class="mb-6 text-gray-600">
                        Conecta Facebook (e Instagram vinculado) con un clic. Para LinkedIn o Twitter puedes agregar la cuenta con token manual.
                    </p>

                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <a
                            :href="route('facebook.connect')"
                            class="inline-flex items-center rounded-md border border-transparent bg-[#1877f2] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#166fe5]"
                        >
                            Conectar Facebook
                        </a>
                        <Link
                            :href="route('social-accounts.create')"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            O agregar con token manual
                        </Link>
                    </div>
                    <p v-if="!facebookConfigured" class="mb-4 text-sm text-amber-700">
                        Para que «Conectar Facebook» funcione, configura <code class="rounded bg-amber-100 px-1">FACEBOOK_APP_ID</code> y <code class="rounded bg-amber-100 px-1">FACEBOOK_APP_SECRET</code> en el .env del servidor.
                    </p>

                    <div v-if="accounts.length === 0" class="rounded-lg border border-gray-200 bg-white p-8 text-center text-gray-500">
                        No hay cuentas configuradas. Agrega una para publicar desde la plataforma.
                    </div>

                    <div v-else class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Plataforma</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Page ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Token</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="a in accounts" :key="a.id">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-800">{{ a.platform }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-800">{{ a.account_name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ a.page_id || '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <span v-if="a.has_token" class="text-green-600">Configurado</span>
                                        <span v-else class="text-amber-600">Sin token</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                        <Link :href="route('social-accounts.edit', a.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                        <span class="mx-2 text-gray-300">|</span>
                                        <button type="button" class="text-red-600 hover:text-red-900" @click="remove(a.id)">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
