<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    classes: Array,
})
</script>

<template>
    <AuthenticatedLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800">Classes</h1>
            <Link href="/admin/classes/create" class="rounded bg-gray-800 px-3 py-1.5 text-sm text-white hover:bg-gray-900">
                + New Class
            </Link>
        </div>

        <table class="w-full overflow-hidden rounded border border-gray-200 bg-white text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2">Class Name</th>
                    <th class="px-4 py-2">Academic Year</th>
                    <th class="px-4 py-2">Jurusan</th>
                    <th class="px-4 py-2">Grade</th>
                    <th class="px-4 py-2">Wali Kelas</th>
                    <th class="px-4 py-2">Capacity</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in classes" :key="item.id" class="border-t border-gray-100">
                    <td class="px-4 py-2 font-medium text-gray-800">{{ item.name }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ item.academic_year_name }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ item.major_name }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ item.grade_level }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ item.homeroom_teacher_name ?? '—' }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ item.capacity ?? '—' }}</td>
                    <td class="px-4 py-2 text-right">
                        <Link :href="`/admin/classes/${item.id}/edit`" class="text-gray-600 hover:underline">Edit</Link>
                    </td>
                </tr>
                <tr v-if="classes.length === 0">
                    <td colspan="7" class="px-4 py-6 text-center text-gray-400">No classes yet.</td>
                </tr>
            </tbody>
        </table>
    </AuthenticatedLayout>
</template>