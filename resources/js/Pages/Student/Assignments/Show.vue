<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    course: Object,
    assignment: Object,
    submission: Object,
});

const form = useForm({
    text_response: props.submission?.text_response || '',
    attachment: null,
});

const submitWork = () => {
    form.post(`/student/courses/${props.course.id}/assignments/${props.assignment.id}/submit`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="p-6 max-w-4xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">{{ assignment.title }}</h2>
            <Link :href="`/student/courses/${course.id}/assignments`" class="text-gray-600 hover:underline">← Kembali ke List Tugas</Link>
        </div>

        <div v-if="$page.props.errors?.submission" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ $page.props.errors.submission }}
        </div>

        <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ $page.props.flash.success }}
        </div>

        <!-- Detail Tugas -->
        <div class="bg-white p-6 rounded shadow space-y-3">
            <p class="text-gray-700 whitespace-pre-line">{{ assignment.description || 'Tidak ada deskripsi.' }}</p>
            <div class="text-sm text-gray-500 border-t pt-3 flex gap-6">
                <div>Deadline: <span class="font-semibold text-gray-800">{{ assignment.due_at ? new Date(assignment.due_at).toLocaleString('id-ID') : 'Tanpa Deadline' }}</span></div>
                <div>Status Tugas: <span class="font-semibold uppercase text-gray-800">{{ assignment.status }}</span></div>
            </div>
        </div>

        <!-- Status Pengumpulan -->
        <div class="bg-white p-6 rounded shadow space-y-4">
            <h3 class="font-semibold text-lg border-b pb-2">Status Pengumpulan Anda</h3>

            <div v-if="submission" class="bg-blue-50 p-4 rounded text-sm space-y-2 border border-blue-200">
                <div>Dikirim Pada: <strong>{{ new Date(submission.submitted_at).toLocaleString('id-ID') }}</strong></div>
                <div>Status: <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-200 text-blue-800 uppercase">{{ submission.status }}</span></div>
                <div v-if="submission.text_response">Jawaban Teks: <p class="mt-1 p-2 bg-white rounded border">{{ submission.text_response }}</p></div>
                <div v-if="submission.attachment_path">
                    Lampiran Saat Ini: 
                    <a :href="`/downloads/submissions/${submission.id}`" class="text-blue-600 hover:underline font-semibold ml-1" target="_blank">
                        📄 Lihat/Unduh Lampiran
                    </a>
                </div>
            </div>

            <!-- Form Pengumpulan / Resubmission -->
            <form v-if="assignment.status === 'published'" @submit.prevent="submitWork" class="space-y-4 pt-2">
                <div>
                    <label class="block font-medium mb-1">Teks Jawaban</label>
                    <textarea v-model="form.text_response" rows="4" class="w-full border rounded p-2" placeholder="Tuliskan jawaban Anda di sini..."></textarea>
                </div>

                <div>
                    <label class="block font-medium mb-1">Unggah File Lampiran</label>
                    <input type="file" @input="form.attachment = $event.target.files[0]" class="w-full border rounded p-2" />
                    <div class="text-xs text-gray-500 mt-1">Format diizinkan: {{ assignment.allowed_file_types }}. Maksimal: {{ assignment.max_file_size_mb }}MB</div>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded font-medium hover:bg-blue-700" :disabled="form.processing">
                    {{ submission ? 'Kirim Ulang Tugas (Update)' : 'Kirim Tugas' }}
                </button>
            </form>

            <div v-else class="text-red-500 font-medium">
                Pengumpulan tugas telah ditutup.
            </div>
        </div>
    </div>
</template>