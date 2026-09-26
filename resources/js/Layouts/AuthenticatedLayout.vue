<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const open = ref(false);
const themeOpen = ref(false);
const theme = ref("midnight");
const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const themes = [
    { id: "midnight", label: "Midnight", detail: "Deep navy", mark: "◐" },
    { id: "aurora", label: "Aurora", detail: "Indigo glow", mark: "✦" },
    { id: "daylight", label: "Daylight", detail: "Clean and bright", mark: "☀" },
];
const nav = [
    { label: "Overview", href: "dashboard", icon: "⌂", public: true },
    { label: "Branches", href: "hosts.index", icon: "◇" },
    { label: "Incidents", href: "incidents.index", icon: "⚠" },
    { label: "Settings", href: "settings.edit", icon: "⚙", admin: true },
    { label: "Users", href: "users.index", icon: "♙", admin: true },
];
const visibleNav = computed(() => nav.filter((item) => item.public || (user.value && (!item.admin || user.value.role === 'admin'))));
const isCurrent = (item) => route().current(item.href) || route().current(item.href.replace(".index", "") + ".*");
const applyTheme = (value) => {
    theme.value = value;
    document.documentElement.dataset.theme = value;
    window.localStorage.setItem("beeping-theme", value);
    themeOpen.value = false;
};
watch(theme, (value) => {
    if (typeof document !== "undefined") document.documentElement.dataset.theme = value;
});
onMounted(() => {
    theme.value = window.localStorage.getItem("beeping-theme") || "midnight";
});
</script>

<template>
    <div class="app-shell">
        <aside :class="{ 'app-sidebar--open': open }" class="app-sidebar">
            <div class="app-brand">
                <Link :href="route('dashboard')" class="app-brand__link">
                    <span class="app-brand__mark">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 12h4l2.1-6 4.1 12 2.2-6H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.4" opacity=".42"/></svg>
                    </span>
                    <span><b>Bee<span>Ping</span></b><small>Branch monitor</small></span>
                </Link>
                <button type="button" class="app-close-nav" aria-label="Close navigation" @click="open = false">×</button>
            </div>

            <nav class="app-navigation">
                <span class="app-navigation__label">Workspace</span>
                <Link v-for="item in visibleNav" :key="item.label" :href="route(item.href)" :class="{ 'is-active': isCurrent(item) }" class="app-nav-link" @click="open = false">
                    <span class="app-nav-link__icon">{{ item.icon }}</span>
                    {{ item.label }}
                    <span v-if="item.href === 'incidents.index'" class="app-nav-link__hint">Live</span>
                </Link>
            </nav>

            <div class="app-sidebar__bottom">
                <div class="app-sidebar__pulse"><i></i><span>Monitoring service</span><b>Online</b></div>
                <template v-if="user">
                    <Link :href="route('profile.edit')" class="app-user">
                        <span class="app-user__avatar">{{ user.name.charAt(0) }}</span>
                        <span class="app-user__copy"><b>{{ user.name }}</b><small>{{ user.role }}</small></span>
                        <span class="app-user__chevron">›</span>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button" class="app-signout">Sign out <span>↗</span></Link>
                </template>
                <template v-else>
                    <p class="app-sidebar__guest">Sign in to manage branch monitoring and alerts.</p>
                    <Link :href="route('login')" class="app-login-link">Admin sign in <span>→</span></Link>
                </template>
            </div>
        </aside>

        <div class="app-main">
            <header class="app-topbar">
                <button type="button" class="app-menu-button" aria-label="Open navigation" @click="open = true"><span></span><span></span><span></span></button>
                <div class="app-topbar__title"><slot name="header" /></div>
                <div class="app-topbar__actions">
                    <div class="app-theme-picker">
                        <button type="button" class="app-theme-button" :aria-expanded="themeOpen" @click="themeOpen = !themeOpen">
                            <span :class="'theme-mark theme-mark--' + theme">{{ themes.find((item) => item.id === theme)?.mark }}</span>
                            <span class="app-theme-button__label">{{ themes.find((item) => item.id === theme)?.label }}</span>
                            <span class="app-theme-button__chevron">⌄</span>
                        </button>
                        <div v-if="themeOpen" class="app-theme-menu">
                            <button v-for="item in themes" :key="item.id" type="button" :class="{ 'is-selected': theme === item.id }" @click="applyTheme(item.id)">
                                <span :class="'theme-mark theme-mark--' + item.id">{{ item.mark }}</span>
                                <span><b>{{ item.label }}</b><small>{{ item.detail }}</small></span>
                                <i v-if="theme === item.id">✓</i>
                            </button>
                        </div>
                    </div>
                    <div class="app-live-indicator"><i></i><span>Live</span></div>
                </div>
            </header>

            <main class="app-content">
                <div v-if="page.props.flash?.success" class="app-flash"><span>✓</span>{{ page.props.flash.success }}</div>
                <slot />
            </main>
        </div>

        <button v-if="open" type="button" class="app-mobile-scrim" aria-label="Close navigation" @click="open = false"></button>
    </div>
</template>
