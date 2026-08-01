<template>

  <Head :title="`Pesanan ${order.order_code}`" />
  <UserLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="flex items-center justify-between mb-8">
        <div>
          <Link href="/pesanan" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-2">
            <Icon icon="mdi:arrow-left" /> Kembali
          </Link>
          <h1 class="text-2xl font-bold text-text">{{ order.order_code }}</h1>
        </div>
        <span :class="['px-4 py-1.5 rounded-full text-sm font-medium', statusClass(order.status_color)]">{{
          order.status_label }}</span>
      </div>

      <!-- 24 HOURS PAYMENT DEADLINE COUNTDOWN BANNER -->
      <div v-if="order.status === 'menunggu_pembayaran'"
        class="mb-6 p-5 bg-primary text-white rounded-2xl shadow-sm relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative z-10">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-white shrink-0">
              <Icon icon="mdi:clock-outline" class="text-2xl animate-pulse" />
            </div>
            <div>
              <h3 class="font-bold text-base">Batas Waktu Pembayaran & Upload (24 Jam)</h3>
              <p class="text-xs text-white/90 mt-0.5">Selesaikan pembayaran sebelum: <strong>{{
                order.payment_due_at_formatted }}</strong></p>
            </div>
          </div>

          <!-- Live Countdown Timer -->
          <div
            class="bg-black/20 backdrop-blur-md px-4 py-2 rounded-xl text-center border border-white/20 w-full sm:w-auto">
            <span class="text-[10px] text-white/80 uppercase tracking-wider block font-semibold">Sisa Waktu
              Pembayaran:</span>
            <span class="text-xl font-bold text-white tracking-wider">{{ countdownText }}</span>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left 2 Columns -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Items List with reviews conditional -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-semibold text-text mb-4">Item Pesanan</h2>
            <div class="space-y-6">
              <div v-for="item in order.items" :key="item.id"
                class="border-b border-gray-100 pb-5 last:border-0 last:pb-0">
                <div class="flex justify-between items-start py-2">
                  <div>
                    <p class="font-semibold text-text text-base">{{ item.product_name }}</p>
                    <p class="text-sm text-gray-500">{{ item.qty }} x Rp {{ fmt(item.price) }}</p>
                  </div>
                  <p class="font-bold text-text">Rp {{ fmt(item.subtotal) }}</p>
                </div>

                <!-- Review Section for Completed Orders -->
                <div v-if="order.status === 'selesai'" class="mt-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                  <!-- Case A: Review exists -->
                  <div v-if="item.review" class="space-y-2">
                    <div class="flex items-center gap-2">
                      <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Ulasan Anda:</span>
                      <div class="flex items-center gap-0.5">
                        <Icon v-for="s in 5" :key="s" icon="mdi:star"
                          :class="s <= item.review.rating ? 'text-amber-400' : 'text-gray-300'" class="text-lg" />
                      </div>
                    </div>
                    <p class="text-sm text-gray-700 italic">"{{ item.review.comment || 'Tidak ada komentar ulasan.' }}"
                    </p>
                  </div>

                  <!-- Case B: Form to create review -->
                  <div v-else-if="reviewForms[item.product_id]" class="space-y-3">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Berikan Ulasan Produk</p>

                    <!-- Star Rating Interactive Selector -->
                    <div class="flex items-center gap-1.5">
                      <button type="button" v-for="star in 5" :key="star"
                        @click="reviewForms[item.product_id].rating = star"
                        @mouseover="reviewForms[item.product_id].hoverRating = star"
                        @mouseleave="reviewForms[item.product_id].hoverRating = 0"
                        class="focus:outline-none transition-all duration-150 transform hover:scale-125 cursor-pointer">
                        <Icon icon="mdi:star" :class="[
                          'text-2xl',
                          (reviewForms[item.product_id].hoverRating || reviewForms[item.product_id].rating) >= star
                            ? 'text-amber-400 drop-shadow-sm'
                            : 'text-gray-300'
                        ]" />
                      </button>
                      <span class="text-xs font-semibold text-amber-500 ml-2">
                        {{ ['Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'][reviewForms[item.product_id].rating
                        - 1] }}
                      </span>
                    </div>

                    <!-- Comment input -->
                    <div>
                      <textarea v-model="reviewForms[item.product_id].comment" rows="2"
                        placeholder="Tulis pendapat Anda tentang produk ini..."
                        class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm transition resize-none"></textarea>
                    </div>

                    <!-- Submit Review Button -->
                    <button type="button" @click="submitReview(item.product_id)"
                      class="px-4 py-2 bg-primary text-white font-semibold rounded-xl text-xs hover:shadow-lg transition-all flex items-center gap-1 w-fit cursor-pointer">
                      <Icon icon="mdi:send-outline" /> Kirim Ulasan
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 space-y-2 text-sm">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal Produk</span>
                <span class="font-medium text-text">Rp {{ fmt(order.subtotal || (order.total_amount -
                  (order.shipping_cost || 0))) }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Perkiraan Biaya Pengiriman ({{ order.courier }} {{ order.courier_service }})</span>
                <span class="font-medium text-text">Rp {{ fmt(order.shipping_cost || 0) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-gray-100 text-base font-bold">
                <span>Total Pembayaran</span>
                <span class="text-primary">Rp {{ fmt(order.total_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Shipping Address -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-semibold text-text mb-4">Pengiriman</h2>
            <div class="text-sm space-y-1 text-gray-600">
              <p><strong>{{ order.shipping_name }}</strong> ({{ order.shipping_phone }})</p>
              <p>{{ order.shipping_address }}</p>
              <p v-if="order.notes" class="mt-2 italic text-gray-500">Catatan: {{ order.notes }}</p>
            </div>
          </div>

          <!-- Struk Pemesanan (Kasir Offline & Online) -->
          <div v-if="!['dibatalkan', 'ditolak', 'menunggu_pembayaran'].includes(order.status)"
            class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-semibold text-text mb-3 flex items-center gap-2">
              <Icon icon="mdi:receipt-text-outline" class="text-primary text-xl" />
              Struk Pemesanan
            </h2>
            <p class="text-xs text-gray-500 mb-4">Anda dapat melihat atau mencetak struk untuk pesanan ini.</p>
            <button @click="showReceiptModal = true"
              class="w-full py-2.5 bg-primary/10 hover:bg-primary/20 text-primary font-semibold text-sm rounded-xl flex items-center justify-center gap-2 transition cursor-pointer">
              <Icon icon="mdi:printer-eye" class="text-lg" />
              Lihat &amp; Cetak Struk
            </button>
          </div>

          <!-- Hubungi Admin via WhatsApp -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-semibold text-text mb-2 flex items-center gap-2">
              <Icon icon="mdi:whatsapp" class="text-green-500 text-xl" />
              Bantuan &amp; Layanan
            </h2>
            <p class="text-xs text-gray-500 mb-4">Butuh bantuan atau ingin menanyakan status pesanan ini?</p>
            <a :href="whatsappAdminUrl" target="_blank"
              class="w-full py-2.5 bg-green-500 hover:bg-green-600 text-white font-semibold text-sm rounded-xl flex items-center justify-center gap-2 transition shadow-md shadow-green-500/20 cursor-pointer">
              <Icon icon="mdi:whatsapp" class="text-lg" />
              Chat Admin via WhatsApp
            </a>
          </div>
        </div>

        <div class="">
          <div v-if="order.status === 'menunggu_pembayaran'"
            class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm mb-4">
            <h2 class="font-semibold text-text mb-2 flex items-center gap-2">
              <Icon icon="mdi:close-circle-outline" class="text-red-500 text-xl" />
              Batalkan Pesanan
            </h2>
            <p class="text-xs text-gray-500 mb-4">Apakah Anda ingin membatalkan pesanan ini?</p>
            <button @click="showConfirmCancelModal = true"
              class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm rounded-xl flex items-center justify-center gap-2 transition shadow-md shadow-red-500/20 cursor-pointer">
              <Icon icon="mdi:cancel" class="text-lg" />
              Batalkan Pesanan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Konfirmasi Pembatalan Pesanan Customer -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
      enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100"
      leave-to-class="opacity-0">
      <div v-if="showConfirmCancelModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        @click.self="showConfirmCancelModal = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
          <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 text-red-600">
            <Icon icon="mdi:alert-circle-outline" class="text-3xl" />
          </div>
          <h3 class="font-bold text-gray-900 text-lg">Konfirmasi Batalkan Pesanan?</h3>
          <p class="text-xs text-gray-500 mt-2">
            Apakah Anda yakin ingin membatalkan pesanan <strong>#{{ order.order_code }}</strong> ini? Stok produk akan
            otomatis dikembalikan.
          </p>
          <div class="flex gap-3 mt-6">
            <button @click="showConfirmCancelModal = false"
              class="flex-1 py-2.5 border border-gray-200 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer">Kembali</button>
            <button @click="cancelOrder" :disabled="cancelLoading"
              class="flex-1 py-2.5 bg-red-600 text-white text-xs font-semibold rounded-xl hover:bg-red-700 transition cursor-pointer flex items-center justify-center gap-1.5">
              <Icon v-if="cancelLoading" icon="mdi:loading" class="animate-spin" />
              {{ cancelLoading ? 'Memproses...' : 'Ya, Batalkan' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>



    <!-- Modal Struk Pemesanan -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
      enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100"
      leave-to-class="opacity-0">
      <div v-if="showReceiptModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        @click.self="showReceiptModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95" appear>
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden text-gray-900">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
              <h3 class="font-bold flex items-center gap-2">
                <Icon icon="mdi:receipt-text-outline" class="text-primary" /> Struk Pemesanan
              </h3>
              <button @click="showReceiptModal = false"
                class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition cursor-pointer">
                <Icon icon="mdi:close" class="text-xl" />
              </button>
            </div>

            <div class="p-5 max-h-[70vh] overflow-y-auto">
              <div id="order-receipt-print-area"
                class="bg-gray-50 border border-gray-200 rounded-2xl p-5 font-mono text-xs text-gray-800 space-y-4">
                <div class="text-center">
                  <div class="flex justify-center mb-2">
                    <img src="/img/logo-brand.jpeg" alt="Logo Brand"
                      class="h-12 w-auto object-contain rounded-md mx-auto" />
                  </div>
                  <h4 class="font-bold text-sm uppercase">UD FLAMBOYAN</h4>
                  <p class="text-[10px] text-gray-500 mt-0.5">Biskuit Ikan Huluu Danau Limboto</p>
                  <p class="text-[9px] text-gray-400 mt-1">Gorontalo, Indonesia</p>
                </div>

                <div class="border-t border-dashed border-gray-300 my-2"></div>

                <div class="space-y-1 text-[10px]">
                  <div class="flex justify-between"><span>No Struk:</span><span class="font-bold">{{ order.order_code
                      }}</span></div>
                  <div class="flex justify-between"><span>Tanggal:</span><span>{{ order.created_at }}</span></div>
                  <div class="flex justify-between"><span>Pelanggan:</span><span>{{ order.shipping_name }}</span></div>
                </div>

                <div class="border-t border-dashed border-gray-300 my-2"></div>

                <table class="w-full text-[10px]">
                  <tbody>
                    <tr v-for="(item, idx) in order.items" :key="idx">
                      <td class="py-1">
                        <div>{{ item.product_name }}</div>
                        <div class="text-gray-500">
                          {{ item.qty }} x Rp {{ fmt(item.price) }}
                          <span v-if="Number(item.original_price || item.price) > Number(item.price)" class="text-[9px] text-red-600 font-bold ml-1">
                            (Diskon -Rp {{ fmt(Number(item.original_price) - Number(item.price)) }})
                          </span>
                        </div>
                      </td>
                      <td class="text-right py-1 font-semibold align-bottom">Rp {{ fmt(item.subtotal) }}</td>
                    </tr>
                  </tbody>
                </table>

                <div class="border-t border-dashed border-gray-300 my-2"></div>

                <div class="space-y-1 text-[10px]">
                  <div class="flex justify-between"><span>Subtotal Produk:</span><span>Rp {{ fmt(order.items.reduce((sum, item) => sum + (Number(item.original_price || item.price) * item.qty), 0)) }}</span></div>
                  <div v-if="order.items.some(item => Number(item.original_price || item.price) > Number(item.price))" class="flex justify-between text-red-600 font-semibold">
                    <span>Total Diskon:</span>
                    <span>-Rp {{ fmt(order.items.reduce((sum, item) => sum + (Number(item.original_price || item.price) - Number(item.price)) * item.qty, 0)) }}</span>
                  </div>
                  <div v-if="order.shipping_cost" class="flex justify-between"><span>Ongkir ({{ order.courier }}):</span><span>Rp {{ fmt(order.shipping_cost) }}</span></div>
                  <div class="flex justify-between font-bold"><span>Total Belanja:</span><span>Rp {{ fmt(order.total_amount) }}</span></div>
                </div>

                <div class="border-t border-dashed border-gray-300 my-2"></div>

                <div class="text-center text-[9px] text-gray-400">
                  <p>Terima kasih atas kunjungan Anda</p>
                  <p class="mt-0.5">Produk Olahan Ikan Segar Gorontalo</p>
                </div>
              </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
              <button @click="showReceiptModal = false"
                class="flex-1 py-2.5 border border-gray-200 text-gray-600 font-semibold text-xs rounded-xl hover:bg-gray-50 transition cursor-pointer">
                Tutup
              </button>
              <button @click="printOrderReceipt"
                class="flex-1 py-2.5 bg-primary text-white font-semibold text-xs rounded-xl hover:bg-primary-dark transition shadow-md shadow-primary/20 cursor-pointer flex items-center justify-center gap-1.5">
                <Icon icon="mdi:printer" /> Cetak Struk
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </UserLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({ order: Object });
const page = usePage();

const reviewForms = ref({});
const showReceiptModal = ref(false);
const showConfirmCancelModal = ref(false);
const cancelLoading = ref(false);
const countdownText = ref('24:00:00');
let timerInterval = null;

function cancelOrder() {
  cancelLoading.value = true;
  router.post(`/pesanan/${props.order.id}/batal`, {}, {
    onSuccess: () => { showConfirmCancelModal.value = false; },
    onFinish: () => { cancelLoading.value = false; }
  });
}

const storePhone = computed(() => {
  let phone = page.props.fonntePhone || '6281356578805';
  phone = phone.replace(/[^0-9]/g, '');
  if (phone.startsWith('0')) {
    phone = '62' + phone.slice(1);
  }
  return phone;
});

const whatsappAdminUrl = computed(() => {
  const text = encodeURIComponent(`Halo Admin UD Flamboyan, saya ingin menanyakan tentang pesanan saya #${props.order.order_code}. Terima kasih!`);
  return `https://wa.me/${storePhone.value}?text=${text}`;
});

const whatsappPaymentConfirmUrl = computed(() => {
  const itemsText = props.order.items.map((item, index) => {
    return `${index + 1}. ${item.product_name} (${item.qty} x Rp ${fmt(item.price)} = Rp ${fmt(item.subtotal)})`;
  }).join('\n');

  const courierText = `${props.order.courier} ${props.order.courier_service}`;
  const paymentMethodText = props.order.payment_method === 'qris' ? 'QRIS' : 'Transfer Bank Manual';

  const rawText = `Halo Admin UD Flamboyan, saya ingin mengonfirmasi pembayaran untuk pesanan saya:

*Detail Pesanan:*
• Kode Pesanan: #${props.order.order_code}
• Pemesan: ${props.order.shipping_name}
• Metode Bayar: ${paymentMethodText}

*Rincian Produk:*
${itemsText}

*Ongkos Kirim (${courierText}):* Rp ${fmt(props.order.shipping_cost || 0)}
*Total Pembayaran:* Rp ${fmt(props.order.total_amount)}

Mohon untuk memproses pesanan saya. Terima kasih!`;

  return `https://wa.me/${storePhone.value}?text=${encodeURIComponent(rawText)}`;
});

onMounted(() => {
  props.order.items.forEach(item => {
    if (!item.review) {
      reviewForms.value[item.product_id] = {
        rating: 5,
        comment: '',
        hoverRating: 0,
      };
    }
  });

  if (props.order.status === 'menunggu_pembayaran' && props.order.payment_due_at) {
    updateCountdown();
    timerInterval = setInterval(updateCountdown, 1000);
  }
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});

function updateCountdown() {
  if (!props.order.payment_due_at) return;
  const now = new Date().getTime();
  const due = new Date(props.order.payment_due_at).getTime();
  const diff = due - now;

  if (diff <= 0) {
    countdownText.value = 'Waktu Pembayaran Habis';
    if (timerInterval) clearInterval(timerInterval);
    return;
  }

  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);

  const h = String(hours).padStart(2, '0');
  const m = String(minutes).padStart(2, '0');
  const s = String(seconds).padStart(2, '0');

  countdownText.value = `${h}:${m}:${s}`;
}

function fmt(p) { return Number(p).toLocaleString('id-ID'); }
function statusClass(c) { return { warning: 'bg-yellow-100 text-yellow-700', info: 'bg-blue-100 text-blue-700', primary: 'bg-orange-100 text-orange-700', success: 'bg-green-100 text-green-700', danger: 'bg-red-100 text-red-700' }[c] || 'bg-gray-100 text-gray-700'; }

function submitReview(productId) {
  const formData = reviewForms.value[productId];
  router.post('/ulasan', {
    order_id: props.order.id,
    product_id: productId,
    rating: formData.rating,
    comment: formData.comment,
  }, {
    preserveScroll: true
  });
}

function printOrderReceipt() {
  const printElement = document.getElementById('order-receipt-print-area');
  if (!printElement) return;

  const printWindow = window.open('', '_blank', 'width=380,height=600');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Struk - ${props.order.order_code}</title>
        <style>
          @page { size: auto; margin: 0mm; }
          body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            padding: 15px;
            margin: 0;
            width: 280px;
            box-sizing: border-box;
            line-height: 1.3;
          }
          img {
            max-width: 90px;
            height: auto;
            display: block;
            margin: 0 auto 6px auto;
          }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .font-bold, .bold { font-weight: bold; }
          .font-semibold { font-weight: 600; }
          .uppercase { text-transform: uppercase; }
          .my-2 { margin-top: 8px; margin-bottom: 8px; }
          .mb-2 { margin-bottom: 8px; }
          .mt-0.5 { margin-top: 2px; }
          .mt-1 { margin-top: 4px; }
          .py-1 { padding-top: 4px; padding-bottom: 4px; }
          .space-y-1 > * + * { margin-top: 4px; }
          .space-y-4 > * + * { margin-top: 12px; }
          .border-t { border-top: 1px dashed #000 !important; }
          .border-dashed { border-style: dashed !important; }
          .border-gray-300 { border-color: #000 !important; }
          .flex { display: flex !important; justify-content: space-between !important; align-items: center !important; }
          .justify-between { justify-content: space-between !important; }
          .justify-center { justify-content: center !important; }
          table { width: 100% !important; border-collapse: collapse !important; margin: 4px 0 !important; }
          td, th { padding: 3px 0 !important; vertical-align: top !important; font-size: 11px !important; }
          .align-bottom { vertical-align: bottom !important; }
          .text-primary { color: #000 !important; }
          .text-gray-500, .text-gray-400 { color: #444 !important; }
        </style>
      </head>
      <body>${printElement.innerHTML}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => { printWindow.print(); printWindow.close(); }, 350);
}
</script>
