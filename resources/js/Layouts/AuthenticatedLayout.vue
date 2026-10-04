<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth.user)

function logout() {
    router.post('/logout')
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
                <span class="font-semibold text-gray-800">SALMS</span>

                <div class="flex items-center gap-4 text-sm text-gray-600">
                    <span v-if="user">
                        {{ user.name }}
                        <span class="text-gray-400">({{ user.role }})</span>
                    </span>
                    <button
                        type="button"
                        class="rounded border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-100"
                        @click="logout"
                    >
                        Logout
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8">
            <slot />
        </main>
    </div>
</template>
