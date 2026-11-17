<script setup lang="ts">
import SpringModal from '@/components/SpringModal.vue';
import { Button } from '@/components/ui/button';
import { AlertTriangle } from 'lucide-vue-next';

defineProps<{
    open: boolean;
    title?: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    processing?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', val: boolean): void;
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();

const handleCancel = () => {
    emit('update:open', false);
    emit('cancel');
};

const handleConfirm = () => {
    emit('confirm');
};
</script>

<template>
    <SpringModal
        :open="open"
        @update:open="emit('update:open', $event)"
        :title="title || 'Konfirmasi Hapus Data'"
        :description="
            description ||
            'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.'
        "
    >
        <div
            class="my-1 flex items-start gap-3 rounded-lg border border-rose-100/80 bg-rose-50/60 p-3 py-2 text-xs text-slate-600"
        >
            <AlertTriangle class="mt-0.5 size-4.5 shrink-0 text-rose-600" />
            <p class="leading-relaxed">
                <slot>
                    Data yang telah dihapus akan hilang dari database sistem dan
                    tidak dapat dipulihkan kembali.
                </slot>
            </p>
        </div>

        <template #footer>
            <Button
                type="button"
                variant="outline"
                size="sm"
                class="h-8 border-slate-200 text-xs"
                :disabled="processing"
                @click="handleCancel"
            >
                {{ cancelText || 'Batal' }}
            </Button>
            <Button
                type="button"
                variant="destructive"
                size="sm"
                class="h-8 bg-rose-600 text-xs font-medium text-white hover:bg-rose-700"
                :disabled="processing"
                @click="handleConfirm"
            >
                {{
                    processing
                        ? 'Menghapus...'
                        : confirmText || 'Hapus Sekarang'
                }}
            </Button>
        </template>
    </SpringModal>
</template>
