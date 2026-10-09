<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    courses: Object,
    subjects: Array,
    schoolClasses: Array,
    filters: Object,
    can: Object,
});

const search = ref(props.filters?.search || '');
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    subject_id: '',
    school_class_id: '',
    is_active: true,
});

let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/admin/courses', { search: value }, { 
            preserveState: true, 
            replace: true,
            preserveScroll: true
        });
    }, 300);
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (course) => {
    isEditing.value = true;
    editingId.value = course.id;
    form.subject_id = course.subject_id;
    form.school_class_id = course.school_class_id;
    form.is_active = course.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(`/admin/courses/${editingId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/courses', {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleStatus = (course) => {
    router.post(`/admin/courses/${course.id}/toggle-status`);
};

const deleteCourse = (course) => {
    if (confirm(`Are you sure you want to delete the course offering "${course.subject?.name}" for ${course.school_class?.name}?`)) {
        router.delete(`/admin/courses/${course.id}`);
    }
};
</script>

<template>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Course Management</h1>
                <p class="text-sm text-gray-500">Manage subject offerings assigned to school classes.</p>
            </div>
            <button v-if="can.create" @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition text-sm">
                + Create Course Offering
            </button>
        </div>

        <!-- Search Bar -->
        <div class="mb-4">
            <input v-model="search" type="text" placeholder="Search by subject code/name or class name..." class="w-full max-w-md border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500" />
        </div>

        <!-- Courses Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b text-xs uppercase text-gray-600 font-semibold">
                        <th class="p-4">Subject Code</th>
                        <th class="p-4">Subject Name</th>
                        <th class="p-4">Class</th>
                        <th class="p-4">Academic Year</th>
                        <th class="p-4">Status</th>
                        <th v-if="can.update || can.delete" class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    <tr v-for="course in courses.data" :key="course.id" class="hover:bg-gray-50">
                        <td class="p-4 font-mono font-bold text-blue-600">{{ course.subject?.code }}</td>
                        <td class="p-4 font-medium text-gray-900">{{ course.subject?.name }}</td>
                        <td class="p-4 text-gray-700">{{ course.school_class?.name || '-' }}</td>
                        <td class="p-4 text-gray-500">{{ course.academic_year?.name || '-' }}</td>
                        <td class="p-4">
                            <span :class="course.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                                {{ course.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td v-if="can.update || can.delete" class="p-4 text-right space-x-2">
                            <button v-if="can.update" @click="toggleStatus(course)" class="text-xs px-2 py-1 border rounded hover:bg-gray-100">
                                {{ course.is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button v-if="can.update" @click="openEditModal(course)" class="text-blue-600 hover:text-blue-800 font-medium">
                                Edit
                            </button>
                            <button v-if="can.delete" @click="deleteCourse(course)" class="text-red-600 hover:text-red-800 font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="courses.data.length === 0">
                        <td colspan="6" class="p-4 text-center text-gray-500">No course offerings found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h2 class="text-lg font-bold mb-4">{{ isEditing ? 'Edit Course Offering' : 'Create Course Offering' }}</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Select Subject</label>
                        <select v-model="form.subject_id" class="w-full border rounded p-2 text-sm" required>
                            <option value="" disabled>-- Select Subject --</option>
                            <option v-for="subj in subjects" :key="subj.id" :value="subj.id">
                                [{{ subj.code }}] {{ subj.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.subject_id" class="text-red-500 text-xs mt-1">{{ form.errors.subject_id }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Select School Class</label>
                        <select v-model="form.school_class_id" class="w-full border rounded p-2 text-sm" required>
                            <option value="" disabled>-- Select Class --</option>
                            <option v-for="cls in schoolClasses" :key="cls.id" :value="cls.id">
                                {{ cls.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.school_class_id" class="text-red-500 text-xs mt-1">{{ form.errors.school_class_id }}</div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input v-model="form.is_active" type="checkbox" id="is_active_course" />
                        <label for="is_active_course" class="text-sm">Active Offering</label>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm" :disabled="form.processing">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>