<script setup lang="ts">
import type { DialogContentEmits, DialogContentProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import {
  DialogContent,
  DialogPortal,
  useForwardPropsEmits,
} from "reka-ui"
import { cn } from "@/lib/utils"

defineOptions({
  inheritAttrs: false,
})

const props = withDefaults(
  defineProps<DialogContentProps & { class?: HTMLAttributes["class"] }>(),
  {
    forceMount: true, // WAJIB untuk Motion AnimatePresence agar exit animation sempat selesai
  }
)
const emits = defineEmits<DialogContentEmits>()

const delegatedProps = reactiveOmit(props, "class")
const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <DialogPortal>
    <!-- Container centering murni flexbox full screen tanpa konflik translate CSS -->
    <DialogContent
      data-slot="motion-dialog-content"
      v-bind="{ ...$attrs, ...forwarded }"
      :class="
        cn(
          'fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none',
          props.class
        )
      "
    >
      <slot />
    </DialogContent>
  </DialogPortal>
</template>

