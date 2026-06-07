<template>
  <Head title="Profil Admin" />
  <AdminLayout>
    <div>
      <h1 class="text-2xl font-bold text-text mb-6">Profil Saya</h1>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Info -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
            <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary-dark rounded-2xl flex items-center justify-center shadow-lg shadow-primary/20">
              <span class="text-white text-2xl font-bold">{{ user.name?.charAt(0) }}</span>
            </div>
            <div>
              <h2 class="text-lg font-semibold text-text">{{ user.name }}</h2>
              <p class="text-sm text-gray-500">{{ user.email }}</p>
              <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-0.5 bg-primary/10 text-primary text-xs font-medium rounded-full">
                <Icon icon="mdi:shield-crown" class="text-sm" /> Administrator
              </span>
            </div>
          </div>

          <h3 class="font-semibold text-text mb-4 flex items-center gap-2">
            <Icon icon="mdi:account-edit" class="text-primary text-lg" /> Informasi Profil
          </h3>
          <form @submit.prevent="submitProfile" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
              <input v-model="profileForm.name" required
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
              <p v-if="profileForm.errors.name" class="text-danger text-sm mt-1">{{ profileForm.errors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="profileForm.email" type="email" required
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
              <p v-if="profileForm.errors.email" class="text-danger text-sm mt-1">{{ profileForm.errors.email }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
              <input v-model="profileForm.phone"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                placeholder="08xxxxxxxxxx">
            </div>
            <button type="submit" :disabled="profileForm.processing"
              class="px-6 py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50 flex items-center gap-2">
              <Icon v-if="profileForm.processing" icon="mdi:loading" class="animate-spin" />
              <Icon v-else icon="mdi:content-save" />
              {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Profil' }}
            </button>
          </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h3 class="font-semibold text-text mb-4 flex items-center gap-2">
            <Icon icon="mdi:lock-reset" class="text-primary text-lg" /> Ubah Password
          </h3>
          <form @submit.prevent="submitPassword" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
              <div class="relative">
                <input v-model="passwordForm.current_password" :type="showCurrent ? 'text' : 'password'" required
                  class="w-full px-4 py-2.5 pr-12 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                <button type="button" @click="showCurrent = !showCurrent" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <Icon :icon="showCurrent ? 'mdi:eye-off' : 'mdi:eye'" />
                </button>
              </div>
              <p v-if="passwordForm.errors.current_password" class="text-danger text-sm mt-1">{{ passwordForm.errors.current_password }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
              <div class="relative">
                <input v-model="passwordForm.password" :type="showNew ? 'text' : 'password'" required
                  class="w-full px-4 py-2.5 pr-12 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                  placeholder="Minimal 8 karakter">
                <button type="button" @click="showNew = !showNew" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <Icon :icon="showNew ? 'mdi:eye-off' : 'mdi:eye'" />
                </button>
              </div>
              <p v-if="passwordForm.errors.password" class="text-danger text-sm mt-1">{{ passwordForm.errors.password }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
              <input v-model="passwordForm.password_confirmation" :type="showNew ? 'text' : 'password'" required
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                placeholder="Ulangi password baru">
            </div>
            <button type="submit" :disabled="passwordForm.processing"
              class="px-6 py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50 flex items-center gap-2">
              <Icon v-if="passwordForm.processing" icon="mdi:loading" class="animate-spin" />
              <Icon v-else icon="mdi:lock-check" />
              {{ passwordForm.processing ? 'Memperbarui...' : 'Ubah Password' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ user: Object });

const showCurrent = ref(false);
const showNew = ref(false);

const profileForm = useForm({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone || '',
});

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

function submitProfile() {
  profileForm.put('/admin/profile');
}

function submitPassword() {
  passwordForm.put('/admin/profile/password', {
    onSuccess: () => passwordForm.reset(),
  });
}
</script>
