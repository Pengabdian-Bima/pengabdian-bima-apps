<template>
  <Head title="Pre-Order Saya" />
  <UserLayout>
    <div class="max-w-4xl mx-auto px-4 py-10">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pre-Order Saya</h1>
          <p class="text-sm text-gray-500 mt-1">Riwayat permintaan Pre-Order Anda</p>
        </div>
        <Link href="/pre-order/buat" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-semibold text-sm rounded-xl hover:bg-primary-dark transition-all shadow-md shadow-primary/20 cursor-pointer">
          <Icon icon="mdi:plus" class="text-lg" />
          Buat Pre-Order
        </Link>
      </div>

      <!-- Empty State -->
      <div v-if="preOrders.length === 0" class="text-center py-20 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
        <Icon icon="mdi:clipboard-list-outline" class="text-gray-300 dark:text-gray-600 text-6xl mx-auto mb-4" />
        <h2 class="text-xl font-semibold text-gray-600 dark:text-gray-300 mb-2">Belum ada Pre-Order</h2>
        <p class="text-sm text-gray-400 mb-6">Buat Pre-Order untuk memesan produk dalam jumlah besar atau khusus.</p>
        <Link href="/pre-order/buat" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all">
          <Icon icon="mdi:plus" /> Buat Pre-Order Pertama
        </Link>
      </div>

      <!-- PO List -->
      <div v-else class="space-y-4">
        <Link v-for="po in preOrders" :key="po.id" :href="`/pre-order/${po.id}`"
          class="block bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 hover:border-primary/30 hover:shadow-md transition-all">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-primary/10 text-primary px-2 py-0.5 rounded-full">
                  <Icon icon="mdi:clipboard-list-outline" class="text-xs" /> PO
                </span>
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">{{ po.po_code }}</h3>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ po.created_at }}</p>
              <p class="text-sm font-semibold text-primary mt-2">Rp {{ fmt(po.total_amount) }}</p>
            </div>
            <div class="text-right shrink-0">
              <span :class="['inline-block px-3 py-1 rounded-full text-xs font-semibold', sc(po.status_color)]">
                {{ po.status_label }}
              </span>
              <div class="mt-3 text-xs text-gray-400 flex items-center justify-end gap-1">
                Lihat Detail <Icon icon="mdi:chevron-right" />
              </div>
            </div>
          </div>
        </Link>
      </div>
    </div>
  </UserLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

defineProps({ preOrders: Array });

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
</script>
