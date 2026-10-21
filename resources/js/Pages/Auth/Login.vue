<template>
    <div class="min-h-screen bg-white text-ink-dark flex items-center justify-center p-6 font-sans">
        <div class="max-w-md w-full border border-border-soft rounded-xl p-8 space-y-6">
            <!-- Brand title -->
            <div class="text-center">
                <Link href="/" class="text-2xl font-bold tracking-widest text-ink-dark">ELVARA</Link>
                <p class="text-xs text-gray-dark mt-2">Masuk untuk melanjutkan belanja Anda</p>
            </div>

            <!-- Validation Error Notice -->
            <div v-if="Object.keys(errors).length" class="bg-gray-light border border-border-soft p-3 rounded-md">
                <p v-for="(err, idx) in errors" :key="idx" class="text-xs text-ink-dark font-medium">{{ err }}</p>
            </div>

            <!-- Login Form -->
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
                    <div class="flex justify-between items-center">
                        <label for="password" class="text-xs font-semibold text-ink-dark">Kata Sandi</label>
                        <Link href="/forgot-password" class="text-[11px] text-gray-dark hover:underline">Lupa password?</Link>
                    </div>
                    <input 
                        type="password" 
                        id="password" 
                        v-model="form.password" 
                        required 
                        placeholder="••••••••"
                        class="input-field"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        v-model="form.remember" 
                        class="rounded border-border-soft text-ink-dark focus:ring-0 w-4 h-4 cursor-pointer"
                    />
                    <label for="remember" class="text-xs text-gray-dark select-none cursor-pointer">Ingat saya di perangkat ini</label>
                </div>

                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full btn-primary mt-2"
                >
                    {{ form.processing ? 'Memproses...' : 'Masuk' }}
                </button>
            </form>

            <div class="relative flex py-2 items-center">
                <div class="flex-grow border-t border-border-soft"></div>
                <span class="flex-shrink mx-4 text-[10px] text-gray-dark uppercase tracking-wider">Atau</span>
                <div class="flex-grow border-t border-border-soft"></div>
            </div>

            <!-- Google OAuth Simulation Button -->
            <a 
                href="/auth/google/simulate"
                class="w-full btn-outline flex items-center justify-center gap-2"
            >
                <!-- SVG Google Icon -->
                <svg class="w-4 h-4 text-ink-dark" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12.24 10.285V14.4h6.887c-.648 2.41-2.519 4.114-5.136 4.114A5.727 5.727 0 018.2 12.8a5.727 5.727 0 015.79-5.714c1.554 0 2.946.59 4.008 1.55l3.116-3.085C19.23 3.793 16.786 2.5 13.99 2.5a10.212 10.212 0 00-10.28 10.3c0 5.69 4.606 10.3 10.28 10.3 5.913 0 9.83-4.114 9.83-10.011 0-.675-.06-1.32-.176-1.804H12.24z"/>
                </svg>
                <span>Masuk dengan Google (Simulasi)</span>
            </a>

            <div class="text-center pt-2">
                <p class="text-xs text-gray-dark">
                    Belum punya akun? 
                    <Link href="/register" class="font-bold text-ink-dark hover:underline">Daftar sekarang</Link>
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
    password: '',
    remember: false,
    processing: false
});

const submit = () => {
    form.processing = true;
    router.post('/login', {
        email: form.email,
        password: form.password,
        remember: form.remember
    }, {
        onFinish: () => {
            form.processing = false;
        }
    });
};
</script>
