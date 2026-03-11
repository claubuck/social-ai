<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    topics: { type: Array, required: true },
    hasCompany: { type: Boolean, default: false },
    companyId: { type: Number, default: null },
    flash: { type: Object, default: () => ({}) },
    apiTokenPlain: { type: String, default: null },
});

const editingId = ref(null);
const editTopic = ref('');

const form = useForm({ topic: '' });
const companyForm = useForm({ name: '', plan: 'free' });

function submitAdd() {
    form.post(route('content-topics.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('topic'),
    });
}

function startEdit(t) {
    editingId.value = t.id;
    editTopic.value = t.topic;
}

function cancelEdit() {
    editingId.value = null;
    editTopic.value = '';
}

function submitEdit(id) {
    router.put(route('content-topics.update', id), { topic: editTopic.value }, {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
            editTopic.value = '';
        },
    });
}

function remove(id) {
    if (confirm('¿Eliminar este tema?')) {
        router.delete(route('content-topics.destroy', id), { preserveScroll: true });
    }
}

function submitCompany() {
    companyForm.post(route('company.store'), { preserveScroll: true });
}

function generateToken() {
    router.post(route('api-token.store'), { name: 'n8n' }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Temas de contenido" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Temas de contenido
            </h2>
        </template>

        <div class="py-12">
            <div v-if="flash?.success" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-green-800">
                {{ flash.success }}
            </div>
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div v-if="!hasCompany" class="rounded-lg border border-amber-200 bg-amber-50 p-6">
                    <p class="mb-4 text-amber-800">
                        No tienes una empresa asignada. En esta app cada usuario pertenece a una empresa:
                        los temas, posts y cuentas sociales son por empresa. Crea tu empresa y se te asignará automáticamente.
                    </p>
                    <form @submit.prevent="submitCompany" class="flex flex-wrap items-end gap-3">
                        <div class="min-w-0 flex-1">
                            <InputLabel for="company_name" value="Nombre de la empresa" />
                            <TextInput
                                id="company_name"
                                v-model="companyForm.name"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="ej. Mi Negocio"
                                required
                            />
                            <InputError :message="companyForm.errors.name" class="mt-1" />
                        </div>
                        <PrimaryButton type="submit" :disabled="companyForm.processing">
                            Crear mi empresa
                        </PrimaryButton>
                    </form>
                </div>

                <template v-else>
                    <p class="mb-6 text-gray-600">
                        Estos temas se envían a n8n con <code class="rounded bg-gray-100 px-1">GET /api/content-topics</code>.
                        n8n puede usarlos para generar un post con IA por cada tema (ej. cada día a las 9:00).
                    </p>

                    <div class="mb-8 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h3 class="mb-2 font-medium text-slate-800">API para n8n (varias empresas)</h3>
                        <p class="mb-2 text-sm text-slate-600">
                            En .env: <code class="rounded bg-slate-200 px-1">N8N_API_KEY=tu-clave</code>. n8n llama <strong>GET /api/content-topics</strong> solo con <code>Authorization: Bearer tu-clave</code>; Laravel devuelve temas de todas las empresas, cada uno con <code>company_id</code>. Para crear el post, n8n envía <code>X-Company-Id: {{ companyId }}</code> (el del item actual).
                        </p>
                        <p v-if="companyId" class="mb-3 text-sm text-slate-600">
                            ID de esta empresa: <strong>{{ companyId }}</strong> (viene en cada tema en la respuesta de content-topics).
                        </p>
                        <p class="mb-3 text-sm text-slate-600">
                            Alternativa: genera un token por usuario (Sanctum) y úsalo como <code class="rounded bg-slate-200 px-1">Authorization: Bearer TOKEN</code>.
                        </p>
                        <button
                            type="button"
                            class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700"
                            @click="generateToken"
                        >
                            Generar token de usuario
                        </button>
                        <div v-if="apiTokenPlain" class="mt-3 rounded border border-amber-200 bg-amber-50 p-3">
                            <p class="mb-1 text-xs font-medium text-amber-800">Cópialo ahora (solo se muestra una vez):</p>
                            <code class="block break-all text-sm text-amber-900">{{ apiTokenPlain }}</code>
                        </div>
                    </div>

                    <form @submit.prevent="submitAdd" class="mb-8 flex flex-wrap items-end gap-3">
                        <div class="min-w-0 flex-1">
                            <InputLabel for="topic" value="Nuevo tema" />
                            <TextInput
                                id="topic"
                                v-model="form.topic"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="ej. tips de marketing, promociones restaurante"
                                required
                            />
                            <InputError :message="form.errors.topic" class="mt-1" />
                        </div>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            Agregar tema
                        </PrimaryButton>
                    </form>

                    <div v-if="topics.length === 0" class="rounded-lg border border-gray-200 bg-gray-50 p-6 text-center text-gray-500">
                        Aún no hay temas. Agrega uno arriba o ejecuta
                        <code class="rounded bg-gray-100 px-1">php artisan db:seed --class=ContentTopicSeeder</code>
                        para cargar ejemplos.
                    </div>

                    <ul v-else class="space-y-2">
                        <li
                            v-for="t in topics"
                            :key="t.id"
                            class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 bg-white px-4 py-3"
                        >
                            <template v-if="editingId === t.id">
                                <input
                                    v-model="editTopic"
                                    type="text"
                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    @keydown.enter="submitEdit(t.id)"
                                    @keydown.esc="cancelEdit"
                                />
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="text-sm text-indigo-600 hover:underline"
                                        @click="submitEdit(t.id)"
                                    >
                                        Guardar
                                    </button>
                                    <button
                                        type="button"
                                        class="text-sm text-gray-500 hover:underline"
                                        @click="cancelEdit"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </template>
                            <template v-else>
                                <span class="flex-1 text-gray-900">{{ t.topic }}</span>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="text-sm text-indigo-600 hover:underline"
                                        @click="startEdit(t)"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        type="button"
                                        class="text-sm text-red-600 hover:underline"
                                        @click="remove(t.id)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </template>
                        </li>
                    </ul>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
