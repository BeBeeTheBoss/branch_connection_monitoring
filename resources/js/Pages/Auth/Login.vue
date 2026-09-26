<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Sign in" />

    <main class="min-h-screen bg-[#07111f] text-slate-100 lg:grid lg:grid-cols-[1.05fr_.95fr]">
        <section class="relative hidden min-h-screen overflow-hidden border-r border-white/5 bg-[#0a1728] px-14 py-12 lg:flex lg:flex-col lg:justify-between xl:px-20">
            <div class="pointer-events-none absolute -left-40 top-24 h-96 w-96 rounded-full bg-emerald-400/10 blur-[100px]"></div>
            <div class="pointer-events-none absolute -bottom-48 right-0 h-[30rem] w-[30rem] rounded-full bg-cyan-500/10 blur-[110px]"></div>
            <Link href="/" class="relative z-10 inline-flex w-fit items-center gap-3">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-400 text-slate-950 shadow-lg shadow-emerald-950/40">
                    <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" aria-hidden="true"><path d="M3 12h4l2.1-6 4.1 12 2.2-6H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" opacity=".4"/></svg>
                </span>
                <span class="text-xl font-bold tracking-tight">Bee<span class="text-emerald-400">Ping</span></span>
            </Link>

            <div class="relative z-10 max-w-xl pb-8">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/5 px-3 py-1.5 text-xs font-medium text-emerald-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_10px_#34d399]"></span>
                    Branch network monitoring
                </div>
                <h1 class="text-5xl font-semibold leading-[1.12] tracking-tight xl:text-6xl">Know your network is <span class="text-emerald-400">always there.</span></h1>
                <p class="mt-6 max-w-lg text-base leading-7 text-slate-400">A clear view of every branch, host, and connection. Sign in to check your network health.</p>
                <div class="mt-12 flex items-center gap-3 text-sm text-slate-500">
                    <div class="flex -space-x-2"><span class="grid h-8 w-8 place-items-center rounded-full border-2 border-[#0a1728] bg-slate-700 text-[10px] font-bold text-slate-200">BR</span><span class="grid h-8 w-8 place-items-center rounded-full border-2 border-[#0a1728] bg-emerald-900 text-[10px] font-bold text-emerald-200">IP</span><span class="grid h-8 w-8 place-items-center rounded-full border-2 border-[#0a1728] bg-cyan-900 text-[10px] font-bold text-cyan-200">✓</span></div>
                    <span>One dashboard. Every branch.</span>
                </div>
            </div>
            <div class="relative z-10 flex items-center justify-between text-xs text-slate-600"><span>BeePing Network Operations</span><span>Secure access</span></div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <Link href="/" class="mb-12 inline-flex items-center gap-2.5 lg:hidden">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-400 text-slate-950"><svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true"><path d="M3 12h4l2.1-6 4.1 12 2.2-6H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" opacity=".4"/></svg></span>
                    <span class="text-lg font-bold">Bee<span class="text-emerald-400">Ping</span></span>
                </Link>
                <div class="mb-8">
                    <p class="text-xs font-semibold uppercase tracking-[.22em] text-emerald-400">Welcome back</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-white">Sign in to BeePing</h2>
                    <p class="mt-2 text-sm text-slate-400">Use your administrator account to continue.</p>
                </div>

                <div v-if="status" class="mb-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ status }}</div>

                <form class="space-y-5" @submit.prevent="submit">
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-slate-300">Email address</label>
                        <input id="email" v-model="form.email" type="email" autocomplete="username" required autofocus placeholder="you@company.com" class="block w-full rounded-xl border border-slate-700/80 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-600 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3"><label for="password" class="block text-sm font-medium text-slate-300">Password</label><Link v-if="canResetPassword" :href="route('password.request')" class="text-xs font-medium text-emerald-400 transition hover:text-emerald-300">Forgot password?</Link></div>
                        <input id="password" v-model="form.password" type="password" autocomplete="current-password" required placeholder="Enter your password" class="block w-full rounded-xl border border-slate-700/80 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-600 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>
                    <div class="flex items-center"><Checkbox name="remember" v-model:checked="form.remember" class="rounded border-slate-600 bg-slate-900 text-emerald-400 shadow-sm focus:ring-emerald-400 focus:ring-offset-slate-950" /><span class="ms-2 text-sm text-slate-400">Keep me signed in</span></div>
                    <button type="submit" :disabled="form.processing" class="group flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-400 px-4 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-300 focus:outline-none focus:ring-4 focus:ring-emerald-400/20 disabled:cursor-not-allowed disabled:opacity-60">
                        {{ form.processing ? 'Signing in…' : 'Sign in' }}
                        <svg v-if="!form.processing" viewBox="0 0 20 20" fill="none" class="h-4 w-4 transition group-hover:translate-x-0.5" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </form>

                <div class="mt-8 flex items-center gap-3 text-xs text-slate-600"><span class="h-px flex-1 bg-slate-800"></span><span>Protected administrator access</span><span class="h-px flex-1 bg-slate-800"></span></div>
                <p class="mt-8 text-center text-xs text-slate-600">© {{ new Date().getFullYear() }} BeePing · Branch network monitoring</p>
            </div>
        </section>
    </main>
</template>
