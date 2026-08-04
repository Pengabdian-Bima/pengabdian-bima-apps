<template>
  <Head title="Daftar" />
  <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50 flex items-center justify-center p-4">
    <div class="w-full max-w-4xl">
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary-dark rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl shadow-primary/30">
          <Icon icon="mdi:account-plus" class="text-white text-3xl" />
        </div>
        <h1 class="text-2xl font-bold text-text">Buat Akun Baru</h1>
        <p class="text-gray-500 mt-1">Bergabung dengan UD Flamboyan & lengkapi alamat Anda</p>
      </div>

      <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
        
        <!-- Global Validation Error Alert Banner -->
        <div v-if="Object.keys(form.errors).length > 0" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium flex items-start gap-3">
          <Icon icon="mdi:alert-circle" class="text-xl shrink-0 mt-0.5 text-danger" />
          <div>
            <p class="font-bold mb-1">Gagal mendaftar. Harap periksa kembali input berikut:</p>
            <ul class="list-disc list-inside space-y-1">
              <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
            </ul>
          </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-8">
          
          <!-- Column 1: Account Info -->
          <div class="space-y-4">
            <h2 class="font-bold text-lg text-primary border-b border-gray-100 pb-2 mb-2 flex items-center gap-2">
              <Icon icon="mdi:account-circle-outline" /> Informasi Akun
            </h2>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
              <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Nama lengkap">
              <p v-if="form.errors.name" class="text-danger text-sm mt-1">{{ form.errors.name }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
              <input v-model="form.email" type="email" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="email@contoh.com">
              <p v-if="form.errors.email" class="text-danger text-sm mt-1">{{ form.errors.email }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. HP *</label>
              <input v-model="form.phone" type="text" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="08xxxxxxxxxx">
              <p v-if="form.errors.phone" class="text-danger text-sm mt-1">{{ form.errors.phone }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
              <div class="relative">
                <input v-model="form.password" :type="showPass ? 'text' : 'password'" required class="w-full px-4 pr-12 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Buat password (min. 8 karakter)">
                <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <Icon :icon="showPass ? 'mdi:eye-off' : 'mdi:eye'" class="text-lg" />
                </button>
              </div>
              <p v-if="form.errors.password" class="text-danger text-sm mt-1">{{ form.errors.password }}</p>
              
              <div v-if="form.password.length > 0" class="mt-2 space-y-1">
                <div class="flex items-center gap-2 text-xs">
                  <Icon :icon="rules.minLength ? 'mdi:check-circle' : 'mdi:close-circle'" :class="rules.minLength ? 'text-success' : 'text-danger'" />
                  <span :class="rules.minLength ? 'text-success' : 'text-danger'">Minimal 8 karakter</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                  <Icon :icon="rules.hasUppercase ? 'mdi:check-circle' : 'mdi:close-circle'" :class="rules.hasUppercase ? 'text-success' : 'text-gray-400'" />
                  <span :class="rules.hasUppercase ? 'text-success' : 'text-gray-500'">Menggunakan huruf besar (opsional)</span>
                </div>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password *</label>
              <div class="relative">
                <input v-model="form.password_confirmation" :type="showConfirm ? 'text' : 'password'" required class="w-full px-4 pr-12 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Ulangi password">
                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <Icon :icon="showConfirm ? 'mdi:eye-off' : 'mdi:eye'" class="text-lg" />
                </button>
              </div>
              
              <div v-if="form.password_confirmation.length > 0" class="mt-2">
                <div class="flex items-center gap-2 text-xs">
                  <Icon :icon="passwordsMatch ? 'mdi:check-circle' : 'mdi:close-circle'" :class="passwordsMatch ? 'text-success' : 'text-danger'" />
                  <span :class="passwordsMatch ? 'text-success' : 'text-danger'">{{ passwordsMatch ? 'Password cocok' : 'Password tidak cocok' }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Column 2: Address Info -->
          <div class="space-y-4">
            <h2 class="font-bold text-lg text-primary border-b border-gray-100 pb-2 mb-2 flex items-center gap-2">
              <Icon icon="mdi:map-marker-outline" /> Alamat Pengiriman Utama
            </h2>

            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Label Alamat (Contoh: Rumah, Kantor) *</label>
                <input v-model="form.label" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Rumah, Kantor, Toko, dsb.">
                <p v-if="form.errors.label" class="text-danger text-sm mt-1">{{ form.errors.label }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                <input v-model="form.recipient_name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" :placeholder="form.name || 'Nama penerima'">
                <p v-if="form.errors.recipient_name" class="text-danger text-sm mt-1">{{ form.errors.recipient_name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Penerima</label>
                <input v-model="form.address_phone" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" :placeholder="form.phone || '08xxxxxxxxxx'">
                <p v-if="form.errors.address_phone" class="text-danger text-sm mt-1">{{ form.errors.address_phone }}</p>
              </div>

              <!-- RajaOngkir Autocomplete Location Search -->
              <div class="col-span-2 relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Wilayah (Provinsi, Kota, Kecamatan, Kode Pos)</label>
                <div class="relative">
                  <input 
                    type="text" 
                    v-model="addressSearchQuery" 
                    @input="searchAddress" 
                    @focus="showDropdown = true"
                    @blur="handleBlur"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" 
                    placeholder="Ketik wilayah (misal: Limboto, Gorontalo)..."
                    autocomplete="off"
                  >
                  <!-- Clear button -->
                  <button 
                    v-if="addressSearchQuery" 
                    type="button" 
                    @click="clearAddressSelection" 
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                  >
                    <Icon icon="mdi:close-circle" class="text-lg" />
                  </button>
                </div>
                
                <!-- Autocomplete Dropdown List -->
                <div v-if="showDropdown && searchResults.length > 0" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                  <div 
                    v-for="(result, index) in searchResults" 
                    :key="index" 
                    @mousedown="selectLocation(result)"
                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0"
                  >
                    {{ result.label }}
                  </div>
                </div>
                <div v-else-if="showDropdown && searching" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-4 text-center text-sm text-gray-500">
                  <span class="flex items-center justify-center gap-2 text-xs">
                    <Icon icon="mdi:loading" class="animate-spin text-primary" /> Mencari wilayah...
                  </span>
                </div>
              </div>

              <!-- Selected Address Breakdown (Read Only for verification) -->
              <div v-if="form.city" class="col-span-2 grid grid-cols-2 gap-3 p-3.5 bg-primary/5 rounded-xl border border-primary/10 text-xs text-gray-600">
                <div><span class="font-bold text-gray-500">Provinsi:</span> {{ form.province }}</div>
                <div><span class="font-bold text-gray-500">Kota/Kab:</span> {{ form.city }}</div>
                <div><span class="font-bold text-gray-500">Kecamatan:</span> {{ form.district }}</div>
                <div><span class="font-bold text-gray-500">Desa/Kelurahan:</span> {{ form.village }}</div>
                <div class="col-span-2"><span class="font-bold text-gray-500">Kode Pos:</span> {{ form.postal_code }}</div>
              </div>

              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap (Jalan, No. Rumah, RT/RW) *</label>
                <textarea v-model="form.address" required rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none" placeholder="Nama Jalan, No. Rumah, RT/RW, dsb."></textarea>
                <p v-if="form.errors.address" class="text-danger text-sm mt-1">{{ form.errors.address }}</p>
              </div>
            </div>
          </div>

          <!-- Submit Button spanning both columns -->
          <div class="col-span-1 md:col-span-2 pt-4 border-t border-gray-100 flex flex-col items-center">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full max-w-md py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <Icon v-if="form.processing" icon="mdi:loading" class="animate-spin text-xl" />
              <span v-if="form.processing">Memproses Registrasi...</span>
              <span v-else>Daftar Sekarang</span>
            </button>
            <p class="text-center text-sm text-gray-500 mt-4">Sudah punya akun? <Link href="/login" class="text-primary font-semibold hover:underline">Masuk</Link></p>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import axios from 'axios';

const showPass = ref(false);
const showConfirm = ref(false);

const addressSearchQuery = ref('');
const searchResults = ref([]);
const searching = ref(false);
const showDropdown = ref(false);
let searchTimeout = null;

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  
  // Address fields
  label: 'Rumah',
  recipient_name: '',
  address_phone: '',
  address: '',
  province: '',
  city: '',
  city_id: '',
  district: '',
  village: '',
  postal_code: '',
});

// Auto-populate recipient info as helper
watch(() => form.name, (newVal) => {
  if (!form.recipient_name) {
    form.recipient_name = newVal;
  }
});

watch(() => form.phone, (newVal) => {
  if (!form.address_phone) {
    form.address_phone = newVal;
  }
});

function searchAddress() {
  if (searchTimeout) clearTimeout(searchTimeout);
  
  if (addressSearchQuery.value.length < 2) {
    searchResults.value = [];
    return;
  }
  
  searching.value = true;
  showDropdown.value = true;
  
  searchTimeout = setTimeout(async () => {
    try {
      const response = await axios.get('/api/locations/search', {
        params: { q: addressSearchQuery.value }
      });
      searchResults.value = response.data;
    } catch (e) {
      console.error("Gagal mencari alamat:", e);
    } finally {
      searching.value = false;
    }
  }, 300);
}

function selectLocation(result) {
  form.province = result.province || '';
  form.city = result.city || '';
  form.city_id = result.city_id ? String(result.city_id) : '';
  form.district = result.district || '';
  form.village = result.village || '';
  form.postal_code = result.postal_code || '';
  
  addressSearchQuery.value = result.label;
  showDropdown.value = false;
}

function clearAddressSelection() {
  addressSearchQuery.value = '';
  form.province = '';
  form.city = '';
  form.city_id = '';
  form.district = '';
  form.village = '';
  form.postal_code = '';
  searchResults.value = [];
}

function handleBlur() {
  setTimeout(() => {
    showDropdown.value = false;
  }, 250);
}

const rules = computed(() => ({
  minLength: form.password.length >= 8,
  hasUppercase: /[A-Z]/.test(form.password),
}));

const passwordsMatch = computed(() => form.password === form.password_confirmation && form.password_confirmation.length > 0);

function submit() {
  if (!form.recipient_name) {
    form.recipient_name = form.name;
  }
  if (!form.address_phone) {
    form.address_phone = form.phone;
  }
  if (!form.label) {
    form.label = 'Rumah';
  }

  form.post('/register', {
    preserveScroll: true,
    onError: (errors) => {
      console.error("Registrasi gagal:", errors);
    }
  });
}
</script>
