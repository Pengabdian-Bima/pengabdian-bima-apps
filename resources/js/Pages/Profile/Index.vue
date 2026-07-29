<template>
  <Head title="Profil Saya" />
  <UserLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 class="text-3xl font-bold text-text mb-8">Profil Saya</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Side: Profile Info & Password -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Profile Form -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-semibold text-text mb-4 flex items-center gap-2">
              <Icon icon="mdi:account-edit" class="text-primary text-xl" /> Informasi Profil
            </h2>
            <form @submit.prevent="submitProfile" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input v-model="profileForm.name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="profileForm.email" type="email" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                <input v-model="profileForm.phone" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="08xxxxxxxxxx">
              </div>
              <button type="submit" :disabled="profileForm.processing" class="w-full py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50">
                Simpan Perubahan
              </button>
            </form>
          </div>

          <!-- Password Form -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-semibold text-text mb-4 flex items-center gap-2">
              <Icon icon="mdi:lock-reset" class="text-primary text-xl" /> Ubah Password
            </h2>
            <form @submit.prevent="submitPassword" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                <input v-model="passwordForm.current_password" type="password" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                <p v-if="passwordForm.errors.current_password" class="text-danger text-sm mt-1">{{ passwordForm.errors.current_password }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                <input v-model="passwordForm.password" type="password" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <input v-model="passwordForm.password_confirmation" type="password" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
              </div>
              <button type="submit" :disabled="passwordForm.processing" class="w-full py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50">
                Ubah Password
              </button>
            </form>
          </div>
        </div>

        <!-- Right Side: Address Management (Shopee-like) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
              <h2 class="font-semibold text-lg text-text flex items-center gap-2">
                <Icon icon="mdi:map-marker-multiple-outline" class="text-primary text-2xl" /> Alamat Saya
              </h2>
              <button @click="openAddModal" class="px-4 py-2 bg-primary/10 text-primary hover:bg-primary/20 rounded-xl font-medium text-sm flex items-center gap-1.5 transition-all">
                <Icon icon="mdi:plus" class="text-lg" /> Tambah Alamat Baru
              </button>
            </div>

            <!-- Address List -->
            <div v-if="addresses.length === 0" class="text-center py-12 border border-dashed border-gray-200 rounded-2xl">
              <Icon icon="mdi:map-marker-off-outline" class="text-gray-300 text-5xl mx-auto mb-3" />
              <p class="text-gray-500 font-medium">Belum ada alamat tersimpan</p>
              <p class="text-sm text-gray-400 mt-1">Silakan tambahkan alamat untuk mempermudah proses checkout</p>
            </div>
            <div v-else class="space-y-4">
              <div v-for="address in addresses" :key="address.id" :class="['p-5 rounded-2xl border transition-all relative', address.is_default ? 'border-primary bg-primary/[0.02]' : 'border-gray-100 hover:border-gray-200 bg-white']">
                <!-- Badges -->
                <div class="flex items-center gap-2 mb-2">
                  <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-semibold uppercase tracking-wider">{{ address.label }}</span>
                  <span v-if="address.is_default" class="px-2 py-0.5 bg-primary text-white rounded text-xs font-semibold uppercase tracking-wider">Utama</span>
                </div>

                <!-- Recipient Info -->
                <h3 class="font-bold text-text flex items-center gap-2 text-base">
                  {{ address.recipient_name }}
                  <span class="text-gray-400 font-normal text-sm border-l border-gray-200 pl-2">{{ address.phone }}</span>
                </h3>

                <!-- Detailed Address -->
                <p class="text-sm text-gray-600 mt-1 max-w-xl">
                  {{ address.address }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                  {{ [address.village, address.district, address.city, address.province, address.postal_code].filter(Boolean).join(', ') }}
                </p>

                <!-- Actions -->
                <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-3">
                  <div class="flex items-center gap-4">
                    <button @click="openEditModal(address)" class="text-sm text-primary font-medium hover:underline flex items-center gap-1">
                      <Icon icon="mdi:pencil-outline" /> Ubah
                    </button>
                    <button v-if="!address.is_default" @click="deleteAddress(address.id)" class="text-sm text-danger font-medium hover:underline flex items-center gap-1">
                      <Icon icon="mdi:trash-can-outline" /> Hapus
                    </button>
                  </div>
                  <button v-if="!address.is_default" @click="setDefaultAddress(address.id)" class="px-3 py-1.5 border border-gray-200 hover:border-primary hover:text-primary rounded-lg text-xs font-medium text-gray-500 transition-all">
                    Atur Sebagai Utama
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form (Tambah / Edit Alamat) -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-gray-100 w-full max-w-xl overflow-hidden shadow-2xl transition-all">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-text">{{ isEdit ? 'Ubah Alamat' : 'Tambah Alamat Baru' }}</h3>
            <button @click="closeModal" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition">
              <Icon icon="mdi:close" class="text-xl" />
            </button>
          </div>
          
          <form @submit.prevent="submitAddress" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Label Alamat (Contoh: Rumah, Kantor) *</label>
                <input v-model="addressForm.label" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Rumah, Toko, Kantor, dll.">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima *</label>
                <input v-model="addressForm.recipient_name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Handphone Penerima *</label>
                <input v-model="addressForm.phone" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="08xxxxxxxxxx">
              </div>
              <div class="col-span-2 relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat (Cari Provinsi, Kota/Kab, Kecamatan, Kode Pos) *</label>
                <div class="relative">
                  <input 
                    type="text" 
                    v-model="addressSearchQuery" 
                    @input="searchAddress" 
                    @focus="showDropdown = true"
                    @blur="handleBlur"
                    required 
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" 
                    placeholder="Masukkan alamat (misal: Joglo, Kebon Jeruk, 11640)..."
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
                    <Icon icon="mdi:loading" class="animate-spin text-primary" /> Mencari alamat...
                  </span>
                </div>
                <p v-if="addressValidationError" class="text-danger text-xs mt-1.5 font-medium flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Alamat wilayah wajib dicari dan dipilih dari hasil pencarian.
                </p>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap (Jalan, No Rumah, RT/RW, dsb.) *</label>
                <textarea v-model="addressForm.address" @input="addressDetailValidationError = false" required rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none"></textarea>
                <p v-if="addressDetailValidationError || addressForm.errors.address" class="text-danger text-xs mt-1.5 font-medium flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Alamat lengkap (jalan, nomor rumah, RT/RW, dsb.) wajib diisi.
                </p>
              </div>
              <div class="col-span-2 flex items-center gap-2 py-2">
                <input type="checkbox" v-model="addressForm.is_default" id="is_default" class="w-4.5 h-4.5 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="is_default" class="text-sm font-medium text-gray-700 select-none cursor-pointer">Atur sebagai alamat utama</label>
              </div>

              <!-- Error Messages Debug -->
              <div v-if="Object.keys(addressForm.errors).length > 0" class="col-span-2 p-3 bg-red-50 border border-red-100 rounded-xl text-red-700 text-xs">
                <p class="font-semibold mb-1 flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Harap perbaiki kesalahan berikut:
                </p>
                <ul class="list-disc pl-4 space-y-0.5">
                  <li v-for="(error, key) in addressForm.errors" :key="key">{{ error }}</li>
                </ul>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
              <button type="button" @click="closeModal" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition-all">Batal</button>
              <button type="submit" :disabled="addressForm.processing" class="px-5 py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl text-sm hover:shadow-lg transition-all disabled:opacity-50">
                {{ addressForm.processing ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </UserLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({
  user: Object,
  addresses: Array,
});

// Profile & Password forms
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
  profileForm.put('/profil');
}

function submitPassword() {
  passwordForm.put('/profil/password', {
    onSuccess: () => passwordForm.reset(),
  });
}

import axios from 'axios';

// Address Management
const showModal = ref(false);
const isEdit = ref(false);
const currentAddressId = ref(null);
const addressValidationError = ref(false);
const addressDetailValidationError = ref(false);

const addressSearchQuery = ref('');
const searchResults = ref([]);
const searching = ref(false);
const showDropdown = ref(false);
let searchTimeout = null;

const addressForm = useForm({
  label: '',
  recipient_name: '',
  phone: '',
  address: '',
  province: '',
  city: '',
  city_id: '',
  district: '',
  village: '',
  postal_code: '',
  is_default: false,
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
  addressForm.province = result.province;
  addressForm.city = result.city;
  addressForm.city_id = result.city_id;
  addressForm.district = result.district;
  addressForm.village = result.village;
  addressForm.postal_code = result.postal_code;
  
  addressSearchQuery.value = result.label;
  showDropdown.value = false;
}

function clearAddressSelection() {
  addressSearchQuery.value = '';
  addressForm.province = '';
  addressForm.city = '';
  addressForm.city_id = '';
  addressForm.district = '';
  addressForm.village = '';
  addressForm.postal_code = '';
  searchResults.value = [];
}

function handleBlur() {
  setTimeout(() => {
    showDropdown.value = false;
  }, 250);
}

function openAddModal() {
  isEdit.value = false;
  currentAddressId.value = null;
  addressForm.reset();
  addressForm.label = 'Rumah';
  addressForm.recipient_name = props.user.name;
  addressForm.phone = props.user.phone || '';
  addressSearchQuery.value = '';
  addressValidationError.value = false;
  addressDetailValidationError.value = false;
  showModal.value = true;
}

function openEditModal(address) {
  isEdit.value = true;
  currentAddressId.value = address.id;
  addressForm.label = address.label;
  addressForm.recipient_name = address.recipient_name;
  addressForm.phone = address.phone;
  addressForm.address = address.address;
  addressForm.province = address.province || '';
  addressForm.city = address.city || '';
  addressForm.city_id = address.city_id || '';
  addressForm.district = address.district || '';
  addressForm.village = address.village || '';
  addressForm.postal_code = address.postal_code || '';
  addressForm.is_default = address.is_default;
  addressValidationError.value = false;
  addressDetailValidationError.value = false;
  
  addressSearchQuery.value = [
    address.village,
    address.district,
    address.city,
    address.province,
    address.postal_code
  ].filter(Boolean).join(', ');
  
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  addressForm.reset();
  addressSearchQuery.value = '';
  searchResults.value = [];
  addressValidationError.value = false;
  addressDetailValidationError.value = false;
}

function submitAddress() {
  let hasError = false;

  if (!addressForm.city_id) {
    addressValidationError.value = true;
    hasError = true;
  } else {
    addressValidationError.value = false;
  }

  if (!addressForm.address || !addressForm.address.trim()) {
    addressDetailValidationError.value = true;
    hasError = true;
  } else {
    addressDetailValidationError.value = false;
  }

  if (hasError) {
    return;
  }

  if (isEdit.value) {
    addressForm.put(`/alamat/${currentAddressId.value}`, {
      onSuccess: () => closeModal(),
    });
  } else {
    addressForm.post('/alamat', {
      onSuccess: () => closeModal(),
    });
  }
}

function deleteAddress(id) {
  if (confirm('Apakah Anda yakin ingin menghapus alamat ini?')) {
    router.delete(`/alamat/${id}`);
  }
}

function setDefaultAddress(id) {
  router.put(`/alamat/${id}/default`);
}
</script>
