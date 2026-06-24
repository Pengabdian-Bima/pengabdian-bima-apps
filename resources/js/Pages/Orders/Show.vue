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
          <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-semibold text-text mb-4 flex items-center gap-1.5">
              <Icon icon="mdi:credit-card-outline" class="text-primary text-xl" /> Pembayaran
            </h2>
            
            <div class="mb-4 pb-4 border-b border-gray-50">
              <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Metode Pembayaran</span>
              <span class="text-sm font-semibold text-text mt-1 flex items-center gap-1.5">
                <Icon :icon="order.payment_method === 'qris' ? 'mdi:qrcode-scan' : 'mdi:bank'" class="text-primary text-lg" />
                {{ order.payment_method === 'qris' ? 'QRIS (E-Wallet)' : 'Transfer Bank Manual' }}
              </span>
            </div>

            <div v-if="order.payment" class="text-sm space-y-2 bg-gray-50 p-4 rounded-xl">
              <div class="flex justify-between"><span class="text-gray-500">Pengirim:</span><span class="font-medium text-text">{{ order.payment.sender_name }}</span></div>
              <div class="flex justify-between"><span class="text-gray-500">Bank/E-Wallet:</span><span class="font-medium text-text">{{ order.payment.sender_bank }}</span></div>
              <div class="flex justify-between"><span class="text-gray-500">Jumlah:</span><span class="font-bold text-primary">Rp {{ fmt(order.payment.amount) }}</span></div>
              <div class="flex justify-between"><span class="text-gray-500">Tanggal:</span><span class="text-gray-500 text-xs">{{ order.payment.transfer_date }}</span></div>
              <div class="pt-2">
                <span class="text-xs font-medium text-gray-400 block mb-1">Bukti Transfer:</span>
                <img v-if="order.payment.proof_image_url" :src="order.payment.proof_image_url" class="w-full rounded-xl border border-gray-100 hover:scale-105 transition duration-300">
              </div>
            </div>
            
            <div v-else-if="order.status === 'menunggu_pembayaran'" class="space-y-4">
              <!-- QRIS Display -->
              <div v-if="order.payment_method === 'qris'" class="text-center bg-gray-50 p-4 rounded-xl border border-gray-100">
                <p class="text-xs font-semibold text-text mb-2">SCAN BARCODE QRIS BERIKUT</p>
                <div class="bg-white p-2 rounded-xl inline-block border border-gray-200 shadow-sm mb-2 cursor-pointer hover:shadow-md transition-all duration-300 group" @click="showQrisModal = true">
                  <div class="relative overflow-hidden rounded-lg">
                    <img src="/img/qris-barcode.png" alt="QRIS Barcode" class="w-48 h-48 object-contain mx-auto group-hover:scale-105 transition-transform duration-500" />
                    <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                      <div class="bg-white/90 backdrop-blur-sm p-2 rounded-full shadow-sm">
                        <Icon icon="mdi:magnify-plus-outline" class="text-primary text-xl" />
                      </div>
                    </div>
                  </div>
                </div>
                <p class="text-[10px] text-gray-400">Scan QRIS menggunakan GoPay, OVO, Dana, LinkAja, ShopeePay atau Mobile Banking</p>
              </div>

              <!-- Bank Transfer Display -->
              <div v-else class="space-y-2">
                <p class="text-xs text-gray-500">Silakan transfer manual ke rekening berikut:</p>
                <div class="text-xs space-y-2">
                  <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center">
                    <div>
                      <span class="font-bold text-text">Bank BRI</span>
                      <p class="font-mono text-gray-500 mt-0.5 select-all">0123-4567-8901</p>
                    </div>
                    <span class="text-[10px] text-gray-400">A.N UDF Flamboyan</span>
                  </div>
                  <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center">
                    <div>
                      <span class="font-bold text-text">Bank BNI</span>
                      <p class="font-mono text-gray-500 mt-0.5 select-all">9876-5432-1098</p>
                    </div>
                    <span class="text-[10px] text-gray-400">A.N UDF Flamboyan</span>
                  </div>
                </div>
              </div>

              <Link :href="`/pesanan/${order.id}/bayar`" class="mt-4 w-full py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-semibold rounded-xl flex items-center justify-center gap-2 hover:shadow-lg transition-all">
                <Icon icon="mdi:upload" /> Upload Bukti Pembayaran
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

    <!-- QRIS Modal -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showQrisModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" @click="showQrisModal = false">
        <div class="relative max-w-sm w-full bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all" @click.stop>
          <button @click="showQrisModal = false" class="absolute top-4 right-4 w-8 h-8 bg-black/10 hover:bg-black/20 rounded-full flex items-center justify-center text-gray-700 hover:text-black transition-colors z-10">
            <Icon icon="mdi:close" class="text-xl" />
          </button>
          <div class="p-6 pt-8 text-center">
            <h3 class="font-bold text-lg text-text mb-1">QRIS Pembayaran</h3>
            <p class="text-xs text-gray-500 mb-6">Scan barcode ini untuk menyelesaikan pembayaran pesanan Anda.</p>
            <div class="bg-white p-2 border border-gray-100 rounded-2xl shadow-inner inline-block">
              <img src="/img/qris-barcode.png" alt="QRIS Full" class="w-full max-w-[280px] h-auto object-contain mx-auto" />
            </div>
          </div>
          <div class="px-6 pb-6 pt-2">
            <button @click="showQrisModal = false" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors">Tutup</button>
          </div>
        </div>
      </div>
    </Transition>
  </UserLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({ order: Object });
const showQrisModal = ref(false);

function fmt(p) { return Number(p).toLocaleString('id-ID'); }
function statusClass(c) { return { warning:'bg-yellow-100 text-yellow-700',info:'bg-blue-100 text-blue-700',primary:'bg-orange-100 text-orange-700',success:'bg-green-100 text-green-700',danger:'bg-red-100 text-red-700' }[c]||'bg-gray-100 text-gray-700'; }
</script>
