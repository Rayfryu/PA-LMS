<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    majors: Array,
})
</script>

<template>
    <AuthenticatedLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800">Majors</h1>
            <Link href="/admin/majors/create" class="rounded bg-gray-800 px-3 py-1.5 text-sm text-white hover:bg-gray-900">
                + New Major
            </Link>
        </div>

        <table class="w-full overflow-hidden rounded border border-gray-200 bg-white text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2">Code</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="major in majors" :key="major.id" class="border-t border-gray-100">
                    <td class="px-4 py-2 font-medium text-gray-800">{{ major.code }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ major.name }}</td>
                    <td class="px-4 py-2">
                        <span
                            class="rounded px-2 py-0.5 text-xs font-medium"
                            :class="major.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600'"
                        >
                            {{ major.is_active ? 'active' : 'inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <Link :href="`/admin/majors/${major.id}/edit`" class="text-gray-600 hover:underline">Edit</Link>
                    </td>
                </tr>
                <tr v-if="majors.length === 0">
                    <td colspan="4" class="px-4 py-6 text-center text-gray-400">No majors yet.</td>
                </tr>
            </tbody>
        </table>
    </AuthenticatedLayout>
</template>