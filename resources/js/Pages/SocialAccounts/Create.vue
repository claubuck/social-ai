<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    platforms: { type: Array, required: true },
});

const form = useForm({
    platform: props.platforms[0] || 'instagram',
    account_name: '',
    access_token: '',
    page_id: '',
});

function submit() {
    form.post(route('social-accounts.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Agregar cuenta social" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Agregar cuenta social
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('social-accounts.index')" class="text-indigo-600 hover:text-indigo-900">← Volver a cuentas</Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <div>
                        <InputLabel for="platform" value="Plataforma" />
                        <select
                            id="platform"
                            v-model="form.platform"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option v-for="p in platforms" :key="p" :value="p">{{ p }}</option>
                        </select>
                        <InputError :message="form.errors.platform" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="account_name" value="Nombre de la cuenta" />
                        <TextInput
                            id="account_name"
                            v-model="form.account_name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="ej. Mi página de Facebook"
                            required
                        />
                        <InputError :message="form.errors.account_name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="access_token" value="Token de acceso (access token)" />
                        <textarea
                            id="access_token"
                            v-model="form.access_token"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Pega el token de la API (Facebook, Meta, etc.)"
                            required
                        />
                        <InputError :message="form.errors.access_token" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="page_id" value="Page ID (opcional)" />
                        <TextInput
                            id="page_id"
                            v-model="form.page_id"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Para Facebook/Instagram: ID de la página"
                        />
                        <InputError :message="form.errors.page_id" class="mt-1" />
                    </div>

                    <div class="flex gap-3">
                        <PrimaryButton type="submit" :disabled="form.processing">
                            Guardar cuenta
                        </PrimaryButton>
                        <Link :href="route('social-accounts.index')" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
