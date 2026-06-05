<template>
  <Head :title="`Pesanan ${order.order_code}`" />
  <UserLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="flex items-center justify-between mb-8">
        <div>
          <Link href="/pesanan" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-2"><Icon icon="mdi:arrow-left" /> Kembali</Link>
          <h1 class="text-2xl font-bold text-text">{{ order.order_code }}</h1>
        </div>
        <span :class="['px-4 py-1.5 rounded-full text-sm font-medium', statusClass(order.status_color)]">{{ order.status_label }}</span>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-text mb-4">Item Pesanan</h2>
            <div class="space-y-3">
              <div v-for="item in order.items" :key="item.id" class="flex justify-between py-2 border-b border-gray-50 last:border-0">
                <div><p class="font-medium text-text">{{ item.product_name }}</p><p class="text-sm text-gray-500">{{ item.qty }} x Rp {{ fmt(item.price) }}</p></div>
                <p class="font-semibold">Rp {{ fmt(item.subtotal) }}</p>
              </div>
            </div>
            <div class="flex justify-between mt-4 pt-4 border-t border-gray-100 text-lg font-bold"><span>Total</span><span class="text-primary">Rp {{ fmt(order.total_amount) }}</span></div>
          </div>
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-text mb-4">Pengiriman</h2>
            <div class="text-sm space-y-1 text-gray-600">
              <p><strong>{{ order.shipping_name }}</strong> ({{ order.shipping_phone }})</p>
              <p>{{ order.shipping_address }}</p>
              <p v-if="order.notes" class="mt-2 italic text-gray-500">Catatan: {{ order.notes }}</p>
            </div>
          </div>
        </div>
        <div class="space-y-6">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-text mb-4">Pembayaran</h2>
            <div v-if="order.payment" class="text-sm space-y-2">
              <p>{{ order.payment.sender_name }} - {{ order.payment.sender_bank }}</p>
              <p>Rp {{ fmt(order.payment.amount) }} | {{ order.payment.transfer_date }}</p>
              <img v-if="order.payment.proof_image_url" :src="order.payment.proof_image_url" class="w-full rounded-xl mt-2">
            </div>
            <div v-else-if="order.status === 'menunggu_pembayaran'">
              <p class="text-sm text-gray-500 mb-3">Transfer ke:</p>
              <div class="text-sm space-y-1"><p class="p-2 bg-gray-50 rounded-lg"><strong>BRI:</strong> 0123-4567-8901</p><p class="p-2 bg-gray-50 rounded-lg"><strong>BNI:</strong> 9876-5432-1098</p></div>
              <Link :href="`/pesanan/${order.id}/bayar`" class="mt-4 w-full py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-semibold rounded-xl flex items-center justify-center gap-2 hover:shadow-lg transition-all">
                <Icon icon="mdi:upload" /> Upload Bukti
              </Link>
            </div>
          </div>
          <div v-if="order.status === 'menunggu_pembayaran'">
            <button @click="router.post(`/pesanan/${order.id}/batal`)" class="w-full py-2.5 bg-red-50 text-danger text-sm font-semibold rounded-xl hover:bg-red-100 transition">Batalkan Pesanan</button>
          </div>
          <div v-if="order.status === 'dikirim'">
            <button @click="router.post(`/pesanan/${order.id}/selesai`)" class="w-full py-2.5 bg-green-50 text-success text-sm font-semibold rounded-xl hover:bg-green-100 transition">Pesanan Diterima</button>
          </div>
        </div>
      </div>
    </div>
  </UserLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';
defineProps({ order: Object });
function fmt(p) { return Number(p).toLocaleString('id-ID'); }
function statusClass(c) { return { warning:'bg-yellow-100 text-yellow-700',info:'bg-blue-100 text-blue-700',primary:'bg-orange-100 text-orange-700',success:'bg-green-100 text-green-700',danger:'bg-red-100 text-red-700' }[c]||'bg-gray-100 text-gray-700'; }
</script>
