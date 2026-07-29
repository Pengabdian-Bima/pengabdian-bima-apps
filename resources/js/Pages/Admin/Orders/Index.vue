<template>
  <Head title="Pesanan - Admin" />
  <AdminLayout>
    <h1 class="text-2xl font-bold text-text mb-6">Pemesanan</h1>
    
    <!-- Status Filter -->
    <div class="flex flex-wrap gap-2 mb-6">
      <Link v-for="s in statuses" :key="s.value" :href="buildStatusUrl(s.value)"
        :class="['px-4 py-2 rounded-xl text-sm font-medium transition-all', currentStatus === s.value ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'bg-white border border-gray-200 text-gray-600 hover:border-primary hover:text-primary']">
        {{ s.label }}
      </Link>
    </div>

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

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead><tr class="bg-gray-50 text-left whitespace-nowrap"><th class="px-4 py-3 font-medium text-gray-500">Kode</th><th class="px-4 py-3 font-medium text-gray-500">Pelanggan</th><th class="px-4 py-3 font-medium text-gray-500">Total</th><th class="px-4 py-3 font-medium text-gray-500">Status</th><th class="px-4 py-3 font-medium text-gray-500">Tanggal</th><th class="px-4 py-3 font-medium text-gray-500">Aksi</th></tr></thead>
          <tbody>
            <tr v-for="o in orders.data" :key="o.id" class="border-t border-gray-50 hover:bg-gray-50/50 whitespace-nowrap">
              <td class="px-4 py-3 font-medium text-text">{{ o.order_code }}</td>
              <td class="px-4 py-3 text-gray-600">{{ o.user_name }}</td>
              <td class="px-4 py-3 font-medium text-primary">Rp {{ fmt(o.total_amount) }}</td>
              <td class="px-4 py-3"><span :class="['text-xs px-2 py-1 rounded-full font-medium', sc(o.status_color)]">{{ o.status_label }}</span></td>
              <td class="px-4 py-3 text-gray-500 text-xs">{{ o.created_at }}</td>
              <td class="px-4 py-3"><Link :href="`/admin/orders/${o.id}`" class="p-1.5 text-blue-500 bg-blue-100 border border-blue-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition inline-flex"><Icon icon="mdi:eye" /></Link></td>
            </tr>
            <tr v-if="!orders.data.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada pesanan ditemukan</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ orders: Object, currentStatus: String, filters: Object });

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const statuses = [
  { value: null, label: 'Semua' },
  { value: 'menunggu_pembayaran', label: 'Menunggu Pembayaran' },
  { value: 'menunggu_verifikasi', label: 'Menunggu Verifikasi' },
  { value: 'diproses', label: 'Diproses' },
  { value: 'dikirim', label: 'Dikirim' },
  { value: 'selesai', label: 'Selesai' },
  { value: 'ditolak', label: 'Ditolak' },
  { value: 'dibatalkan', label: 'Dibatalkan' },
];

function fmt(p) { return Number(p).toLocaleString('id-ID'); }
function sc(c) { return { warning:'bg-yellow-100 text-yellow-700',info:'bg-blue-100 text-blue-700',primary:'bg-orange-100 text-orange-700',success:'bg-green-100 text-green-700',danger:'bg-red-100 text-red-700' }[c]||'bg-gray-100 text-gray-700'; }

function buildStatusUrl(status) {
  const query = {};
  if (status) query.status = status;
  if (startDate.value) query.start_date = startDate.value;
  if (endDate.value) query.end_date = endDate.value;
  
  const params = new URLSearchParams(query).toString();
  return '/admin/orders' + (params ? '?' + params : '');
}

function applyFilter() {
  const query = {};
  if (props.currentStatus) query.status = props.currentStatus;
  if (startDate.value) query.start_date = startDate.value;
  if (endDate.value) query.end_date = endDate.value;

  router.get('/admin/orders', query, {
    preserveState: true,
    replace: true
  });
}

function resetFilter() {
  startDate.value = '';
  endDate.value = '';
  
  const query = {};
  if (props.currentStatus) query.status = props.currentStatus;
  
  router.get('/admin/orders', query);
}
</script>
