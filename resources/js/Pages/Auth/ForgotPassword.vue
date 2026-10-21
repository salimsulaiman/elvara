<template>
    <div class="min-h-screen bg-white text-ink-dark flex items-center justify-center p-6 font-sans">
        <div class="max-w-md w-full border border-border-soft rounded-xl p-8 space-y-6">
            <!-- Brand title -->
            <div class="text-center">
                <Link href="/" class="text-2xl font-bold tracking-widest text-ink-dark">ELVARA</Link>
                <p class="text-xs text-gray-dark mt-2">Lupa Kata Sandi Anda?</p>
            </div>

            <!-- Validation Error Notice -->
            <div v-if="Object.keys(errors).length" class="bg-gray-light border border-border-soft p-3 rounded-md">
                <p v-for="(err, idx) in errors" :key="idx" class="text-xs text-ink-dark font-medium">{{ err }}</p>
            </div>

            <div class="bg-gray-light text-ink-dark p-4 rounded-lg text-xs leading-relaxed space-y-2 border border-border-soft">
                <p class="font-semibold">Informasi Simulasi OTP:</p>
                <p>
                    Karena ini merupakan server lokal pengembangan, sistem **tidak** mengirim email sungguhan. Kode OTP 6-digit akan dicatat langsung pada berkas **`storage/logs/laravel.log`** di folder project Anda setelah Anda menekan tombol kirim di bawah.
                </p>
            </div>

            <!-- Forgot Password Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-semibold text-ink-dark">Alamat Email Terdaftar</label>
                    <input 
                        type="email" 
                        id="email" 
                        v-model="form.email" 
                        required 
                        placeholder="nama@email.com"
                        class="input-field"
                    />
                </div>

                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full btn-primary mt-2"
                >
                    {{ form.processing ? 'Mengirim...' : 'Kirim Kode OTP' }}
                </button>
            </form>

            <div class="text-center pt-2">
                <p class="text-xs text-gray-dark">
                    Ingat kata sandi Anda? 
                    <Link href="/login" class="font-bold text-ink-dark hover:underline">Masuk kembali</Link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';

defineProps({
    errors: {
        type: Object,
        default: () => ({})
    }
});

const form = reactive({
    email: '',
    processing: false
});

const submit = () => {
    form.processing = true;
    router.post('/forgot-password', {
        email: form.email
    }, {
        onFinish: () => {
            form.processing = false;
        }
    });
};
</script>
