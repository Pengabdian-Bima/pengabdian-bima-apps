<template>
  <Head title="Masuk" />
  <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary-dark rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl shadow-primary/30">
          <Icon icon="mdi:store" class="text-white text-3xl" />
        </div>
        <h1 class="text-2xl font-bold text-text">Masuk ke UD Flamboyan</h1>
        <p class="text-gray-500 mt-1">Selamat datang kembali!</p>
      </div>

      <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
            <div class="relative">
              <Icon icon="mdi:email-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg" />
              <input v-model="form.email" type="email" required
                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="email@contoh.com">
            </div>
            <p v-if="form.errors.email" class="text-danger text-sm mt-1">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <div class="relative">
              <Icon icon="mdi:lock-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg" />
              <input v-model="form.password" :type="showPass ? 'text' : 'password'" required
                class="w-full pl-10 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="••••••••">
              <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <Icon :icon="showPass ? 'mdi:eye-off' : 'mdi:eye'" class="text-lg" />
              </button>
            </div>
          </div>

          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="form.remember" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
            <span class="text-sm text-gray-600">Ingat saya</span>
          </label>

          <button type="submit" :disabled="form.processing"
            class="w-full py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50">
            <span v-if="form.processing" class="flex items-center justify-center gap-2">
              <Icon icon="mdi:loading" class="animate-spin text-xl" /> Memproses...
            </span>
            <span v-else>Masuk</span>
          </button>
        </form>
      </div>

      <p class="text-center text-sm text-gray-500 mt-6">
        Belum punya akun?
        <Link href="/register" class="text-primary font-semibold hover:underline">Daftar sekarang</Link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const showPass = ref(false);
const form = useForm({ email: '', password: '', remember: false });

function submit() {
  form.post('/login', { preserveScroll: true });
}
</script>
