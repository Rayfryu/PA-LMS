<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    schoolClass: Object,
    enrollments: Array,
    activeCount: Number,
    eligibleStudents: Array,
});

const enrollForm = useForm({
    user_id: '',
    enrolled_at: new Date().toISOString().split('T')[0],
});

const submitEnrollment = () => {
    enrollForm.post(route('admin.classes.students.store', props.schoolClass.id), {
        onSuccess: () => enrollForm.reset(),
    });
};

const withdrawStudent = (enrollmentId) => {
    if (confirm('Are you sure you want to withdraw this student?')) {
        router.post(route('admin.classes.students.withdraw', {
            class: props.schoolClass.id,
            enrollment: enrollmentId
        }), {
            withdraw_date: new Date().toISOString().split('T')[0]
        });
    }
};
</script>

<template>
    <div class="p-6">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Class Roster: {{ schoolClass.name }}</h1>
            <div class="text-sm">
                Capacity: {{ activeCount }} / {{ schoolClass.capacity || 'Unlimited' }}
            </div>
        </div>

        <!-- Enroll Form -->
        <div class="mb-8 p-4 bg-gray-50 rounded-lg">
            <h2 class="text-lg font-semibold mb-4">Enroll Student</h2>
            <form @submit.prevent="submitEnrollment" class="flex gap-4">
                <select v-model="enrollForm.user_id" class="border rounded p-2 flex-1" required>
                    <option value="" disabled>Select Student</option>
                    <option v-for="student in eligibleStudents" :key="student.id" :value="student.id">
                        {{ student.name }}
                    </option>
                </select>
                <input type="date" v-model="enrollForm.enrolled_at" class="border rounded p-2" required>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" :disabled="enrollForm.processing">
                    Enroll
                </button>
            </form>
            <div v-if="enrollForm.errors.error" class="text-red-500 mt-2 text-sm">{{ enrollForm.errors.error }}</div>
        </div>

        <!-- Roster Table -->
        <table class="w-full bg-white rounded shadow text-left">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="p-4">Student Name</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Enrolled At</th>
                    <th class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="enrollment in enrollments" :key="enrollment.id" class="border-b">
                    <td class="p-4">{{ enrollment.user.name }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-sm rounded" :class="{
                            'bg-green-100 text-green-800': enrollment.status === 'active',
                            'bg-red-100 text-red-800': enrollment.status === 'withdrawn',
                            'bg-gray-100 text-gray-800': enrollment.status === 'transferred',
                        }">
                            {{ enrollment.status.toUpperCase() }}
                        </span>
                    </td>
                    <td class="p-4">{{ new Date(enrollment.enrolled_at).toLocaleDateString() }}</td>
                    <td class="p-4">
                        <button v-if="enrollment.status === 'active'" 
                                @click="withdrawStudent(enrollment.id)"
                                class="text-red-600 hover:text-red-800">
                            Withdraw
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>