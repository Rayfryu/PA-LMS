<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    course: Object,
    assignment: Object,
    submissions: Array,
});
</script>

<template>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold">Hasil Pengumpulan Tugas</h2>
                <p class="text-gray-600">{{ assignment.title }} — {{ course.school_class?.name }}</p>
            </div>
            <Link :href="`/teacher/courses/${course.id}/assignments`" class="text-gray-600 hover:underline">← Kembali</Link>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="p-3">Nama Siswa</th>
                        <th class="p-3">Waktu Pengumpulan</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Teks Jawaban</th>
                        <th class="p-3">Lampiran File</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="submissions.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500">Belum ada siswa yang mengumpulkan.</td>
                    </tr>
                    <tr v-for="sub in submissions" :key="sub.id" class="border-b hover:bg-gray-50">
                        <td class="p-3 font-medium">{{ sub.student?.name }}</td>
                        <td class="p-3">{{ new Date(sub.submitted_at).toLocaleString('id-ID') }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-700">
                                {{ sub.status }}
                            </span>
                        </td>
                        <td class="p-3 max-w-xs truncate">{{ sub.text_response || '-' }}</td>
                        <td class="p-3">
                            <a v-if="sub.attachment_path" :href="`/downloads/submissions/${sub.id}`" target="_blank" class="text-blue-600 hover:underline">
                                📄 Unduh File
                            </a>
                            <span v-else class="text-gray-400">-</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>