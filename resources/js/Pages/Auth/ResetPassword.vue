<template>
    <div class="min-h-screen bg-white text-ink-dark flex items-center justify-center p-6 font-sans">
        <div class="max-w-md w-full border border-border-soft rounded-xl p-8 space-y-6">
            <!-- Brand title -->
            <div class="text-center">
                <Link href="/" class="text-2xl font-bold tracking-widest text-ink-dark">ELVARA</Link>
                <p class="text-xs text-gray-dark mt-2">Reset Kata Sandi</p>
            </div>

            <!-- Validation Error Notice -->
            <div v-if="Object.keys(errors).length" class="bg-gray-light border border-border-soft p-3 rounded-md">
                <p v-for="(err, idx) in errors" :key="idx" class="text-xs text-ink-dark font-medium">{{ err }}</p>
            </div>

            <!-- Reset Password Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-semibold text-ink-dark">Alamat Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        v-model="form.email" 
                        required 
                        placeholder="nama@email.com"
                        class="input-field"
                    />
                </div>

                <div class="space-y-1.5">
                    <label for="otp_code" class="text-xs font-semibold text-ink-dark">Kode OTP (6-Digit)</label>
                    <input 
                        type="text" 
                        id="otp_code" 
                        v-model="form.otp_code" 
                        required 
                        maxlength="6"
                        placeholder="Masukkan 6 angka"
                        class="input-field"
                    />
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-semibold text-ink-dark">Kata Sandi Baru</label>
                    <input 
                        type="password" 
                        id="password" 
                        v-model="form.password" 
                        required 
                        placeholder="Minimal 8 karakter"
                        class="input-field"
                    />
                </div>

                <div class="space-y-1.5">
                    <label for="password_confirmation" class="text-xs font-semibold text-ink-dark">Konfirmasi Kata Sandi Baru</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        v-model="form.password_confirmation" 
                        required 
                        placeholder="Ulangi kata sandi baru"
                        class="input-field"
                    />
                </div>

                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full btn-primary mt-2"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Perbarui Kata Sandi' }}
                </button>
            </form>

            <div class="text-center pt-2">
                <p class="text-xs text-gray-dark">
                    Batal mengatur ulang kata sandi? 
                    <Link href="/login" class="font-bold text-ink-dark hover:underline">Masuk kembali</Link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        default: ''
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const form = reactive({
    email: '',
    otp_code: '',
    password: '',
    password_confirmation: '',
    processing: false
});

onMounted(() => {
    form.email = props.email;
});

const submit = () => {
    form.processing = true;
    router.post('/reset-password', {
        email: form.email,
        otp_code: form.otp_code,
        password: form.password,
        password_confirmation: form.password_confirmation
    }, {
        onFinish: () => {
            form.processing = false;
        }
    });
};
</script>
