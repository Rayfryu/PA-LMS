<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    course: Object,
    materials: Array,
});

const form = useForm({
    title: '',
    description: '',
    content: '',
    attachment: null,
    status: 'draft',
});

const submit = () => {
    // Menggunakan URL langsung dengan backtick (`) alih-alih fungsi route()
    form.post(`/teacher/courses/${props.course.id}/materials`, {
        onSuccess: () => form.reset(),
        preserveScroll: true,
    });
};

const deleteMaterial = (id) => {
    if (confirm('Yakin ingin menghapus materi ini?')) {
        // Menggunakan URL langsung untuk fungsi hapus
        form.delete(`/teacher/courses/${props.course.id}/materials/${id}`);
    }
};
</script>

<template>
    <div class="p-6 max-w-7xl mx-auto">
        <h2 class="text-2xl font-bold mb-4">Materi Pembelajaran: {{ course.name }}</h2>

        <!-- Notifikasi Sukses -->
        <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ $page.props.flash.success }}
        </div>

        <!-- Form Tambah Materi -->
        <div class="bg-white p-4 rounded shadow mb-8">
            <h3 class="font-semibold mb-4 text-lg">Tambah Materi Baru</h3>
            <form @submit.prevent="submit" class="space-y-4">
                
                <div>
                    <label class="block mb-1">Judul Materi <span class="text-red-500">*</span></label>
                    <input v-model="form.title" type="text" class="border p-2 w-full rounded" :class="{'border-red-500': form.errors.title}" required />
                    <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                </div>

                <div>
                    <label class="block mb-1">Deskripsi Singkat</label>
                    <textarea v-model="form.description" class="border p-2 w-full rounded" :class="{'border-red-500': form.errors.description}"></textarea>
                    <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                </div>

                <div>
                    <label class="block mb-1">File Lampiran (Opsional)</label>
                    <input type="file" @input="form.attachment = $event.target.files[0]" class="border p-2 w-full rounded" :class="{'border-red-500': form.errors.attachment}" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar,.jpg,.jpeg,.png,.mp4,.mkv,.avi" />
                    <div class="text-xs text-gray-500 mt-1">Format: Dokumen, ZIP, JPG, PNG, MP4, dll. Maksimal: 50MB</div>
                    <div v-if="form.errors.attachment" class="text-red-500 text-sm mt-1">{{ form.errors.attachment }}</div>
                </div>

                <div>
                    <label class="block mb-1">Status</label>
                    <select v-model="form.status" class="border p-2 rounded" :class="{'border-red-500': form.errors.status}">
                        <option value="draft">Draft (Disembunyikan dari siswa)</option>
                        <option value="published">Publish (Dapat dilihat siswa)</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition" :disabled="form.processing">
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan Materi</span>
                </button>
            </form>
        </div>

        <!-- Daftar Materi -->
        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold mb-4 text-lg">Daftar Materi</h3>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="border-b p-3">Judul</th>
                        <th class="border-b p-3">Status</th>
                        <th class="border-b p-3">Lampiran</th>
                        <th class="border-b p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="materials.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">Belum ada materi.</td>
                    </tr>
                    <tr v-for="material in materials" :key="material.id" class="hover:bg-gray-50">
                        <td class="p-3 border-b">{{ material.title }}</td>
                        <td class="p-3 border-b">
                            <span class="px-2 py-1 rounded text-xs font-semibold" :class="material.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                {{ material.status === 'published' ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td class="p-3 border-b">
                            <!-- Menggunakan URL langsung dengan v-bind (:href) -->
                            <a v-if="material.attachment_path" :href="`/downloads/materials/${material.id}`" class="text-blue-600 hover:underline flex items-center gap-1" target="_blank">
                                📄 Unduh
                            </a>
                            <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="p-3 border-b">
                            <button @click="deleteMaterial(material.id)" class="text-red-600 hover:text-red-800 font-semibold text-sm">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>