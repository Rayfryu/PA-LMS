<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    course: Object,
    assignments: Array,
});

const form = useForm({
    title: '',
    description: '',
    due_at: '',
    allowed_file_types: 'pdf,doc,docx,zip,rar,jpg,png',
    max_file_size_mb: 10,
    status: 'draft',
});

const submit = () => {
    form.post(`/teacher/courses/${props.course.id}/assignments`, {
        onSuccess: () => form.reset(),
        preserveScroll: true,
    });
};

const deleteAssignment = (id) => {
    if (confirm('Yakin ingin menghapus tugas ini? Semua pengumpulan siswa akan ikut terhapus.')) {
        form.delete(`/teacher/courses/${props.course.id}/assignments/${id}`);
    }
};
</script>

<template>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Kelola Tugas: {{ course.subject?.name }} ({{ course.school_class?.name }})</h2>
        </div>

        <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ $page.props.flash.success }}
        </div>

        <!-- Form Tambah Tugas -->
        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold text-lg mb-4">Buat Tugas Baru</h3>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block font-medium mb-1">Judul Tugas *</label>
                    <input v-model="form.title" type="text" class="w-full border rounded p-2" required />
                    <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                </div>

                <div>
                    <label class="block font-medium mb-1">Instruksi / Deskripsi</label>
                    <textarea v-model="form.description" rows="3" class="w-full border rounded p-2"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium mb-1">Batas Waktu (Deadline)</label>
                        <input v-model="form.due_at" type="datetime-local" class="w-full border rounded p-2" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Status Publikasi</label>
                        <select v-model="form.status" class="w-full border rounded p-2">
                            <option value="draft">Draft (Disembunyikan dari siswa)</option>
                            <option value="published">Published (Siswa dapat mengumpulkan)</option>
                            <option value="closed">Closed (Ditutup)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium mb-1">Ekstensi File Diizinkan</label>
                        <input v-model="form.allowed_file_types" type="text" class="w-full border rounded p-2" placeholder="pdf,doc,docx,zip" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Maksimal Ukuran (MB)</label>
                        <input v-model="form.max_file_size_mb" type="number" class="w-full border rounded p-2" min="1" max="50" />
                    </div>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" :disabled="form.processing">
                    Simpan Tugas
                </button>
            </form>
        </div>

        <!-- Daftar Tugas -->
        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold text-lg mb-4">Daftar Tugas</h3>
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="p-3">Judul</th>
                        <th class="p-3">Deadline</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Pengumpulan</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="assignments.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500">Belum ada tugas.</td>
                    </tr>
                    <tr v-for="item in assignments" :key="item.id" class="border-b hover:bg-gray-50">
                        <td class="p-3 font-medium">{{ item.title }}</td>
                        <td class="p-3">{{ item.due_at ? new Date(item.due_at).toLocaleString('id-ID') : 'Tanpa Deadline' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold"
                                :class="{
                                    'bg-gray-200 text-gray-700': item.status === 'draft',
                                    'bg-green-100 text-green-700': item.status === 'published',
                                    'bg-red-100 text-red-700': item.status === 'closed'
                                }">
                                {{ item.status.toUpperCase() }}
                            </span>
                        </td>
                        <td class="p-3">
                            <Link :href="`/teacher/courses/${course.id}/assignments/${item.id}/submissions`" class="text-blue-600 hover:underline">
                                {{ item.submissions_count || 0 }} Siswa
                            </Link>
                        </td>
                        <td class="p-3 space-x-2">
                            <button @click="deleteAssignment(item.id)" class="text-red-600 hover:underline">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>