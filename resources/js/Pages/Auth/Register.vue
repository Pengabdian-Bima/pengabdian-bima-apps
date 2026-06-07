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
            <div class="relative">
              <input v-model="form.password" :type="showPass ? 'text' : 'password'" required class="w-full px-4 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Buat password">
              <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <Icon :icon="showPass ? 'mdi:eye-off' : 'mdi:eye'" class="text-lg" />
              </button>
            </div>
            <p v-if="form.errors.password" class="text-danger text-sm mt-1">{{ form.errors.password }}</p>
            <!-- Real-time password validation -->
            <div v-if="form.password.length > 0" class="mt-2 space-y-1">
              <div class="flex items-center gap-2 text-xs">
                <Icon :icon="rules.minLength ? 'mdi:check-circle' : 'mdi:close-circle'" :class="rules.minLength ? 'text-success' : 'text-danger'" />
                <span :class="rules.minLength ? 'text-success' : 'text-danger'">Minimal 8 karakter</span>
              </div>
              <div class="flex items-center gap-2 text-xs">
                <Icon :icon="rules.hasUppercase ? 'mdi:check-circle' : 'mdi:close-circle'" :class="rules.hasUppercase ? 'text-success' : 'text-danger'" />
                <span :class="rules.hasUppercase ? 'text-success' : 'text-danger'">Minimal 1 huruf besar</span>
              </div>
              <div class="flex items-center gap-2 text-xs">
                <Icon :icon="rules.noSpaces ? 'mdi:check-circle' : 'mdi:close-circle'" :class="rules.noSpaces ? 'text-success' : 'text-danger'" />
                <span :class="rules.noSpaces ? 'text-success' : 'text-danger'">Tidak boleh ada spasi</span>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password</label>
            <div class="relative">
              <input v-model="form.password_confirmation" :type="showConfirm ? 'text' : 'password'" required class="w-full px-4 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Ulangi password">
              <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <Icon :icon="showConfirm ? 'mdi:eye-off' : 'mdi:eye'" class="text-lg" />
              </button>
            </div>
            <!-- Confirmation match check -->
            <div v-if="form.password_confirmation.length > 0" class="mt-2">
              <div class="flex items-center gap-2 text-xs">
                <Icon :icon="passwordsMatch ? 'mdi:check-circle' : 'mdi:close-circle'" :class="passwordsMatch ? 'text-success' : 'text-danger'" />
                <span :class="passwordsMatch ? 'text-success' : 'text-danger'">{{ passwordsMatch ? 'Password cocok' : 'Password tidak cocok' }}</span>
              </div>
            </div>
          </div>
          <button type="submit" :disabled="form.processing || !canSubmit"
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
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const showPass = ref(false);
const showConfirm = ref(false);
const form = useForm({ name: '', email: '', phone: '', password: '', password_confirmation: '' });

const rules = computed(() => ({
  minLength: form.password.length >= 8,
  hasUppercase: /[A-Z]/.test(form.password),
  noSpaces: !/\s/.test(form.password),
}));

const isPasswordValid = computed(() => rules.value.minLength && rules.value.hasUppercase && rules.value.noSpaces);
const passwordsMatch = computed(() => form.password === form.password_confirmation && form.password_confirmation.length > 0);
const canSubmit = computed(() => isPasswordValid.value && passwordsMatch.value);

function submit() {
  if (!canSubmit.value) return;
  form.post('/register');
}
</script>
