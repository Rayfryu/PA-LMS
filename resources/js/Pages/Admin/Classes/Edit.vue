<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    class: Object,
    academicYears: Array,
    majors: Array,
    teachers: Array,
    gradeLevels: Array,
    academicYearLocked: Boolean,
})

const form = useForm({
    academic_year_id: props.class.academic_year_id,
    major_id: props.class.major_id,
    grade_level: props.class.grade_level,
    name: props.class.name,
    homeroom_teacher_id: props.class.homeroom_teacher_id ?? '',
    capacity: props.class.capacity ?? '',
})

function submit() {
    form.transform((data) => ({
        ...data,
        homeroom_teacher_id: data.homeroom_teacher_id || null,
        capacity: data.capacity || null,
    })).put(`/admin/classes/${props.class.id}`)
}
</script>

<template>
    <AuthenticatedLayout>
        <h1 class="mb-6 text-xl font-semibold text-gray-800">Edit Class</h1>

        <form @submit.prevent="submit" class="max-w-md space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Academic Year</label>
                <select
                    v-model="form.academic_year_id"
                    :disabled="academicYearLocked"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500"
                >
                    <option v-for="year in academicYears" :key="year.id" :value="year.id">
                        {{ year.name }} ({{ year.status }})
                    </option>
                </select>
                <p v-if="academicYearLocked" class="mt-1 text-xs text-gray-400">
                    This class belongs to an archived academic year and can no longer be moved.
                </p>
                <p v-if="form.errors.academic_year_id" class="mt-1 text-sm text-red-600">{{ form.errors.academic_year_id }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Major</label>
                <select v-model="form.major_id" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option v-for="major in majors" :key="major.id" :value="major.id">{{ major.name }}</option>
                </select>
                <p v-if="form.errors.major_id" class="mt-1 text-sm text-red-600">{{ form.errors.major_id }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Grade Level</label>
                <select v-model="form.grade_level" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option v-for="level in gradeLevels" :key="level" :value="level">{{ level }}</option>
                </select>
                <p v-if="form.errors.grade_level" class="mt-1 text-sm text-red-600">{{ form.errors.grade_level }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Class Name</label>
                <input v-model="form.name" type="text" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Homeroom Teacher</label>
                <select v-model="form.homeroom_teacher_id" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="">None</option>
                    <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{ teacher.name }}</option>
                </select>
                <p v-if="form.errors.homeroom_teacher_id" class="mt-1 text-sm text-red-600">{{ form.errors.homeroom_teacher_id }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Capacity</label>
                <input v-model="form.capacity" type="number" min="1" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" />
                <p v-if="form.errors.capacity" class="mt-1 text-sm text-red-600">{{ form.errors.capacity }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded bg-gray-800 px-4 py-2 text-sm text-white hover:bg-gray-900 disabled:opacity-50"
            >
                Save
            </button>
        </form>
    </AuthenticatedLayout>
</template>