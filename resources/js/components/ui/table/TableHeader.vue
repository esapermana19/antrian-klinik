<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <thead :class="cn('bg-slate-50/75 border-b border-slate-200/80 [&_tr]:border-b-0', props.class)">
    <slot />
  </thead>
</template>

