<script setup>
import { ref } from "vue";
import { useForm, router, Link } from "@inertiajs/vue3";

const props = defineProps({
    course: Object,
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const daysOfWeek = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat"];

const form = useForm({
    day_of_week: "Senin",
    start_time: "",
    end_time: "",
    room: "",
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (schedule) => {
    isEditing.value = true;
    editingId.value = schedule.id;

    // Format waktu (H:i) dari database
    form.day_of_week = schedule.day_of_week;
    form.start_time = schedule.start_time.substring(0, 5);
    form.end_time = schedule.end_time.substring(0, 5);
    form.room = schedule.room || "";

    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (isEditing.value) {
        form.put(
            `/admin/courses/${props.course.id}/schedules/${editingId.value}`,
            {
                onSuccess: () => closeModal(),
            },
        );
    } else {
        form.post(`/admin/courses/${props.course.id}/schedules`, {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSchedule = (schedule) => {
    if (
        confirm(
            `Apakah Anda yakin ingin menghapus jadwal hari ${schedule.day_of_week}?`,
        )
    ) {
        router.delete(
            `/admin/courses/${props.course.id}/schedules/${schedule.id}`,
        );
    }
};
</script>

<template>
    <div class="p-6 max-w-7xl mx-auto">
        <!-- Header Info -->
        <div
            class="mb-6 bg-white p-6 rounded-lg shadow-sm border border-gray-100"
        >
            <div class="flex justify-between items-start">
                <div>
                    <Link
                        href="/admin/courses"
                        class="text-blue-600 text-sm hover:underline mb-2 inline-block"
                        >&larr; Kembali ke Daftar Course</Link
                    >
                    <h1 class="text-2xl font-bold text-gray-800">
                        Manajemen Jadwal
                    </h1>
                    <div
                        class="mt-2 text-sm text-gray-600 grid grid-cols-2 gap-x-8 gap-y-2"
                    >
                        <p>
                            <span class="font-semibold">Mata Pelajaran:</span>
                            {{ course.subject?.name }} ({{
                                course.subject?.code
                            }})
                        </p>
                        <p>
                            <span class="font-semibold">Kelas:</span>
                            {{ course.school_class?.name }}
                        </p>
                        <p>
                            <span class="font-semibold">Tahun Ajaran:</span>
                            {{ course.school_class?.academic_year?.name }}
                        </p>
                        <p>
                            <span class="font-semibold">Pengajar:</span>
                            {{ course.teacher?.name || "Belum Ditugaskan" }}
                        </p>
                    </div>
                </div>
                <button
                    @click="openCreateModal"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition text-sm"
                >
                    + Tambah Jadwal
                </button>
            </div>
        </div>

        <!-- Validation Errors (General Conflict) -->
        <div
            v-if="form.errors.general || form.errors.time || form.errors.room"
            class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 text-sm"
        >
            <strong class="font-bold">Gagal menyimpan jadwal!</strong>
            <ul class="list-disc ml-5 mt-1">
                <li v-if="form.errors.general">{{ form.errors.general }}</li>
                <li v-if="form.errors.time">{{ form.errors.time }}</li>
                <li v-if="form.errors.room">{{ form.errors.room }}</li>
            </ul>
        </div>

        <!-- Schedule Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-gray-100 border-b text-xs uppercase text-gray-600 font-semibold"
                    >
                        <th class="p-4">Hari</th>
                        <th class="p-4">Jam Mulai</th>
                        <th class="p-4">Jam Selesai</th>
                        <th class="p-4">Ruangan</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    <tr
                        v-for="schedule in course.schedules"
                        :key="schedule.id"
                        class="hover:bg-gray-50"
                    >
                        <td class="p-4 font-medium text-gray-900">
                            {{ schedule.day_of_week }}
                        </td>
                        <td class="p-4 font-mono">
                            {{ schedule.start_time.substring(0, 5) }} WIB
                        </td>
                        <td class="p-4 font-mono">
                            {{ schedule.end_time.substring(0, 5) }} WIB
                        </td>
                        <td class="p-4">{{ schedule.room || "-" }}</td>
                        <td class="p-4 text-right space-x-3">
                            <button
                                @click="openEditModal(schedule)"
                                class="text-blue-600 hover:text-blue-800 font-medium"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteSchedule(schedule)"
                                class="text-red-600 hover:text-red-800 font-medium"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                    <tr v-if="course.schedules.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500">
                            Belum ada jadwal untuk course ini.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Form -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h2 class="text-lg font-bold mb-4">
                    {{ isEditing ? "Edit Jadwal" : "Tambah Jadwal Baru" }}
                </h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1"
                            >Hari</label
                        >
                        <select
                            v-model="form.day_of_week"
                            class="w-full border rounded p-2 text-sm"
                            required
                        >
                            <option
                                v-for="day in daysOfWeek"
                                :key="day"
                                :value="day"
                            >
                                {{ day }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.day_of_week"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.day_of_week }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Jam Mulai</label
                            >
                            <input
                                type="time"
                                v-model="form.start_time"
                                class="w-full border rounded p-2 text-sm font-mono"
                                required
                            />
                            <div
                                v-if="form.errors.start_time"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.start_time }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Jam Selesai</label
                            >
                            <input
                                type="time"
                                v-model="form.end_time"
                                class="w-full border rounded p-2 text-sm font-mono"
                                required
                            />
                            <div
                                v-if="form.errors.end_time"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.end_time }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1"
                            >Ruangan (Opsional)</label
                        >
                        <input
                            type="text"
                            v-model="form.room"
                            placeholder="Contoh: Lab Komputer A"
                            class="w-full border rounded p-2 text-sm"
                        />
                        <div
                            v-if="form.errors.room"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.room }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 border rounded text-sm"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded text-sm disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
