<template>
  <Head title="Pesanan Saya" />
  <UserLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 class="text-3xl font-bold text-text mb-8">Pesanan Saya</h1>

      <!-- Date Filter Panel -->
      <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-6 shadow-sm flex flex-col md:flex-row md:items-end gap-4">
        <div class="grid grid-cols-2 gap-4 flex-1">
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal Awal</label>
            <input type="date" v-model="startDate" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm transition">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal Akhir</label>
            <input type="date" v-model="endDate" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm transition">
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button @click="applyFilter" class="px-5 py-2.5 bg-primary text-white font-semibold rounded-xl text-sm hover:shadow-lg transition-all flex items-center gap-1">
            <Icon icon="mdi:filter-variant" /> Filter
          </button>
          <button @click="resetFilter" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition-all flex items-center gap-1">
            <Icon icon="mdi:refresh" /> Reset
          </button>
        </div>
      </div>

      <!-- Orders List -->
      <div v-if="orders.data.length" class="space-y-4">
        <Link v-for="order in orders.data" :key="order.id" :href="`/pesanan/${order.id}`"
          class="block bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-lg transition-all duration-300 hover:border-primary/20">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-gray-500">{{ order.order_code }}</span>
            <span :class="['text-xs px-3 py-1 rounded-full font-medium', statusClass(order.status_color)]">{{ order.status_label }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">{{ order.created_at }}</span>
            <span class="text-lg font-bold text-primary">Rp {{ formatPrice(order.total_amount) }}</span>
          </div>
          <div v-if="order.status === 'menunggu_pembayaran' && !order.has_payment" class="mt-3 text-sm text-warning flex items-center gap-1">
            <Icon icon="mdi:alert" /> Segera lakukan pembayaran
          </div>
        </Link>
      </div>
      <div v-else class="text-center py-20 bg-white border border-gray-100 rounded-2xl shadow-sm">
        <Icon icon="mdi:package-variant" class="text-6xl text-gray-300 mx-auto mb-4" />
        <p class="text-gray-500 mb-4 font-medium">Tidak ada riwayat pesanan</p>
        <Link href="/produk" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl hover:shadow-lg transition"><Icon icon="mdi:shopping" /> Mulai Belanja</Link>
      </div>
    </div>
  </UserLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({ orders: Object, filters: Object });

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

function formatPrice(p) { return Number(p).toLocaleString('id-ID'); }
function statusClass(color) {
  const map = { warning: 'bg-yellow-100 text-yellow-700', info: 'bg-blue-100 text-blue-700', primary: 'bg-orange-100 text-orange-700', success: 'bg-green-100 text-green-700', danger: 'bg-red-100 text-red-700' };
  return map[color] || 'bg-gray-100 text-gray-700';
}

function applyFilter() {
  router.get('/pesanan', {
    start_date: startDate.value,
    end_date: endDate.value
  }, {
    preserveState: true,
    replace: true
  });
}

function resetFilter() {
  startDate.value = '';
  endDate.value = '';
  router.get('/pesanan');
}
</script>
