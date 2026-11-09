<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';

const props = withDefaults(
    defineProps<{
        value: number;
        duration?: number;
    }>(),
    {
        duration: 1200,
    }
);

const displayValue = ref(0);

const animateNumber = (target: number) => {
    const startTime = performance.now();
    const startValue = displayValue.value;
    const change = target - startValue;

    if (change === 0) {
        displayValue.value = target;
        return;
    }

    const step = (currentTime: number) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / props.duration, 1);
        
        // Easing: easeOutExpo
        const easeOut = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        
        displayValue.value = Math.round(startValue + change * easeOut);

        if (progress < 1) {
            requestAnimationFrame(step);
        } else {
            displayValue.value = target;
        }
    };

    requestAnimationFrame(step);
};

onMounted(() => {
    animateNumber(props.value || 0);
});

watch(
    () => props.value,
    (newVal) => {
        animateNumber(newVal || 0);
    }
);
</script>

<template>
    <span>{{ displayValue }}</span>
</template>

