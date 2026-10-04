<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50">
        <div class="w-full max-w-sm rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h1 class="mb-6 text-lg font-semibold text-gray-800">Login to SALMS</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="username"
                        required
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                        {{ form.errors.password }}
                    </p>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input v-model="form.remember" type="checkbox" class="rounded border-gray-300" />
                    Remember me
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded bg-gray-800 py-2 text-sm font-medium text-white hover:bg-gray-900 disabled:opacity-50"
                >
                    {{ form.processing ? 'Logging in…' : 'Login' }}
                </button>
            </form>
        </div>
    </div>
</template>
