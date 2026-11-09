<script setup lang="ts">
import SmoothDashboardLayout from '@/components/SmoothDashboardLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <SmoothDashboardLayout>
        <slot />
    </SmoothDashboardLayout>
</template>
