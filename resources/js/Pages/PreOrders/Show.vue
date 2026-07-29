<template>
  <Head :title="`Pre-Order ${preOrder.po_code}`" />
  <UserLayout>
    <div class="max-w-3xl mx-auto px-4 py-10">
      <Link href="/pre-order" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-6">
        <Icon icon="mdi:arrow-left" /> Kembali ke Pre-Order Saya
      </Link>

      <!-- Header -->
      <div class="flex items-start justify-between gap-4 mb-6">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-bold bg-primary/10 text-primary px-2 py-0.5 rounded-full flex items-center gap-1">
              <Icon icon="mdi:clipboard-list-outline" class="text-xs" /> PO
            </span>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ preOrder.po_code }}</h1>
          </div>
          <p class="text-sm text-gray-500">Dibuat pada {{ preOrder.created_at }}</p>
        </div>
        <span :class="['px-4 py-1.5 rounded-full text-sm font-semibold shrink-0', sc(preOrder.status_color)]">
          {{ preOrder.status_label }}
        </span>
      </div>

      <!-- Rejection Alert -->
      <div v-if="preOrder.status === 'rejected'" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 rounded-2xl flex items-start gap-3">
        <Icon icon="mdi:close-circle" class="text-danger text-2xl shrink-0 mt-0.5" />
        <div>
          <h3 class="font-bold text-red-800 dark:text-red-300 text-sm">Pre-Order Ditolak</h3>
          <p class="text-xs text-red-600 dark:text-red-400 mt-1">Alasan: <span class="font-semibold">{{ preOrder.rejection_reason }}</span></p>
        </div>
      </div>

      <!-- Accepted — Waiting for Shipping Selection -->
      <div v-if="preOrder.status === 'accepted'" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-2xl">
        <div class="flex items-start gap-3">
          <Icon icon="mdi:check-circle" class="text-blue-500 text-2xl shrink-0 mt-0.5" />
          <div class="flex-1">
            <h3 class="font-bold text-blue-800 dark:text-blue-300 text-sm">Pre-Order Disetujui! 🎉</h3>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
              Estimasi pengerjaan: <strong>{{ preOrder.estimated_days }} hari</strong>.
              Silakan pilih ekspedisi dan metode pembayaran untuk melanjutkan.
            </p>
          </div>
        </div>
        <div class="mt-4">
          <Link :href="`/pre-order/${preOrder.id}/pengiriman`"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-semibold text-sm rounded-xl hover:bg-primary-dark transition-all shadow-md shadow-primary/20 cursor-pointer">
            <Icon icon="mdi:truck-outline" />
            Pilih Ekspedisi &amp; Lanjutkan Pembayaran
          </Link>
        </div>
      </div>

      <!-- Processing — Payment Section -->
      <div v-if="preOrder.status === 'processing'" class="mb-6 space-y-4">
        <!-- Status Info -->
        <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 rounded-2xl flex items-start gap-3">
          <Icon icon="mdi:clock-outline" class="text-orange-500 text-2xl shrink-0 mt-0.5" />
          <div>
            <h3 class="font-bold text-orange-800 dark:text-orange-300 text-sm">Menunggu Pembayaran</h3>
            <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">
              Ekspedisi: <strong>{{ preOrder.courier }} {{ preOrder.courier_service }}</strong> |
              Metode Bayar: <strong class="uppercase">{{ preOrder.payment_method }}</strong>
            </p>
          </div>
        </div>

        <!-- Payment Confirmation Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
          <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2 text-sm">
            <Icon icon="mdi:credit-card-outline" class="text-primary text-lg" />
            Informasi &amp; Konfirmasi Pembayaran
          </h2>
          
          <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 text-xs space-y-2">
            <p class="text-gray-600 dark:text-gray-300">Silakan lakukan pembayaran sebesar <strong class="text-primary">Rp {{ fmt(preOrder.total_amount) }}</strong> ke:</p>
            <div v-if="preOrder.payment_method === 'transfer'" class="font-mono text-gray-800 dark:text-gray-200 space-y-1">
              <p>• Bank BRI: <span class="font-bold select-all">1234-5678-9012-3456</span> a.n. UD Flamboyan</p>
            </div>
            <div v-else class="text-center py-2">
              <img src="/img/qris-barcode.png" alt="QRIS" class="w-44 h-44 object-contain mx-auto border border-gray-200 rounded-xl bg-white p-2 mb-1" />
              <p class="text-[10px] text-gray-400">Scan QRIS toko untuk pembayaran</p>
            </div>
          </div>

          <p class="text-xs text-gray-500">
            Setelah melakukan pembayaran, silakan kirimkan bukti transfer Anda langsung ke WhatsApp Admin UD Flamboyan dengan menekan tombol di bawah ini:
          </p>

          <a
            :href="whatsappPaymentConfirmUrl"
            target="_blank"
            class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-semibold text-sm rounded-xl flex items-center justify-center gap-2 shadow-md shadow-green-500/20 transition-all cursor-pointer"
          >
            <Icon icon="mdi:whatsapp" class="text-xl" />
            Konfirmasi Pembayaran via WhatsApp
          </a>
        </div>
      </div>

      <!-- Completed -->
      <div v-if="preOrder.status === 'completed'" class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800 rounded-2xl flex items-start gap-3">
        <Icon icon="mdi:check-decagram" class="text-success text-2xl shrink-0 mt-0.5" />
        <div>
          <h3 class="font-bold text-green-800 dark:text-green-300 text-sm">Pre-Order Selesai ✅</h3>
          <p class="text-xs text-green-600 dark:text-green-400 mt-1">
            Pembayaran telah diverifikasi dan pesanan telah diproses. Terima kasih!
          </p>
        </div>
      </div>

      <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
          <!-- Items -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Item Pre-Order</h2>
            <div class="space-y-3">
              <div v-for="item in preOrder.items" :key="item.id" class="flex justify-between py-2 border-b border-gray-50 dark:border-gray-700 last:border-0">
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ item.product_name }}</p>
                  <p class="text-sm text-gray-500">
                    {{ item.qty }} x Rp {{ fmt(item.price) }}
                    <span class="text-xs text-gray-400 border-l border-gray-200 dark:border-gray-700 pl-2 ml-2">
                      {{ fmt(item.weight) }}g / unit (Total: {{ fmt(item.weight * item.qty) }}g)
                    </span>
                  </p>
                </div>
                <p class="font-semibold text-gray-900 dark:text-white">Rp {{ fmt(item.subtotal) }}</p>
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2">
              <div class="flex justify-between font-bold text-base">
                <span class="text-gray-700 dark:text-gray-300">Total Produk</span>
                <span class="text-primary">Rp {{ fmt(preOrder.total_amount - (preOrder.shipping_cost || 0)) }}</span>
              </div>
              <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>Berat Total</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ fmt(totalWeight) }} gram ({{ (totalWeight / 1000).toFixed(2) }} kg)</span>
              </div>
              <div v-if="preOrder.shipping_cost" class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>Ongkos Kirim ({{ preOrder.courier }} {{ preOrder.courier_service }})</span>
                <span class="font-medium text-gray-900 dark:text-white">Rp {{ fmt(preOrder.shipping_cost) }}</span>
              </div>
              <div v-if="preOrder.shipping_cost" class="flex justify-between font-bold text-primary mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                <span>Total Tagihan</span>
                <span>Rp {{ fmt(preOrder.total_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="preOrder.notes" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-2xl p-5">
            <h2 class="font-semibold text-amber-800 dark:text-amber-300 mb-2 flex items-center gap-2 text-sm">
              <Icon icon="mdi:comment-text-outline" class="text-lg" /> Catatan Anda
            </h2>
            <p class="text-sm text-amber-700 dark:text-amber-400 italic">{{ preOrder.notes }}</p>
          </div>
        </div>

        <div class="space-y-6">
          <!-- Shipping Info -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm flex items-center gap-2">
              <Icon icon="mdi:map-marker-outline" class="text-primary" /> Alamat Pengiriman
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
              <p class="font-semibold text-gray-900 dark:text-white">{{ preOrder.shipping_name }}</p>
              <p>{{ preOrder.shipping_phone }}</p>
              <p>{{ preOrder.shipping_address }}</p>
              <p class="text-xs text-gray-400">
                {{ [preOrder.shipping_village, preOrder.shipping_district, preOrder.shipping_city, preOrder.shipping_province, preOrder.shipping_postal_code].filter(Boolean).join(', ') }}
              </p>
            </div>
          </div>

          <!-- Struk Pre-Order (Processing / Completed) -->
          <div v-if="['processing', 'completed'].includes(preOrder.status)" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm flex items-center gap-2">
              <Icon icon="mdi:receipt-text-outline" class="text-primary" /> Struk Pre-Order
            </h2>
            <p class="text-xs text-gray-500 mb-4">Anda dapat melihat atau mencetak struk pemesanan Anda.</p>
            <button @click="showReceiptModal = true" class="w-full py-2.5 bg-primary/10 hover:bg-primary/20 text-primary font-semibold text-xs rounded-xl flex items-center justify-center gap-2 transition cursor-pointer">
              <Icon icon="mdi:printer-eye" class="text-sm" />
              Lihat &amp; Cetak Struk
            </button>
          </div>

          <!-- Cancel button -->
          <div v-if="['pending', 'accepted'].includes(preOrder.status)">
            <button @click="confirmCancel = true" class="w-full py-2.5 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">
              Batalkan Pre-Order
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="confirmCancel" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="confirmCancel = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
          <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <Icon icon="mdi:alert-outline" class="text-danger text-3xl" />
          </div>
          <h3 class="font-bold text-gray-900 dark:text-white text-lg">Batalkan Pre-Order?</h3>
          <p class="text-sm text-gray-500 mt-2">Pre-Order <strong>{{ preOrder.po_code }}</strong> akan dibatalkan dan tidak dapat dikembalikan.</p>
          <div class="flex gap-3 mt-6">
            <button @click="confirmCancel = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer">Kembali</button>
            <button @click="cancelPO" class="flex-1 py-2.5 bg-danger text-white text-xs font-semibold rounded-xl hover:bg-red-600 transition cursor-pointer">Ya, Batalkan</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal Struk Pre-Order -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showReceiptModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0 scale-95" appear>
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden text-gray-900 dark:text-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
              <h3 class="font-bold flex items-center gap-2">
                <Icon icon="mdi:receipt-text-outline" class="text-primary" /> Struk Pre-Order
              </h3>
              <button @click="showReceiptModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 transition cursor-pointer">
                <Icon icon="mdi:close" class="text-xl" />
              </button>
            </div>

            <div class="p-5 max-h-[70vh] overflow-y-auto">
              <div id="po-receipt-print-area" class="bg-gray-50 border border-gray-200 rounded-2xl p-5 font-mono text-xs text-gray-800 space-y-4">
                <div class="text-center">
                  <div class="flex justify-center mb-2">
                    <img src="/img/logo-brand.jpeg" alt="Logo Brand" class="h-12 w-auto object-contain rounded-md mx-auto" />
                  </div>
                  <h4 class="font-bold text-sm uppercase">UD FLAMBOYAN</h4>
                  <p class="text-[10px] text-gray-500 mt-0.5">Biskuit Ikan Huluu Danau Limboto</p>
                  <p class="text-[9px] text-gray-400 mt-1">Gorontalo, Indonesia</p>
                </div>

                <div class="border-t border-dashed border-gray-300 my-2"></div>

                <div class="space-y-1 text-[10px]">
                  <div class="flex justify-between"><span>No Struk:</span><span class="font-bold">{{ preOrder.po_code }}</span></div>
                  <div class="flex justify-between"><span>Tanggal:</span><span>{{ preOrder.created_at }}</span></div>
                  <div class="flex justify-between"><span>Pelanggan:</span><span>{{ preOrder.shipping_name }}</span></div>
                </div>

                <div class="border-t border-dashed border-gray-300 my-2"></div>

                <table class="w-full text-[10px]">
                  <tbody>
                    <tr v-for="(item, idx) in preOrder.items" :key="idx">
                      <td class="py-1">
                        <div>{{ item.product_name }}</div>
                        <div class="text-gray-500">{{ item.qty }} x Rp {{ fmt(item.price) }}</div>
                      </td>
                      <td class="text-right py-1 font-semibold align-bottom">Rp {{ fmt(item.subtotal) }}</td>
                    </tr>
                  </tbody>
                </table>

                <div class="border-t border-dashed border-gray-300 my-2"></div>

                <div class="space-y-1 text-[10px]">
                  <div class="flex justify-between"><span>Subtotal:</span><span>Rp {{ fmt(preOrder.total_amount - (preOrder.shipping_cost || 0)) }}</span></div>
                  <div v-if="preOrder.shipping_cost" class="flex justify-between"><span>Ongkir:</span><span>Rp {{ fmt(preOrder.shipping_cost) }}</span></div>
                  <div class="flex justify-between font-bold"><span>Total Bayar:</span><span>Rp {{ fmt(preOrder.total_amount) }}</span></div>
                  <div class="flex justify-between"><span>Metode Bayar:</span><span class="uppercase font-semibold">{{ preOrder.payment_method }}</span></div>
                </div>

                <div class="border-t border-dashed border-gray-300 my-2"></div>

                <div class="text-center text-[9px] text-gray-400">
                  <p>Terima kasih atas kepercayaan Anda</p>
                  <p class="mt-0.5">Pre-Order Biskuit Ikan Hulu'u Gorontalo</p>
                </div>
              </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 flex gap-3">
              <button @click="showReceiptModal = false" class="flex-1 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 font-semibold text-xs rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">
                Tutup
              </button>
              <button @click="printPOReceipt" class="flex-1 py-2.5 bg-primary text-white font-semibold text-xs rounded-xl hover:bg-primary-dark transition shadow-md shadow-primary/20 cursor-pointer flex items-center justify-center gap-1.5">
                <Icon icon="mdi:printer" /> Cetak Struk
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- Image Preview Modal -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="previewImage" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" @click="previewImage = null">
        <img :src="previewImage" alt="Bukti Bayar" class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl" />
      </div>
    </Transition>
  </UserLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({ preOrder: Object });
const page = usePage();
const confirmCancel = ref(false);
const showReceiptModal = ref(false);
const previewImage = ref(null);

const storePhone = computed(() => {
  let phone = page.props.fonntePhone || '6281356578805';
  phone = phone.replace(/[^0-9]/g, '');
  if (phone.startsWith('0')) {
    phone = '62' + phone.slice(1);
  }
  return phone;
});

const totalWeight = computed(() => {
  return props.preOrder.items.reduce((sum, item) => sum + (item.weight * item.qty), 0);
});

function fmt(p) { return Number(p || 0).toLocaleString('id-ID'); }
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

const whatsappPaymentConfirmUrl = computed(() => {
  const itemsText = props.preOrder.items.map((item, index) => {
    return `${index + 1}. ${item.product_name} (${item.qty} x Rp ${fmt(item.price)} = Rp ${fmt(item.subtotal)})`;
  }).join('\n');

  const courierText = props.preOrder.courier ? `${props.preOrder.courier} ${props.preOrder.courier_service}` : '-';
  const paymentMethodText = props.preOrder.payment_method === 'qris' ? 'QRIS' : 'Transfer Bank Manual';
  const estDays = props.preOrder.estimated_days ? `${props.preOrder.estimated_days} Hari` : '-';

  const rawText = `Halo Admin UD Flamboyan, saya ingin mengonfirmasi pembayaran untuk Pre-Order saya:

*Detail Pre-Order:*
• Kode PO: #${props.preOrder.po_code}
• Pemesan: ${props.preOrder.shipping_name}
• Estimasi Pengerjaan: ${estDays}
• Metode Bayar: ${paymentMethodText}

*Rincian Produk:*
${itemsText}

*Ongkos Kirim (${courierText}):* Rp ${fmt(props.preOrder.shipping_cost || 0)}
*Total Pembayaran:* Rp ${fmt(props.preOrder.total_amount)}

Mohon untuk memproses Pre-Order saya. Terima kasih!`;

  return `https://wa.me/${storePhone.value}?text=${encodeURIComponent(rawText)}`;
});

function cancelPO() {
  router.post(`/pre-order/${props.preOrder.id}/batal`, {}, {
    onSuccess: () => { confirmCancel.value = false; }
  });
}

function printPOReceipt() {
  const printContent = document.getElementById('po-receipt-print-area').innerHTML;
  const printWindow = window.open('', '_blank', 'width=380,height=600');
  printWindow.document.write(`
    <html>
      <head>
        <title>Struk PO - ${props.preOrder.po_code}</title>
        <style>
          body { font-family: 'Courier New', Courier, monospace; font-size: 12px; padding: 10px; margin: 0; width: 300px; line-height: 1.3; }
          img { max-width: 120px; height: auto; display: block; margin: 0 auto 8px auto; object-fit: contain; border-radius: 6px; }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .bold { font-weight: bold; }
          .divider { border-top: 1px dashed #000; margin: 8px 0; }
          table { width: 100%; border-collapse: collapse; }
          td { padding: 2px 0; vertical-align: top; }
        </style>
      </head>
      <body>${printContent}</body>
    </html>
  `);
  printWindow.document.close();
  setTimeout(() => { printWindow.print(); printWindow.close(); }, 400);
}
</script>
