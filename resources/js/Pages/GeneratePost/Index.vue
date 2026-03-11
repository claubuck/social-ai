<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    hasCompany: { type: Boolean, default: false },
    companyId: { type: Number, default: null },
    platforms: { type: Array, required: true },
    webhookConfigured: { type: Boolean, default: false },
    flash: { type: Object, default: () => ({}) },
});

const form = useForm({
    topic: '',
    platform: props.platforms[0] || 'instagram',
    publish_immediately: true,
    publish_at: '',
});

function submit() {
    form.post(route('generate-post.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('topic', 'publish_at'),
    });
}
</script>

<template>
    <Head title="Generar publicación" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Generar publicación
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div v-if="flash?.success" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-green-800">
                    {{ flash.success }}
                </div>
                <div v-if="flash?.errors && Object.keys(flash.errors).length" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-red-800">
                    <p v-for="(msgs, key) in flash.errors" :key="key">{{ Array.isArray(msgs) ? msgs[0] : msgs }}</p>
                </div>

                <div v-if="!hasCompany" class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-amber-800">
                    Necesitas pertenecer a una empresa. Crea una desde <a :href="route('content-topics.index')" class="underline">Temas de contenido</a>.
                </div>

                <template v-else>
                    <div v-if="!webhookConfigured" class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800">
                        Configura <code class="rounded bg-amber-100 px-1">N8N_WEBHOOK_GENERATE_URL</code> en el .env del servidor (URL del webhook en n8n que recibe el tema y devuelve a Laravel el contenido generado).
                    </div>

                    <p class="mb-6 text-gray-600">
                        Escribe un tema o texto. Laravel lo envía a n8n; n8n genera el contenido con IA y vuelve a Laravel para crear y publicar el post.
                    </p>

                    <form @submit.prevent="submit" class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <div>
                            <InputLabel for="topic" value="Tema o texto para la publicación" />
                            <textarea
                                id="topic"
                                v-model="form.topic"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="ej. Promoción de verano, tips de marketing digital..."
                                required
                            />
                            <InputError :message="form.errors.topic" class="mt-1" />
                        </div>

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

                        <div class="flex items-center gap-4">
                            <Checkbox v-model:checked="form.publish_immediately" :value="true" />
                            <InputLabel for="publish_immediately" value="Publicar en cuanto n8n genere el contenido (Laravel publica en la red)" class="font-normal" />
                        </div>

                        <div v-if="!form.publish_immediately">
                            <InputLabel for="publish_at" value="Programar para (opcional)" />
                            <input
                                id="publish_at"
                                v-model="form.publish_at"
                                type="datetime-local"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <PrimaryButton type="submit" :disabled="form.processing || !webhookConfigured">
                            Generar publicación
                        </PrimaryButton>
                    </form>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
