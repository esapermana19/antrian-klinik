<script setup lang="ts">
import { AnimatePresence, motion } from 'motion-v';
import { X } from 'lucide-vue-next';

interface Props {
    show: boolean;
    maxWidth?: string;
}

const props = withDefaults(defineProps<Props>(), {
    maxWidth: 'max-w-md',
});

const emit = defineEmits<{
    (e: 'close'): void;
}>();

// Physics Spring bounce configuration
const springTransition = {
    type: 'spring',
    stiffness: 350,
    damping: 20,
    mass: 0.8,
};

const overlayTransition = {
    duration: 0.2,
    ease: [0.16, 1, 0.3, 1],
};
</script>

<template>
    <Teleport to="body">
        <AnimatePresence>
            <div
                v-if="props.show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
            >
                <!-- Backdrop Overlay (Fade-in / Fade-out) -->
                <motion.div
                    key="motion-modal-backdrop"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :exit="{ opacity: 0 }"
                    :transition="overlayTransition"
                    class="fixed inset-0 bg-slate-950/45 backdrop-blur-2xs cursor-pointer z-40"
                    @click="emit('close')"
                />

                <!-- Modal Content Card with Spring Bounce -->
                <motion.div
                    key="motion-modal-card"
                    :initial="{ opacity: 0, scale: 0.8, y: 16 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :exit="{ opacity: 0, scale: 0.92, y: 10, transition: { duration: 0.16, ease: 'easeIn' } }"
                    :transition="springTransition"
                    class="pointer-events-auto relative z-50 w-full overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-2xl my-auto"
                    :class="props.maxWidth"
                >
                    <slot />
                </motion.div>
            </div>
        </AnimatePresence>
    </Teleport>
</template>

