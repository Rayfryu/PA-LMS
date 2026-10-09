<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    subjects: Object,
    filters: Object,
    can: Object,
});

const search = ref(props.filters?.search || '');
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    code: '',
    description: '',
    is_active: true,
});

let searchTimeout;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/admin/subjects', { search: value }, { 
            preserveState: true, 
            replace: true,
            preserveScroll: true
        });
    }, 300); // Jeda 300ms setelah selesai mengetik
});


const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (subject) => {
    isEditing.value = true;
    editingId.value = subject.id;
    form.name = subject.name;
    form.code = subject.code;
    form.description = subject.description || '';
    form.is_active = subject.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(`/admin/subjects/${editingId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/subjects', {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleStatus = (subject) => {
    router.post(`/admin/subjects/${subject.id}/toggle-status`);
};

const deleteSubject = (subject) => {
    if (confirm(`Are you sure you want to delete ${subject.name}?`)) {
        router.delete(`/admin/subjects/${subject.id}`);
    }
};
</script>

<template>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Subject Management</h1>
            <button v-if="can.create" @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                + Add Subject
            </button>
        </div>

        <!-- Search Bar -->
        <div class="mb-4">
            <input v-model="search" type="text" placeholder="Search by name or code..." class="w-full max-w-md border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500" />
        </div>

        <!-- Subjects Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b text-xs uppercase text-gray-600 font-semibold">
                        <th class="p-4">Code</th>
                        <th class="p-4">Name</th>
                        <th class="p-4">Description</th>
                        <th class="p-4">Status</th>
                        <th v-if="can.update || can.delete" class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    <tr v-for="subject in subjects.data" :key="subject.id" class="hover:bg-gray-50">
                        <td class="p-4 font-mono font-bold text-blue-600">{{ subject.code }}</td>
                        <td class="p-4 font-medium text-gray-900">{{ subject.name }}</td>
                        <td class="p-4 text-gray-500 max-w-xs truncate">{{ subject.description || '-' }}</td>
                        <td class="p-4">
                            <span :class="subject.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                                {{ subject.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td v-if="can.update || can.delete" class="p-4 text-right space-x-2">
                            <button v-if="can.update" @click="toggleStatus(subject)" class="text-xs px-2 py-1 border rounded hover:bg-gray-100">
                                {{ subject.is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button v-if="can.update" @click="openEditModal(subject)" class="text-blue-600 hover:text-blue-800 font-medium">
                                Edit
                            </button>
                            <button v-if="can.delete" @click="deleteSubject(subject)" class="text-red-600 hover:text-red-800 font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="subjects.data.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500">No subjects found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h2 class="text-lg font-bold mb-4">{{ isEditing ? 'Edit Subject' : 'Create Subject' }}</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Subject Code</label>
                        <input v-model="form.code" type="text" class="w-full border rounded p-2 text-sm uppercase" required />
                        <div v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Subject Name</label>
                        <input v-model="form.name" type="text" class="w-full border rounded p-2 text-sm" required />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea v-model="form.description" class="w-full border rounded p-2 text-sm" rows="3"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model="form.is_active" type="checkbox" id="is_active" />
                        <label for="is_active" class="text-sm">Active</label>
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