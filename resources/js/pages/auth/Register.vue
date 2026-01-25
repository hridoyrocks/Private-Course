<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthBase
        title="Registration Disabled"
        description="User registration is disabled. Please contact admin."
    >
        <Head title="Register" />

        <div class="text-center text-sm text-muted-foreground">
            Already have an account?
            <TextLink
                href="/login"
                class="underline underline-offset-4"
            >Log in</TextLink>
        </div>
    </AuthBase>
</template>
