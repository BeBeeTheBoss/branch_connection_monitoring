<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({ hosts: Object, summary: Object });
const page = usePage();
const filter = ref("all");
const now = ref(new Date());
let refreshTimer;
let clockTimer;

const statusMeta = {
    active: { label: "Online", tone: "healthy", icon: "✓" },
    down: { label: "Offline", tone: "critical", icon: "!" },
    unstable: { label: "Unstable", tone: "warning", icon: "!" },
    unknown: { label: "Awaiting check", tone: "neutral", icon: "·" },
};

const hostList = computed(() => props.hosts?.data || []);
const total = computed(() => props.summary?.total || 0);
const unknown = computed(() => Math.max(0, total.value - (props.summary?.active || 0) - (props.summary?.down || 0) - (props.summary?.unstable || 0)));
const reachable = computed(() => (props.summary?.active || 0) + (props.summary?.unstable || 0));
const healthScore = computed(() => total.value ? Math.round((reachable.value / total.value) * 100) : 0);
const scoreOffset = computed(() => 251 - ((healthScore.value / 100) * 251));
const attentionCount = computed(() => (props.summary?.down || 0) + (props.summary?.unstable || 0));
const averageLatency = computed(() => {
    const values = hostList.value.map((host) => Number(host.current_latency)).filter((value) => Number.isFinite(value));
    return values.length ? Math.round(values.reduce((sum, value) => sum + value, 0) / values.length) : null;
});
const statusRows = computed(() => [
    { key: "active", label: "Online", value: props.summary?.active || 0, tone: "healthy" },
    { key: "unstable", label: "Unstable", value: props.summary?.unstable || 0, tone: "warning" },
    { key: "down", label: "Offline", value: props.summary?.down || 0, tone: "critical" },
    { key: "unknown", label: "Not checked", value: unknown.value, tone: "neutral" },
]);
const attentionHosts = computed(() => [...hostList.value]
    .filter((host) => ["down", "unstable"].includes(host.current_status))
    .sort((a, b) => (a.current_status === "down" ? -1 : 1) - (b.current_status === "down" ? -1 : 1))
    .slice(0, 5));
const visibleHosts = computed(() => {
    const rows = filter.value === "all" ? hostList.value : hostList.value.filter((host) => host.current_status === filter.value);
    const order = { down: 0, unstable: 1, unknown: 2, active: 3 };
    return [...rows].sort((a, b) => (order[a.current_status] ?? 4) - (order[b.current_status] ?? 4));
});

const percentage = (value) => total.value ? Math.round((value / total.value) * 100) : 0;
const statusFor = (host) => statusMeta[host.current_status] || statusMeta.unknown;
const displayLatency = (host) => host.current_latency === null || host.current_latency === undefined ? "—" : Math.round(host.current_latency) + " ms";
const lastCheck = (host) => {
    // if (!host.last_checked_at) return "Not checked yet";
    // const seconds = Math.max(0, Math.round((now.value - new Date(host.last_checked_at)) / 1000));
    // if (seconds < 60) return "Just now";
    // if (seconds < 3600) return Math.floor(seconds / 60) + " min ago";
    return new Date(host.last_checked_at).toLocaleString([], { month: "short", day: "numeric", hour: "2-digit", minute: "2-digit" });
};

onMounted(() => {
    refreshTimer = window.setInterval(() => router.reload({ only: ["hosts", "summary"], preserveScroll: true, preserveState: true }), 30000);
    clockTimer = window.setInterval(() => { now.value = new Date(); }, 10000);
});
onUnmounted(() => {
    window.clearInterval(refreshTimer);
    window.clearInterval(clockTimer);
});
</script>

<template>
    <Head title="Network overview" />

    <AuthenticatedLayout>
        <template #header>
            <div class="overview-header-copy">
                <span class="overview-eyebrow"><i></i> Network command center</span>
                <h1>Branch network overview</h1>
                <p>Live health, latency, and availability across every branch.</p>
            </div>
        </template>

        <div class="overview-page">
            <section class="overview-hero">
                <div class="overview-hero__copy">
                    <div class="overview-hero__badge"><span class="overview-live-dot"></span>Live monitoring</div>
                    <h2>{{ attentionCount ? "Your network needs attention." : "Everything is running smoothly." }}</h2>
                    <p>{{ total ? reachable + " of " + total + " branches are currently reachable." : "Add your first branch to begin monitoring." }}</p>
                    <div class="overview-hero__actions">
                        <Link v-if="page.props.auth?.user?.role === 'admin'" :href="route('hosts.create')" class="overview-button overview-button--primary"><span>+</span> Add branch</Link>
                        <a href="#branches" class="overview-button overview-button--secondary">View branches <span>↓</span></a>
                    </div>
                </div>
                <div class="overview-score" :class="{ 'overview-score--alert': attentionCount }">
                    <svg viewBox="0 0 100 100" aria-hidden="true"><circle class="overview-score__track" cx="50" cy="50" r="40" /><circle class="overview-score__value" cx="50" cy="50" r="40" :style="{ strokeDashoffset: scoreOffset }" /></svg>
                    <div><strong>{{ healthScore }}<small>%</small></strong><span>Reachability</span></div>
                </div>
                <div class="overview-hero__glow"></div>
            </section>

            <section class="overview-metrics">
                <article class="overview-metric"><div class="overview-metric__icon overview-metric__icon--blue">⌘</div><div><span>Total branches</span><strong>{{ total }}</strong><small>Monitored endpoints</small></div></article>
                <article class="overview-metric"><div class="overview-metric__icon overview-metric__icon--green">↗</div><div><span>Average latency</span><strong>{{ averageLatency === null ? "—" : averageLatency }}<em v-if="averageLatency !== null"> ms</em></strong><small>Across visible branches</small></div></article>
                <article class="overview-metric"><div class="overview-metric__icon overview-metric__icon--amber">!</div><div><span>Needs attention</span><strong>{{ attentionCount }}</strong><small>{{ attentionCount ? "Review affected branches" : "No active incidents" }}</small></div></article>
                <article class="overview-metric"><div class="overview-metric__icon overview-metric__icon--violet">◌</div><div><span>Refresh cycle</span><strong>30<em> sec</em></strong><small>Dashboard data refresh</small></div></article>
            </section>

            <section class="overview-grid">
                <article class="overview-panel overview-panel--health">
                    <div class="overview-panel__heading"><div><span class="overview-panel__eyebrow">Fleet status</span><h3>Availability breakdown</h3></div><span class="overview-panel__total">{{ total }} total</span></div>
                    <div class="overview-distribution"><span v-for="row in statusRows" :key="row.key" :class="'overview-distribution__segment overview-distribution__segment--' + row.tone" :style="{ width: percentage(row.value) + '%' }"></span></div>
                    <div class="overview-status-list"><div v-for="row in statusRows" :key="row.key" class="overview-status-row"><span :class="'overview-status-dot overview-status-dot--' + row.tone"></span><span>{{ row.label }}</span><b>{{ row.value }}</b><small>{{ percentage(row.value) }}%</small></div></div>
                </article>

                <article class="overview-panel overview-panel--activity">
                    <div class="overview-panel__heading"><div><span class="overview-panel__eyebrow">Priority queue</span><h3>Needs attention</h3></div><Link v-if="attentionCount" :href="route('incidents.index')" class="overview-text-link">View incidents →</Link></div>
                    <div v-if="attentionHosts.length" class="overview-activity-list"><Link v-for="host in attentionHosts" :key="host.id" :href="route('hosts.show', host.id)" class="overview-activity"><span :class="'overview-activity__signal overview-activity__signal--' + statusFor(host).tone">{{ statusFor(host).icon }}</span><span class="overview-activity__copy"><b>{{ host.name }}</b><small>{{ host.ip_address }} · {{ displayLatency(host) }}</small></span><span class="overview-activity__time">{{ lastCheck(host) }}</span></Link></div>
                    <div v-else class="overview-empty-state"><span class="overview-empty-state__check">✓</span><div><b>No branches need attention</b><p>All monitored branches are reporting normally.</p></div></div>
                </article>
            </section>

            <section id="branches" class="overview-panel overview-branches">
                <div class="overview-panel__heading overview-branches__heading">
                    <div><span class="overview-panel__eyebrow">Branch fleet</span><h3>Live branch status</h3><p>Automatically refreshed every 30 seconds.</p></div>
                    <div class="overview-filter" aria-label="Filter branch status"><button v-for="item in [{ key: 'all', label: 'All' }, { key: 'active', label: 'Online' }, { key: 'unstable', label: 'Unstable' }, { key: 'down', label: 'Offline' }]" :key="item.key" type="button" :class="{ 'is-active': filter === item.key }" @click="filter = item.key">{{ item.label }}</button></div>
                </div>
                <div v-if="visibleHosts.length" class="overview-host-list"><Link v-for="host in visibleHosts" :key="host.id" :href="route('hosts.show', host.id)" :class="'overview-host overview-host--' + (host.current_status || 'unknown')"><span :class="'overview-host__status overview-host__status--' + statusFor(host).tone"><i></i>{{ statusFor(host).label }}</span><div class="overview-host__identity"><b>{{ host.name }}</b><span>{{ host.ip_address }}</span></div><div class="overview-host__metric"><small>Latency</small><b>{{ displayLatency(host) }}</b></div><div class="overview-host__metric"><small>Last check</small><b>{{ lastCheck(host) }}</b></div><span class="overview-host__arrow">→</span></Link></div>
                <div v-else class="overview-empty-branches"><span>⌁</span><h4>{{ total ? "No branches match this filter" : "Start by adding a branch" }}</h4><p>{{ total ? "Choose another status filter to see more branches." : "BeePing will check availability and latency from this dashboard." }}</p><Link v-if="!total && page.props.auth?.user?.role === 'admin'" :href="route('hosts.create')" class="overview-button overview-button--primary">Add branch</Link></div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
