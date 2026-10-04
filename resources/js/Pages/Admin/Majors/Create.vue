<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    code: '',
    name: '',
    description: '',
    is_active: true,
})

function submit() {
    form.post('/admin/majors')
}
</script>

<template>
    <AuthenticatedLayout>
        <h1 class="mb-6 text-xl font-semibold text-gray-800">New Major</h1>

        <form @submit.prevent="submit" class="max-w-md space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Code</label>
                <input v-model="form.code" type="text" placeholder="RPL" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" />
                <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                <input v-model="form.name" type="text" placeholder="Rekayasa Perangkat Lunak" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm"></textarea>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300" />
                Active
            </label>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded bg-gray-800 px-4 py-2 text-sm text-white hover:bg-gray-900 disabled:opacity-50"
            >
                Create
            </button>
        </form>
    </AuthenticatedLayout>
</template>