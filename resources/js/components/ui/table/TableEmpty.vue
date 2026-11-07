<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
  colspan?: number
}>()
</script>

<template>
  <tr>
    <td
      :class="
        cn(
          'p-8 text-center align-middle text-sm text-slate-400',
          props.class,
        )
      "
      :colspan="props.colspan"
    >
      <slot />
    </td>
  </tr>
</template>

