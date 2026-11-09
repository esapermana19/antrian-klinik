<script setup lang="ts">
import { AnimatePresence, motion } from 'motion-v';
import {
    Dialog,
    DialogClose,
    MotionDialogContent,
} from '@/components/ui/dialog';
import { X } from 'lucide-vue-next';

interface Props {
    open: boolean;
    title?: string;
    description?: string;
    maxWidth?: string;
}

const props = withDefaults(defineProps<Props>(), {
    maxWidth: 'max-w-lg',
});

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

// Physics Spring bounce configuration
const springTransition = {
    type: 'spring',
    stiffness: 350,
    damping: 20,
    mass: 0.8,
};

const overlayTransition = {
    duration: 0.22,
    ease: [0.16, 1, 0.3, 1],
};
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <MotionDialogContent :force-mount="true" class="p-0 border-0 bg-transparent shadow-none">
            <AnimatePresence>
                <!-- Backdrop Overlay (Fade-in / Fade-out) -->
                <motion.div
                    v-if="props.open"
                    key="spring-modal-backdrop"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :exit="{ opacity: 0 }"
                    :transition="overlayTransition"
                    class="fixed inset-0 bg-slate-950/45 backdrop-blur-xs pointer-events-auto z-40"
                    @click="emit('update:open', false)"
                />

                <!-- Modal Content Card with Spring Bounce -->
                <motion.div
                    v-if="props.open"
                    key="spring-modal-card"
                    :initial="{ opacity: 0, scale: 0.8, y: 16 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :exit="{ opacity: 0, scale: 0.92, y: 10, transition: { duration: 0.18, ease: 'easeIn' } }"
                    :transition="springTransition"
                    class="pointer-events-auto relative z-50 w-full overflow-hidden rounded-xl border border-slate-200/90 bg-white p-6 shadow-2xl mx-4"
                    :class="props.maxWidth"
                >
                    <!-- Close button -->
                    <button
                        type="button"
                        @click="emit('update:open', false)"
                        class="absolute right-4 top-4 rounded-md p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        <X class="size-4" />
                        <span class="sr-only">Tutup</span>
                    </button>

                    <!-- Header -->
                    <div v-if="props.title || props.description" class="mb-4 space-y-1 pr-6">
                        <h2 v-if="props.title" class="text-base font-bold text-slate-900 tracking-tight">
                            {{ props.title }}
                        </h2>
                        <p v-if="props.description" class="text-xs text-slate-500 leading-relaxed">
                            {{ props.description }}
                        </p>
                    </div>

                    <!-- Main Slot -->
                    <div>
                        <slot />
                    </div>

                    <!-- Footer Slot -->
                    <div v-if="$slots.footer" class="mt-6 flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <slot name="footer" />
                    </div>
                </motion.div>
            </AnimatePresence>
        </MotionDialogContent>
    </Dialog>
</template>
