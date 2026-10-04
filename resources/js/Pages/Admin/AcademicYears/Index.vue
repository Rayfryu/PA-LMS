<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({
    academicYears: Array,
})

function activate(academicYear) {
    if (!confirm(`Activate ${academicYear.name}? Only one academic year can be active at a time.`)) return
    router.post(`/admin/academic-years/${academicYear.id}/activate`)
}

function archive(academicYear) {
    if (!confirm(`Archive ${academicYear.name}? This cannot be undone.`)) return
    router.post(`/admin/academic-years/${academicYear.id}/archive`)
}

const statusStyle = {
    draft: 'bg-gray-100 text-gray-700',
    active: 'bg-green-100 text-green-700',
    archived: 'bg-slate-200 text-slate-600',
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800">Academic Years</h1>
            <Link
                href="/admin/academic-years/create"
                class="rounded bg-gray-800 px-3 py-1.5 text-sm text-white hover:bg-gray-900"
            >
                + New Academic Year
            </Link>
        </div>

        <table class="w-full overflow-hidden rounded border border-gray-200 bg-white text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Start</th>
                    <th class="px-4 py-2">End</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="year in academicYears" :key="year.id" class="border-t border-gray-100">
                    <td class="px-4 py-2 font-medium text-gray-800">{{ year.name }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ year.start_date }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ year.end_date }}</td>
                    <td class="px-4 py-2">
                        <span class="rounded px-2 py-0.5 text-xs font-medium" :class="statusStyle[year.status]">
                            {{ year.status }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <Link :href="`/admin/academic-years/${year.id}/edit`" class="text-gray-600 hover:underline">
                            Edit
                        </Link>
                        <button
                            v-if="year.status === 'draft'"
                            class="text-green-700 hover:underline"
                            @click="activate(year)"
                        >
                            Activate
                        </button>
                        <button
                            v-if="year.status !== 'archived'"
                            class="text-red-600 hover:underline"
                            @click="archive(year)"
                        >
                            Archive
                        </button>
                    </td>
                </tr>
                <tr v-if="academicYears.length === 0">
                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No academic years yet.</td>
                </tr>
            </tbody>
        </table>
    </AuthenticatedLayout>
</template>