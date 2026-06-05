<template>
  <Head title="Pesanan - Admin" />
  <AdminLayout>
    <h1 class="text-2xl font-bold text-text mb-6">Pemesanan</h1>
    <!-- Status Filter -->
    <div class="flex flex-wrap gap-2 mb-6">
      <Link v-for="s in statuses" :key="s.value" :href="s.value ? `/admin/orders?status=${s.value}` : '/admin/orders'"
        :class="['px-4 py-2 rounded-xl text-sm font-medium transition-all', currentStatus === s.value ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'bg-white border border-gray-200 text-gray-600 hover:border-primary hover:text-primary']">
        {{ s.label }}
      </Link>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 font-medium text-gray-500">Kode</th><th class="px-4 py-3 font-medium text-gray-500">Pelanggan</th><th class="px-4 py-3 font-medium text-gray-500">Total</th><th class="px-4 py-3 font-medium text-gray-500">Status</th><th class="px-4 py-3 font-medium text-gray-500">Tanggal</th><th class="px-4 py-3 font-medium text-gray-500">Aksi</th></tr></thead>
        <tbody>
          <tr v-for="o in orders.data" :key="o.id" class="border-t border-gray-50 hover:bg-gray-50/50">
            <td class="px-4 py-3 font-medium text-text">{{ o.order_code }}</td>
            <td class="px-4 py-3 text-gray-600">{{ o.user_name }}</td>
            <td class="px-4 py-3 font-medium text-primary">Rp {{ fmt(o.total_amount) }}</td>
            <td class="px-4 py-3"><span :class="['text-xs px-2 py-1 rounded-full font-medium', sc(o.status_color)]">{{ o.status_label }}</span></td>
            <td class="px-4 py-3 text-gray-500 text-xs">{{ o.created_at }}</td>
            <td class="px-4 py-3"><Link :href="`/admin/orders/${o.id}`" class="p-1.5 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition inline-flex"><Icon icon="mdi:eye" /></Link></td>
          </tr>
          <tr v-if="!orders.data.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada pesanan</td></tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineProps({ orders: Object, currentStatus: String });
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
</script>
