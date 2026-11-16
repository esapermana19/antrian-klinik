<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Delete account"
            description="Delete your account and all of its resources"
        />
        <div
            class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Warning</p>
                <p class="text-sm">
                    Please proceed with caution, this cannot be undone.
                </p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button"
                        >Delete account</Button
                    >
                </DialogTrigger>
                <DialogContent class="bg-white dark:bg-slate-900 border border-slate-200/90 shadow-2xl rounded-2xl z-50">
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-4"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="space-y-2 text-left">
                            <DialogTitle class="text-base font-bold text-slate-900 dark:text-white"
                                >Hapus Akun Anda?</DialogTitle
                            >
                            <DialogDescription class="text-xs text-slate-500 leading-relaxed">
                                Setelah akun dihapus, seluruh data dan hak akses Anda akan dihapus secara permanen.
                                Masukkan kata sandi untuk mengonfirmasi penghapusan akun.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-1.5 pt-1">
                            <Label for="password" class="text-xs font-medium text-slate-700 dark:text-slate-300"
                                >Kata Sandi Konfirmasi</Label
                            >
                            <PasswordInput
                                id="password"
                                name="password"
                                ref="passwordInput"
                                placeholder="Masukkan kata sandi akun Anda"
                                class="h-9.5 text-xs bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-700"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="flex flex-col-reverse sm:flex-row gap-2 pt-2">
                            <DialogClose as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-9 text-xs border-slate-200 cursor-pointer"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    Batal
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                class="h-9 text-xs bg-rose-600 hover:bg-rose-700 text-white cursor-pointer"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                Ya, Hapus Akun
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
