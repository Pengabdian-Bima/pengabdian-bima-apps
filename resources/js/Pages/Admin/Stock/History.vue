<template>
  <Head :title="`Riwayat Stok - ${product.name}`" />
  <AdminLayout>
    <Link href="/admin/stock" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-4"><Icon icon="mdi:arrow-left" /> Kembali</Link>
    <div class="flex items-center justify-between mb-6">
      <div><h1 class="text-2xl font-bold text-text">Riwayat Stok</h1><p class="text-gray-500">{{ product.name }} — Stok saat ini: <strong>{{ product.stock }}</strong></p></div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 font-medium text-gray-500">Tanggal</th><th class="px-4 py-3 font-medium text-gray-500">Tipe</th><th class="px-4 py-3 font-medium text-gray-500">Jumlah</th><th class="px-4 py-3 font-medium text-gray-500">Sebelum</th><th class="px-4 py-3 font-medium text-gray-500">Sesudah</th><th class="px-4 py-3 font-medium text-gray-500">Keterangan</th></tr></thead>
        <tbody>
          <tr v-for="h in histories.data" :key="h.id" class="border-t border-gray-50">
            <td class="px-4 py-3 text-gray-600">{{ h.created_at }}</td>
            <td class="px-4 py-3"><span :class="['text-xs px-2 py-1 rounded-full font-medium', h.type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">{{ h.type === 'in' ? 'Masuk' : 'Keluar' }}</span></td>
            <td class="px-4 py-3 font-bold" :class="h.type === 'in' ? 'text-success' : 'text-danger'">{{ h.type === 'in' ? '+' : '-' }}{{ h.quantity }}</td>
            <td class="px-4 py-3 text-gray-600">{{ h.stock_before }}</td>
            <td class="px-4 py-3 font-medium">{{ h.stock_after }}</td>
            <td class="px-4 py-3 text-gray-500">{{ h.note || '-' }}</td>
          </tr>
          <tr v-if="!histories.data.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada riwayat</td></tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineProps({ product: Object, histories: Object });
</script>
