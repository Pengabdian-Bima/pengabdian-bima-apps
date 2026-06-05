<template>
  <Head title="Daftar" />
  <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary-dark rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl shadow-primary/30">
          <Icon icon="mdi:account-plus" class="text-white text-3xl" />
        </div>
        <h1 class="text-2xl font-bold text-text">Buat Akun Baru</h1>
        <p class="text-gray-500 mt-1">Bergabung dengan UD Flamboyan</p>
      </div>

      <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
            <input v-model="form.name" type="text" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Nama lengkap">
            <p v-if="form.errors.name" class="text-danger text-sm mt-1">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
            <input v-model="form.email" type="email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="email@contoh.com">
            <p v-if="form.errors.email" class="text-danger text-sm mt-1">{{ form.errors.email }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">No. HP</label>
            <input v-model="form.phone" type="text" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="08xxxxxxxxxx">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <input v-model="form.password" type="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Min. 8 karakter">
            <p v-if="form.errors.password" class="text-danger text-sm mt-1">{{ form.errors.password }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password</label>
            <input v-model="form.password_confirmation" type="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Ulangi password">
          </div>
          <button type="submit" :disabled="form.processing"
            class="w-full py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50 mt-2">
            <span v-if="form.processing">Memproses...</span>
            <span v-else>Daftar Sekarang</span>
          </button>
        </form>
      </div>
      <p class="text-center text-sm text-gray-500 mt-6">Sudah punya akun? <Link href="/login" class="text-primary font-semibold hover:underline">Masuk</Link></p>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
const form = useForm({ name: '', email: '', phone: '', password: '', password_confirmation: '' });
function submit() { form.post('/register'); }
</script>
