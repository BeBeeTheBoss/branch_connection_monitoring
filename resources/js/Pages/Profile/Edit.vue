<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div class="profile-header-copy">
                <span class="profile-eyebrow"><i></i> Account settings</span>
                <h1>Profile</h1>
                <p>Manage your account details, password, and access.</p>
            </div>
        </template>

        <div class="profile-page">
            <div class="profile-grid">
                <article class="profile-card">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </article>

                <article class="profile-card">
                    <UpdatePasswordForm />
                </article>

                <article class="profile-card profile-card--danger">
                    <DeleteUserForm />
                </article>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
