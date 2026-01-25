<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <AuthLayout
        title="Verify email"
        description="Please verify your email address by clicking on the link we just emailed to you."
    >
        <Head title="Email verification" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <div class="text-center">
            <TextLink
                href="#"
                @click.prevent="logout"
                class="mx-auto block text-sm"
            >
                Log out
            </TextLink>
        </div>
    </AuthLayout>
</template>
