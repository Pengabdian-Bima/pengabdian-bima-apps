<template>
  <Head :title="`PO ${preOrder.po_code} - Admin`" />
  <AdminLayout>
    <Link href="/admin/pre-orders" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-4">
      <Icon icon="mdi:arrow-left" /> Kembali
    </Link>

    <div class="flex items-center justify-between mb-6">
      <div>
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold bg-primary/10 text-primary px-2 py-0.5 rounded-full flex items-center gap-1">
            <Icon icon="mdi:clipboard-list-outline" class="text-xs" /> PO
          </span>
          <h1 class="text-2xl font-bold text-text">{{ preOrder.po_code }}</h1>
        </div>
        <p class="text-sm text-gray-500 mt-1">{{ preOrder.created_at }}</p>
      </div>
      <span :class="['px-4 py-1.5 rounded-full text-sm font-medium', sc(preOrder.status_color)]">{{ preOrder.status_label }}</span>
    </div>

    <!-- Rejection reason shown to admin -->
    <div v-if="preOrder.status === 'rejected'" class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-start gap-3">
      <Icon icon="mdi:close-circle" class="text-danger text-2xl shrink-0" />
      <div>
        <h3 class="font-bold text-red-800 text-sm">Alasan Penolakan yang Dikirim ke Pelanggan</h3>
        <p class="text-xs text-red-600 mt-1">{{ preOrder.rejection_reason }}</p>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Items -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-4">Item Pre-Order</h2>
          <div class="space-y-2">
            <div v-for="item in preOrder.items" :key="item.id" class="flex justify-between py-2 border-b border-gray-50 last:border-0">
              <div>
                <p class="font-medium text-text">{{ item.product_name }}</p>
                <p class="text-sm text-gray-500">
                  {{ item.qty }} x Rp {{ fmt(item.price) }}
                  <span class="text-xs text-gray-400 border-l border-gray-200 pl-2 ml-2">
                    {{ fmt(item.weight) }}g / unit (Total: {{ fmt(item.weight * item.qty) }}g)
                  </span>
                </p>
              </div>
              <p class="font-semibold text-text">Rp {{ fmt(item.subtotal) }}</p>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal Produk</span>
              <span class="font-medium">Rp {{ fmt(preOrder.total_amount - (preOrder.shipping_cost || 0)) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Berat Total</span>
              <span class="font-medium text-text">{{ fmt(totalWeight) }} gram ({{ (totalWeight / 1000).toFixed(2) }} kg)</span>
            </div>
            <div v-if="preOrder.shipping_cost" class="flex justify-between text-gray-600">
              <span>Ongkos Kirim ({{ preOrder.courier }} {{ preOrder.courier_service }})</span>
              <span class="font-medium">Rp {{ fmt(preOrder.shipping_cost) }}</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-gray-100 font-bold text-base">
              <span>Total Tagihan</span>
              <span class="text-primary">Rp {{ fmt(preOrder.total_amount) }}</span>
            </div>
          </div>
        </div>

        <!-- Pelanggan -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-4">Info Pelanggan &amp; Pengiriman</h2>
          <div class="text-sm space-y-1.5 text-gray-600">
            <p>Pemesan: <strong>{{ preOrder.user.name }}</strong> ({{ preOrder.user.email }})</p>
            <p>HP: <strong>{{ preOrder.user.phone }}</strong></p>
            <p>Penerima: <strong>{{ preOrder.shipping_name }}</strong> — {{ preOrder.shipping_phone }}</p>
            <p>Alamat: {{ preOrder.shipping_address }}</p>
            <p class="text-xs text-gray-400">
              {{ [preOrder.shipping_village, preOrder.shipping_district, preOrder.shipping_city, preOrder.shipping_province, preOrder.shipping_postal_code].filter(Boolean).join(', ') }}
            </p>
            <div v-if="preOrder.courier" class="pt-2 border-t border-gray-50 mt-2 flex items-center justify-between text-xs">
              <span class="text-gray-500">Ekspedisi Dipilih:</span>
              <span class="font-bold text-text bg-gray-100 px-2 py-0.5 rounded">{{ preOrder.courier }} - {{ preOrder.courier_service }}</span>
            </div>
          </div>
        </div>

        <!-- Bukti Pembayaran -->
        <div v-if="preOrder.payment_proof_url" class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-4 flex items-center gap-2">
            <Icon icon="mdi:receipt-check-outline" class="text-success text-xl" /> Bukti Pembayaran Pelanggan
          </h2>
          <div class="flex flex-col sm:flex-row gap-6 items-start">
            <img :src="preOrder.payment_proof_url" alt="Bukti Pembayaran"
              class="w-full sm:w-44 h-44 object-cover rounded-xl border border-gray-100 cursor-pointer shadow hover:shadow-md transition"
              @click="previewImage = preOrder.payment_proof_url" />
            <div class="text-sm space-y-2 text-gray-600">
              <div>
                <span class="text-xs text-gray-400 block">Nama Pengirim</span>
                <span class="font-semibold text-text">{{ preOrder.payment_sender_name || '-' }}</span>
              </div>
              <div>
                <span class="text-xs text-gray-400 block">Bank / Media Transfer</span>
                <span class="font-semibold text-text">{{ preOrder.payment_sender_bank || '-' }}</span>
              </div>
              <div>
                <span class="text-xs text-gray-400 block">Jumlah yang Ditransfer</span>
                <span class="font-semibold text-primary">Rp {{ fmt(preOrder.payment_amount) }}</span>
              </div>
              <div>
                <span class="text-xs text-gray-400 block">Tanggal Transfer</span>
                <span class="font-semibold text-text">{{ preOrder.payment_date || '-' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="preOrder.notes" class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
          <h2 class="font-semibold text-amber-800 mb-2 text-sm flex items-center gap-2">
            <Icon icon="mdi:comment-text-outline" class="text-lg" /> Catatan dari Pelanggan
          </h2>
          <p class="text-sm text-amber-700 italic">{{ preOrder.notes }}</p>
        </div>
      </div>

      <div class="space-y-6">
        <!-- Admin Actions (only for pending) -->
        <div v-if="preOrder.status === 'pending'" class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-4">Tindakan Admin</h2>

          <!-- Accept Form -->
          <div class="space-y-3 mb-4">
            <label class="block text-sm font-medium text-gray-700">Estimasi Waktu Pengerjaan (hari) *</label>
            <input v-model.number="estimatedDays" type="number" min="1" max="365" placeholder="Contoh: 7"
              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm transition" />
            <button @click="acceptPO" :disabled="!estimatedDays || acceptLoading"
              class="w-full py-2.5 bg-success text-white font-semibold text-sm rounded-xl hover:bg-green-600 transition disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer">
              <Icon v-if="acceptLoading" icon="mdi:loading" class="animate-spin" />
              <Icon v-else icon="mdi:check-circle-outline" />
              {{ acceptLoading ? 'Memproses...' : 'Terima Pre-Order' }}
            </button>
          </div>

          <div class="border-t border-gray-100 pt-4">
            <button @click="showRejectModal = true"
              class="w-full py-2.5 bg-danger text-white font-semibold text-sm rounded-xl hover:bg-red-600 transition flex items-center justify-center gap-2 cursor-pointer">
              <Icon icon="mdi:close-circle-outline" />
              Tolak Pre-Order
            </button>
          </div>
        </div>

        <!-- Accepted Info -->
        <div v-if="preOrder.status === 'accepted'" class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
          <h2 class="font-semibold text-blue-800 mb-2 text-sm flex items-center gap-2">
            <Icon icon="mdi:check-circle-outline" class="text-blue-500" /> PO Telah Diterima
          </h2>
          <p class="text-xs text-blue-700">Estimasi pengerjaan: <strong>{{ preOrder.estimated_days }} hari</strong>.</p>
          <p class="text-xs text-blue-600 mt-1">Menunggu pelanggan memilih ekspedisi dan pembayaran.</p>
        </div>

        <!-- Processing / Verification Info & Actions -->
        <div v-if="preOrder.status === 'processing'" class="space-y-4">
          <div class="bg-orange-50 border border-orange-100 rounded-2xl p-5">
            <h2 class="font-semibold text-orange-800 mb-2 text-sm flex items-center gap-2">
              <Icon icon="mdi:truck-outline" class="text-orange-500" /> Sedang Diproses
            </h2>
            <p class="text-xs text-orange-700 mb-2">
              Ekspedisi: <strong>{{ preOrder.courier }} - {{ preOrder.courier_service }}</strong><br/>
              Metode Bayar: <strong class="uppercase">{{ preOrder.payment_method }}</strong>
            </p>
            <p class="text-xs text-orange-600">Konfirmasi bukti pembayaran dikirim langsung oleh pembeli via WhatsApp. Harap verifikasi lalu tandai PO selesai.</p>
          </div>

          <!-- Complete button -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <button @click="showCompleteModal = true" :disabled="completeLoading"
              class="w-full py-3 bg-success text-white font-semibold text-sm rounded-xl hover:bg-green-600 transition shadow-md shadow-green-100 flex items-center justify-center gap-2 cursor-pointer">
              <Icon v-if="completeLoading" icon="mdi:loading" class="animate-spin" />
              <Icon v-else icon="mdi:check-decagram-outline" />
              {{ completeLoading ? 'Memproses...' : 'Tandai PO Selesai' }}
            </button>
          </div>
        </div>

        <!-- Completed Info -->
        <div v-if="preOrder.status === 'completed'" class="bg-green-50 border border-green-100 rounded-2xl p-5">
          <h2 class="font-semibold text-green-800 mb-2 text-sm flex items-center gap-2">
            <Icon icon="mdi:check-decagram" class="text-green-500" /> PO Selesai
          </h2>
          <p class="text-xs text-green-700">Pre-Order telah diselesaikan dan pembayaran telah diverifikasi sepenuhnya.</p>
        </div>

        <!-- Struk Pre-Order (Processing / Completed) -->
        <div v-if="['processing', 'completed'].includes(preOrder.status)" class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-3 flex items-center gap-2">
            <Icon icon="mdi:receipt-text-outline" class="text-primary text-xl" />
            Struk Pre-Order
          </h2>
          <p class="text-xs text-gray-500 mb-4">Anda dapat melihat atau mencetak struk untuk pre-order ini.</p>
          <button @click="showReceiptModal = true" class="w-full py-2.5 bg-primary/10 hover:bg-primary/20 text-primary font-semibold text-sm rounded-xl flex items-center justify-center gap-2 transition cursor-pointer">
            <Icon icon="mdi:printer-eye" class="text-lg" />
            Lihat &amp; Cetak Struk
          </button>
        </div>
      </div>
    </div>

    <!-- Complete Confirmation Modal -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showCompleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showCompleteModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon icon="mdi:check-decagram-outline" class="text-success text-3xl" />
            </div>
            <h3 class="font-bold text-gray-900 text-lg">Selesaikan Pre-Order?</h3>
            <p class="text-sm text-gray-500 mt-2">Apakah Anda yakin ingin menyelesaikan Pre-Order <strong>{{ preOrder.po_code }}</strong> dan memverifikasi pembayaran pelanggan?</p>
            <div class="flex gap-3 mt-6">
              <button @click="showCompleteModal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer">Batal</button>
              <button @click="confirmComplete" :disabled="completeLoading"
                class="flex-1 py-2.5 bg-success text-white text-xs font-semibold rounded-xl hover:bg-green-600 transition disabled:opacity-50 flex items-center justify-center gap-1 cursor-pointer">
                <Icon v-if="completeLoading" icon="mdi:loading" class="animate-spin" />
                Ya, Selesaikan
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- Modal Struk Pre-Order -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showReceiptModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0 scale-95" appear>
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
              <h3 class="font-bold text-text flex items-center gap-2">
                <Icon icon="mdi:receipt-text-outline" class="text-primary" /> Struk Pre-Order
              </h3>
              <button @click="showReceiptModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition cursor-pointer">
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

            <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
              <button @click="showReceiptModal = false" class="flex-1 py-2.5 border border-gray-200 text-gray-600 font-semibold text-xs rounded-xl hover:bg-gray-50 transition cursor-pointer">
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

    <!-- Rejection Modal -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showRejectModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Alasan Penolakan</h3>
            <p class="text-xs text-gray-500 mb-4">Tuliskan alasan penolakan Pre-Order ini. Alasan ini akan ditampilkan kepada pelanggan.</p>
            <textarea v-model="rejectionReason" rows="4"
              placeholder="Contoh: Stok bahan tidak mencukupi saat ini / Kapasitas produksi sedang penuh / dll."
              class="w-full p-3 border border-gray-200 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none resize-none mb-4"></textarea>
            <div class="flex gap-3">
              <button @click="showRejectModal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition cursor-pointer">Batal</button>
              <button @click="rejectPO" :disabled="!rejectionReason.trim() || rejectLoading"
                class="flex-1 py-2.5 bg-danger text-white text-sm font-semibold rounded-xl hover:bg-red-600 transition disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer">
                <Icon v-if="rejectLoading" icon="mdi:loading" class="animate-spin" />
                {{ rejectLoading ? 'Menolak...' : 'Tolak PO' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- Image Preview Modal -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="previewImage" class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm p-4" @click="previewImage = null">
        <img :src="previewImage" alt="Bukti Pembayaran" class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl" />
      </div>
    </Transition>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ preOrder: Object });

const totalWeight = computed(() => {
  return props.preOrder.items.reduce((sum, item) => sum + (item.weight * item.qty), 0);
});

const estimatedDays = ref(props.preOrder.estimated_days || null);
const showRejectModal = ref(false);
const showCompleteModal = ref(false);
const showReceiptModal = ref(false);
const rejectionReason = ref('');
const acceptLoading = ref(false);
const rejectLoading = ref(false);
const completeLoading = ref(false);
const previewImage = ref(null);

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

function acceptPO() {
  if (!estimatedDays.value) return;
  acceptLoading.value = true;
  router.put(`/admin/pre-orders/${props.preOrder.id}/accept`, { estimated_days: estimatedDays.value }, {
    onFinish: () => { acceptLoading.value = false; },
  });
}

function rejectPO() {
  if (!rejectionReason.value.trim()) return;
  rejectLoading.value = true;
  router.put(`/admin/pre-orders/${props.preOrder.id}/reject`, { rejection_reason: rejectionReason.value }, {
    onSuccess: () => { showRejectModal.value = false; },
    onFinish: () => { rejectLoading.value = false; },
  });
}

function confirmComplete() {
  completeLoading.value = true;
  router.put(`/admin/pre-orders/${props.preOrder.id}/complete`, {}, {
    onSuccess: () => { showCompleteModal.value = false; },
    onFinish: () => { completeLoading.value = false; }
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
