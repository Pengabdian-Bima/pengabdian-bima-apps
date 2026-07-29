<template>
  <Head title="Pre-Order - Admin" />
  <AdminLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-text">Pre-Order</h1>
      <div v-if="pendingCount > 0" class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 px-4 py-2 rounded-xl">
        <Icon icon="mdi:clock-alert-outline" class="text-yellow-600 text-lg" />
        <span class="text-sm font-semibold text-yellow-700">{{ pendingCount }} PO menunggu review</span>
      </div>
    </div>

    <!-- Status Filter -->
    <div class="flex flex-wrap gap-2 mb-6">
      <Link v-for="s in statuses" :key="s.value ?? 'all'" :href="buildUrl(s.value)"
        :class="['px-4 py-2 rounded-xl text-sm font-medium transition-all', currentStatus === s.value ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'bg-white border border-gray-200 text-gray-600 hover:border-primary hover:text-primary']">
        {{ s.label }}
      </Link>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-left whitespace-nowrap">
              <th class="px-4 py-3 font-medium text-gray-500">Kode PO</th>
              <th class="px-4 py-3 font-medium text-gray-500">Pelanggan</th>
              <th class="px-4 py-3 font-medium text-gray-500">Total</th>
              <th class="px-4 py-3 font-medium text-gray-500">Status</th>
              <th class="px-4 py-3 font-medium text-gray-500">Tanggal</th>
              <th class="px-4 py-3 font-medium text-gray-500">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="po in preOrders.data" :key="po.id" class="border-t border-gray-50 hover:bg-gray-50/50 whitespace-nowrap">
              <td class="px-4 py-3">
                <span class="font-bold text-text">{{ po.po_code }}</span>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ po.user_name }}</td>
              <td class="px-4 py-3 font-medium text-primary">Rp {{ fmt(po.total_amount) }}</td>
              <td class="px-4 py-3">
                <span :class="['text-xs px-2 py-1 rounded-full font-medium', sc(po.status_color)]">{{ po.status_label }}</span>
              </td>
              <td class="px-4 py-3 text-gray-500 text-xs">{{ po.created_at }}</td>
              <td class="px-4 py-3">
                <Link :href="`/admin/pre-orders/${po.id}`" class="p-1.5 text-blue-500 bg-blue-100 border border-blue-400 hover:bg-blue-50 rounded-lg transition inline-flex">
                  <Icon icon="mdi:eye" />
                </Link>
              </td>
            </tr>
            <tr v-if="!preOrders.data.length">
              <td colspan="6" class="px-4 py-10 text-center text-gray-500">Tidak ada pre-order ditemukan</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="preOrders.last_page > 1" class="flex justify-center gap-2 mt-6">
      <Link v-for="link in preOrders.links" :key="link.label" :href="link.url || '#'"
        :class="['px-3 py-1.5 rounded-lg text-sm transition', link.active ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-primary', !link.url ? 'opacity-40 pointer-events-none' : '']"
        v-html="link.label" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  preOrders: Object,
  currentStatus: String,
  pendingCount: Number,
});

const statuses = [
  { value: null, label: 'Semua' },
  { value: 'pending', label: 'Menunggu Review' },
  { value: 'accepted', label: 'Diterima' },
  { value: 'rejected', label: 'Ditolak' },
  { value: 'processing', label: 'Diproses' },
  { value: 'completed', label: 'Selesai' },
  { value: 'cancelled', label: 'Dibatalkan' },
];

function fmt(p) { return Number(p).toLocaleString('id-ID'); }
function sc(c) {
  return {
    warning: 'bg-yellow-100 text-yellow-700',
    info:    'bg-blue-100 text-blue-700',
    primary: 'bg-orange-100 text-orange-700',
    success: 'bg-green-100 text-green-700',
    danger:  'bg-red-100 text-red-700',
    default: 'bg-gray-100 text-gray-600',
  }[c] || 'bg-gray-100 text-gray-600';
}

function buildUrl(status) {
  return '/admin/pre-orders' + (status ? '?status=' + status : '');
}
</script>
