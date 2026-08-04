<template>
  <Head :title="`Pilih Pengiriman - ${preOrder.po_code}`" />
  <UserLayout>
    <div class="max-w-5xl mx-auto px-4 py-10">
      <Link :href="`/pre-order/${preOrder.id}`" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-6">
        <Icon icon="mdi:arrow-left" /> Kembali ke Detail PO
      </Link>

      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pilih Ekspedisi & Pembayaran</h1>
        <p class="text-sm text-gray-500 mt-1">Pre-Order <strong>{{ preOrder.po_code }}</strong> telah disetujui. Estimasi pengerjaan: <strong>{{ preOrder.estimated_days }} hari</strong>.</p>
      </div>

      <form @submit.prevent="submit" class="grid lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Shipping Address Summary -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white mb-3 flex items-center gap-2">
              <Icon icon="mdi:map-marker-outline" class="text-primary text-xl" /> Alamat Pengiriman
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
              <h3 class="font-bold text-gray-900 dark:text-white">
                {{ preOrder.shipping_name }}
                <span class="text-gray-400 dark:text-gray-500 font-normal text-sm border-l border-gray-200 dark:border-gray-700 pl-2">{{ preOrder.shipping_phone }}</span>
              </h3>
              <p>{{ preOrder.shipping_address }}</p>
              <p class="text-xs text-gray-400">
                {{ preOrder.shipping_city }}, {{ preOrder.shipping_province }}
              </p>
            </div>
          </div>

          <!-- Courier Selection Section (Shopee-like) -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Icon icon="mdi:truck-delivery-outline" class="text-primary text-2xl" /> Opsi Pengiriman
            </h2>

            <div class="grid grid-cols-3 gap-3">
              <!-- Courier options -->
              <label 
                v-for="c in ['jne', 'jnt', 'sicepat', 'ninja', 'pos']" 
                :key="c" 
                :class="['flex items-center gap-2 p-3 rounded-2xl border cursor-pointer transition-all justify-center', form.courier === c ? 'border-primary bg-primary/[0.02]' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600']"
              >
                <input type="radio" :value="c" v-model="form.courier" @change="calculateShippingCost" class="sr-only">
                <div :class="['w-4 h-4 rounded-full border flex items-center justify-center shrink-0', form.courier === c ? 'border-primary' : 'border-gray-300 dark:border-gray-600']">
                  <div v-if="form.courier === c" class="w-2 h-2 rounded-full bg-primary"></div>
                </div>
                <span class="font-bold text-gray-700 dark:text-gray-300 uppercase text-xs">{{ c }}</span>
              </label>
            </div>

            <!-- Courier Services -->
            <div v-if="loadingShipping" class="mt-6 p-4 text-center text-sm text-gray-500">
              <Icon icon="mdi:loading" class="animate-spin text-primary text-xl mx-auto mb-2" />
              Mencari layanan pengiriman...
            </div>
            
            <div v-else-if="shippingServices.length > 0" class="mt-6 space-y-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Layanan Pengiriman:</label>
              <div class="grid grid-cols-1 gap-2">
                <label 
                  v-for="service in shippingServices" 
                  :key="service.service" 
                  :class="['flex items-center justify-between p-4 rounded-xl border cursor-pointer transition-all', form.courier_service === service.service ? 'border-primary bg-primary/[0.01]' : 'border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-750']"
                >
                  <input type="radio" :value="service.service" v-model="form.courier_service" @change="selectShippingService(service)" class="sr-only">
                  <div class="flex items-center gap-3">
                    <div :class="['w-4 h-4 rounded-full border flex items-center justify-center shrink-0', form.courier_service === service.service ? 'border-primary' : 'border-gray-300 dark:border-gray-600']">
                      <div v-if="form.courier_service === service.service" class="w-2 h-2 rounded-full bg-primary"></div>
                    </div>
                    <div>
                      <span class="font-semibold text-gray-900 dark:text-white text-sm">{{ service.service }}</span>
                      <span class="text-xs text-gray-400 block">{{ service.description }} (Estimasi: {{ service.cost[0].etd }} hari)</span>
                    </div>
                  </div>
                  <span class="font-bold text-primary text-sm">Rp {{ formatPrice(service.cost[0].value) }}</span>
                </label>
              </div>
            </div>

            <div v-else-if="form.courier" class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300 rounded-xl text-xs">
              Tidak ada layanan pengiriman yang tersedia untuk kurir ini. Silakan pilih kurir lain atau hubungi admin.
            </div>
          </div>
        </div>

        <!-- Right Sidebar (PO Summary & Calculation) -->
        <div>
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 sticky top-24 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ringkasan Biaya</h2>
            
            <div class="space-y-3">
              <div v-for="item in preOrder.items" :key="item.product_name" class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400 max-w-[180px] truncate block">{{ item.product_name }} x{{ item.qty }}</span>
                <span class="font-medium text-gray-900 dark:text-white">Rp {{ formatPrice(item.subtotal) }}</span>
              </div>
            </div>
            
            <hr class="my-4 border-gray-100 dark:border-gray-700">

            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
              <span>Subtotal Produk</span>
              <span class="font-medium text-gray-900 dark:text-white">Rp {{ formatPrice(preOrder.total_amount) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mt-2">
              <span>Ongkos Kirim</span>
              <span class="font-medium text-gray-900 dark:text-white">
                <template v-if="form.shipping_cost > 0">
                  Rp {{ formatPrice(form.shipping_cost) }}
                </template>
                <template v-else-if="loadingShipping">
                  Menghitung...
                </template>
                <template v-else>
                  Rp 0
                </template>
              </span>
            </div>
            <div class="flex justify-between text-xs text-gray-400 mt-1">
              <span>Berat Total</span>
              <span>{{ formatPrice(totalWeight) }} gram</span>
            </div>

            <hr class="my-4 border-gray-100 dark:border-gray-700">
            
            <div class="flex justify-between text-lg font-bold">
              <span class="text-gray-900 dark:text-white">Total</span>
              <span class="text-primary">Rp {{ formatPrice(preOrder.total_amount + form.shipping_cost) }}</span>
            </div>

            <button type="submit" :disabled="form.processing || !form.courier || !form.courier_service || !form.payment_method" 
              class="mt-6 w-full py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer">
              <Icon v-if="form.processing" icon="mdi:loading" class="animate-spin" />
              <Icon v-else icon="mdi:check-circle-outline" />
              {{ form.processing ? 'Memproses...' : 'Konfirmasi & Proses' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </UserLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import axios from 'axios';

const props = defineProps({ 
  preOrder: Object,
  totalWeight: Number,
});

const form = useForm({
  courier: '',
  courier_service: '',
  shipping_cost: 0,
  payment_method: '',
});

const shippingServices = ref([]);
const loadingShipping = ref(false);

async function calculateShippingCost() {
  if (!props.preOrder.city_id || !form.courier) {
    form.shipping_cost = 0;
    shippingServices.value = [];
    return;
  }
  
  loadingShipping.value = true;
  shippingServices.value = [];
  
  try {
    const response = await axios.post('/api/shipping-cost', {
      destination_city_id: props.preOrder.city_id,
      weight: props.totalWeight,
      courier: form.courier
    });
    
    if (response.data && response.data.length > 0) {
      shippingServices.value = response.data[0].costs || [];
      
      if (shippingServices.value.length > 0) {
        const regService = shippingServices.value.find(s => s.service === 'REG' || s.service.includes('Reguler')) || shippingServices.value[0];
        selectShippingService(regService);
      } else {
        form.courier_service = '';
        form.shipping_cost = 0;
      }
    }
  } catch (e) {
    console.error("Gagal menghitung ongkos kirim PO:", e);
  } finally {
    loadingShipping.value = false;
  }
}

function selectShippingService(service) {
  form.courier_service = service.service;
  const cost = service.cost[0]?.value || 0;
  form.shipping_cost = cost;
}

function formatPrice(p) {
  return Number(p).toLocaleString('id-ID');
}

function submit() {
  form.post(`/pre-order/${props.preOrder.id}/pengiriman`);
}
</script>
