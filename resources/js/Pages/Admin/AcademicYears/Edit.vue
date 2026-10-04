<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    academicYear: Object,
})

const form = useForm({
    name: props.academicYear.name,
    start_date: props.academicYear.start_date,
    end_date: props.academicYear.end_date,
})

function submit() {
    form.put(`/admin/academic-years/${props.academicYear.id}`)
}
</script>

<template>
    <AuthenticatedLayout>
        <h1 class="mb-6 text-xl font-semibold text-gray-800">Edit Academic Year</h1>

        <form @submit.prevent="submit" class="max-w-md space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                <input v-model="form.name" type="text" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
                <input v-model="form.start_date" type="date" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" />
                <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">End Date</label>
                <input v-model="form.end_date" type="date" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" />
                <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">{{ form.errors.end_date }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                <p class="text-sm text-gray-500">
                    {{ academicYear.status }}
                    <span class="text-gray-400">— change status from the Academic Years list</span>
                </p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded bg-gray-800 px-4 py-2 text-sm text-white hover:bg-gray-900 disabled:opacity-50"
            >
                Save
            </button>
        </form>
    </AuthenticatedLayout>
</template>