<template>
  <Head title="Checkout" />
  <UserLayout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 class="text-3xl font-bold text-text mb-8">Checkout</h1>

      <form @submit.prevent="submit" class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-text mb-4 flex items-center gap-2"><Icon icon="mdi:truck-delivery" class="text-primary text-xl" /> Data Pengiriman</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima *</label><input v-model="form.shipping_name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-1">No. HP *</label><input v-model="form.shipping_phone" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label><input v-model="form.shipping_province" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-1">Kabupaten/Kota</label><input v-model="form.shipping_city" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label><input v-model="form.shipping_district" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-1">Desa/Kelurahan</label><input v-model="form.shipping_village" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label><input v-model="form.shipping_postal_code" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"></div>
            </div>
            <div class="mt-4"><label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap *</label><textarea v-model="form.shipping_address" required rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none"></textarea></div>
            <div class="mt-4"><label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label><textarea v-model="form.notes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none" placeholder="Catatan untuk penjual (opsional)"></textarea></div>
          </div>
        </div>

        <div>
          <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24">
            <h2 class="text-lg font-semibold text-text mb-4">Ringkasan Pesanan</h2>
            <div class="space-y-3">
              <div v-for="item in items" :key="item.id" class="flex justify-between text-sm">
                <span class="text-gray-600">{{ item.product_name }} x{{ item.qty }}</span>
                <span class="font-medium">Rp {{ formatPrice(item.subtotal) }}</span>
              </div>
            </div>
            <hr class="my-4 border-gray-100">
            <div class="flex justify-between text-lg font-bold">
              <span>Total</span>
              <span class="text-primary">Rp {{ formatPrice(total) }}</span>
            </div>

            <div class="mt-4 p-3 bg-blue-50 rounded-xl">
              <p class="text-sm text-blue-700 flex items-center gap-2"><Icon icon="mdi:information" class="text-lg" /> Pembayaran via transfer bank manual</p>
            </div>

            <button type="submit" :disabled="form.processing" class="mt-4 w-full py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50">
              {{ form.processing ? 'Memproses...' : 'Buat Pesanan' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </UserLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({ items: Array, total: Number, user: Object });

const form = useForm({
  shipping_name: props.user?.name || '',
  shipping_phone: props.user?.phone || '',
  shipping_address: '',
  shipping_province: '',
  shipping_city: '',
  shipping_district: '',
  shipping_village: '',
  shipping_postal_code: '',
  notes: '',
});

function formatPrice(p) { return Number(p).toLocaleString('id-ID'); }
function submit() { form.post('/checkout'); }
</script>
