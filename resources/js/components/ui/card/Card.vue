<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div
    data-slot="card"
    :class="
      cn(
        'bg-white/75 dark:bg-slate-900/75 backdrop-blur-md text-card-foreground flex flex-col gap-6 rounded-xl border border-white/60 dark:border-white/10 shadow-xs shadow-slate-200/50 dark:shadow-none transition-all duration-200',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
