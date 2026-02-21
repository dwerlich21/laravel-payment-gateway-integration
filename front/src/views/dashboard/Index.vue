<script setup>
import Layout from '@/components/layouts/main.vue';
import PageHeader from '@/components/layouts/page-header.vue';
import MetricsCards from '@/components/dashboard/MetricsCards.vue';
import StatusChart from '@/components/dashboard/StatusChart.vue';
import UserChart from '@/components/dashboard/UserChart.vue';
import TimelineChart from '@/components/dashboard/TimelineChart.vue';
import UpcomingDeadlines from '@/components/dashboard/UpcomingDeadlines.vue';
import TopPerformers from '@/components/dashboard/TopPerformers.vue';
import RecentActivity from '@/components/dashboard/RecentActivity.vue';

const title = 'Dashboard';
</script>

<template>
    <Layout>
        <PageHeader :title="title" />

    </Layout>
</template>
