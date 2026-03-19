<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
         <div class="min-h-screen bg-base-200 text-base-content">
            <div class="container mx-auto px-4 py-8">
            <slot />
        </div>
    </div>
    </AppLayout>
</template>
