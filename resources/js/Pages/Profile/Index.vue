<template>
  <Head title="Profil" />
  <UserLayout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 class="text-3xl font-bold text-text mb-8">Profil Saya</h1>
      <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-4">Informasi Profil</h2>
          <form @submit.prevent="submitProfile" class="space-y-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama</label><input v-model="profileForm.name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input v-model="profileForm.email" type="email" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label><input v-model="profileForm.phone" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
            <button type="submit" :disabled="profileForm.processing" class="px-6 py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50">Simpan</button>
          </form>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-4">Ubah Password</h2>
          <form @submit.prevent="submitPassword" class="space-y-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label><input v-model="passwordForm.current_password" type="password" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"><p v-if="passwordForm.errors.current_password" class="text-danger text-sm mt-1">{{ passwordForm.errors.current_password }}</p></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label><input v-model="passwordForm.password" type="password" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label><input v-model="passwordForm.password_confirmation" type="password" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
            <button type="submit" :disabled="passwordForm.processing" class="px-6 py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50">Ubah Password</button>
          </form>
        </div>
      </div>
    </div>
  </UserLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
const props = defineProps({ user: Object });
const profileForm = useForm({ name: props.user.name, email: props.user.email, phone: props.user.phone || '' });
const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });
function submitProfile() { profileForm.put('/profil'); }
function submitPassword() { passwordForm.put('/profil/password', { onSuccess: () => passwordForm.reset() }); }
</script>
