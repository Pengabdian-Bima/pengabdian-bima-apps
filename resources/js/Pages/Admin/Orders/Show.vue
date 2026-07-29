<template>
  <Head :title="`Pesanan ${order.order_code}`" />
  <AdminLayout>
    <Link href="/admin/orders" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-4"><Icon icon="mdi:arrow-left" /> Kembali</Link>
    <div class="flex items-center justify-between mb-6">
      <div><h1 class="text-2xl font-bold text-text">{{ order.order_code }}</h1><p class="text-sm text-gray-500">{{ order.created_at }}</p></div>
      <span :class="['px-4 py-1.5 rounded-full text-sm font-medium', sc(order.status_color)]">{{ order.status_label }}</span>
    </div>

    <!-- Alert Rejection Reason -->
    <div v-if="order.status === 'ditolak'" class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-start gap-3 shadow-sm">
      <Icon icon="mdi:alert-circle" class="text-danger text-2xl shrink-0" />
      <div>
        <h3 class="font-bold text-red-800 text-sm">Pembayaran Ditolak</h3>
        <p class="text-xs text-red-600 mt-1">Alasan Penolakan: <span class="font-semibold">{{ order.rejection_reason || 'Tidak ada alasan penolakan yang ditulis.' }}</span></p>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Items -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-4">Item Pesanan</h2>
          <div class="space-y-2">
            <div v-for="item in order.items" :key="item.id" class="flex justify-between py-2 border-b border-gray-50 last:border-0">
              <div><p class="font-medium">{{ item.product_name }}</p><p class="text-sm text-gray-500">{{ item.qty }} x Rp {{ fmt(item.price) }}</p></div>
              <p class="font-semibold">Rp {{ fmt(item.subtotal) }}</p>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal Produk</span>
              <span class="font-medium text-text">Rp {{ fmt(order.subtotal || (order.total_amount - (order.shipping_cost || 0))) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Perkiraan Biaya Pengiriman ({{ order.courier }} {{ order.courier_service }})</span>
              <span class="font-medium text-text">Rp {{ fmt(order.shipping_cost || 0) }}</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-gray-100 text-base font-bold">
              <span>Total Tagihan</span>
              <span class="text-primary">Rp {{ fmt(order.total_amount) }}</span>
            </div>
          </div>
        </div>
        <!-- Pelanggan -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-4">Info Pelanggan & Pengiriman</h2>
          <div class="text-sm space-y-1.5 text-gray-600">
            <p>Pemesan: <strong>{{ order.user.name }}</strong> ({{ order.user.email }})</p>
            <p>Penerima: <strong>{{ order.shipping_name }}</strong> ({{ order.shipping_phone }})</p>
            <p class="text-gray-700">Alamat Lengkap: {{ order.shipping_address }}</p>
            <p v-if="order.shipping_province" class="text-xs text-gray-500">
              {{ [order.shipping_village, order.shipping_district, order.shipping_city, order.shipping_province, order.shipping_postal_code].filter(Boolean).join(', ') }}
            </p>
            <div class="pt-2 border-t border-gray-50 mt-2 flex items-center justify-between text-xs">
              <span class="text-gray-500">Ekspedisi Kurir:</span>
              <span class="font-bold text-text bg-gray-100 px-2 py-0.5 rounded">{{ order.courier }} - {{ order.courier_service }}</span>
            </div>
            <p v-if="order.notes" class="mt-2 italic text-xs text-amber-700 bg-amber-50 p-2 rounded-lg">Catatan: {{ order.notes }}</p>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <!-- Payment -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
          <h2 class="font-semibold text-text mb-4 flex items-center gap-1.5">
            <Icon icon="mdi:credit-card-outline" class="text-primary text-xl" /> Info Pembayaran
          </h2>
          
          <div class="mb-4 pb-4 border-b border-gray-50 text-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Metode Pilihan</span>
            <span class="font-semibold text-text mt-1 flex items-center gap-1.5">
              <Icon :icon="order.payment_method === 'qris' ? 'mdi:qrcode-scan' : 'mdi:bank'" class="text-primary text-lg" />
              {{ order.payment_method === 'qris' ? 'QRIS' : 'Transfer Bank Manual' }}
            </span>
          </div>

          <div v-if="order.payment" class="space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Pengirim:</span><span class="font-medium text-text">{{ order.payment.sender_name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Bank/Platform:</span><span class="font-medium text-text">{{ order.payment.sender_bank }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Jumlah:</span><span class="font-bold text-primary">Rp {{ fmt(order.payment.amount) }}</span></div>
            <p class="text-xs text-gray-400 mt-2">Tanggal Transfer: {{ order.payment.transfer_date }}</p>
            <div class="pt-2">
              <span class="text-xs font-semibold text-gray-400 block mb-1">Bukti Transfer:</span>
              <img v-if="order.payment.proof_image_url" :src="order.payment.proof_image_url" class="w-full rounded-xl border border-gray-100" alt="Bukti">
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">Belum ada unggahan bukti pembayaran</p>
        </div>

        <!-- Update Status -->
        <div v-if="isAdmin" class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-4">Ubah Status</h2>
          <div class="space-y-2">
            <button v-if="order.status === 'menunggu_verifikasi'" @click="updateStatus('diproses')" class="w-full py-2 bg-success text-white text-sm font-semibold rounded-xl hover:bg-green-600 transition">✓ Verifikasi & Proses</button>
            <button v-if="order.status === 'menunggu_verifikasi'" @click="openRejectionModal" class="w-full py-2 bg-danger text-white text-sm font-semibold rounded-xl hover:bg-red-600 transition">✗ Tolak Pembayaran</button>
            <button v-if="order.status === 'diproses'" @click="updateStatus('dikirim')" class="w-full py-2 bg-blue-500 text-white text-sm font-semibold rounded-xl hover:bg-blue-600 transition">📦 Tandai Dikirim</button>
            <button v-if="order.status === 'dikirim'" @click="updateStatus('selesai')" class="w-full py-2 bg-success text-white text-sm font-semibold rounded-xl hover:bg-green-600 transition">✓ Tandai Selesai</button>
            <button v-if="['menunggu_pembayaran','menunggu_verifikasi'].includes(order.status)" @click="updateStatus('dibatalkan')" class="w-full py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-300 transition">Batalkan</button>
          </div>
        </div>

        <!-- Lihat Struk (Kasir Offline) -->
        <div v-if="order.payment_method === 'tunai'" class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-text mb-3 flex items-center gap-2">
            <Icon icon="mdi:receipt-text-outline" class="text-primary text-xl" />
            Struk Kasir
          </h2>
          <p class="text-xs text-gray-500 mb-4">Pesanan ini merupakan transaksi tunai offline. Anda dapat melihat atau mencetak ulang struk kasir.</p>
          <button @click="showReceiptModal = true" class="w-full py-2.5 bg-primary/10 hover:bg-primary/20 text-primary font-semibold text-sm rounded-xl flex items-center justify-center gap-2 transition cursor-pointer">
            <Icon icon="mdi:printer-eye" class="text-lg" />
            Lihat & Cetak Struk
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Alasan Penolakan -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showRejectionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showRejectionModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Alasan Penolakan Pembayaran</h3>
            <p class="text-xs text-gray-500 mb-4">Silakan berikan alasan mengapa bukti pembayaran ini ditolak agar pembeli dapat mengetahuinya.</p>
            
            <textarea 
              v-model="rejectionReason" 
              rows="4" 
              placeholder="Contoh: Bukti transfer tidak terbaca / nominal tidak sesuai / nama pengirim berbeda"
              class="w-full p-3 border border-gray-200 rounded-xl text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none resize-none mb-4"
              required
            ></textarea>
            
            <div class="flex gap-3">
              <button 
                @click="showRejectionModal = false" 
                class="flex-1 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
              >
                Batal
              </button>
              <button 
                @click="submitRejection" 
                :disabled="!rejectionReason.trim()" 
                class="flex-1 py-2 bg-danger text-white text-sm font-semibold rounded-xl hover:bg-red-600 transition disabled:opacity-50"
              >
                Tolak Pembayaran
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- Modal Struk Kasir -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showReceiptModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95" appear>
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
              <h3 class="font-bold text-text flex items-center gap-2">
                <Icon icon="mdi:receipt-text-outline" class="text-primary" /> Struk Pemesanan
              </h3>
              <button @click="showReceiptModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition cursor-pointer">
                <Icon icon="mdi:close" class="text-xl" />
              </button>
            </div>

            <!-- Receipt Content -->
            <div class="p-5 max-h-[70vh] overflow-y-auto">
              <div id="order-receipt-print-area" class="bg-gray-50 border border-gray-200 rounded-2xl p-5 font-mono text-xs text-gray-800 space-y-4">
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
                  <div class="flex justify-between"><span>No Struk:</span><span class="font-bold">{{ order.order_code }}</span></div>
                  <div class="flex justify-between"><span>Tanggal:</span><span>{{ order.created_at }}</span></div>
                  <div class="flex justify-between"><span>Pelanggan:</span><span>{{ order.shipping_name }}</span></div>
                </div>

                <div class="border-t border-dashed border-gray-300 my-2"></div>

                <table class="w-full text-[10px]">
                  <tbody>
                    <tr v-for="(item, idx) in order.items" :key="idx">
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
                  <div class="flex justify-between font-bold"><span>Total Belanja:</span><span>Rp {{ fmt(order.total_amount) }}</span></div>
                  <div class="flex justify-between"><span>Metode Bayar:</span><span class="uppercase font-semibold">{{ order.payment_method }}</span></div>
                </div>

                <div class="border-t border-dashed border-gray-300 my-2"></div>

                <div class="text-center text-[9px] text-gray-400">
                  <p>Terima kasih atas kunjungan Anda</p>
                  <p class="mt-0.5">Produk Olahan Ikan Segar Gorontalo</p>
                </div>
              </div>
            </div>

            <!-- Modal Actions -->
            <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
              <button @click="showReceiptModal = false" class="flex-1 py-2.5 border border-gray-200 text-gray-600 font-semibold text-xs rounded-xl hover:bg-gray-50 transition cursor-pointer">
                Tutup
              </button>
              <button @click="printOrderReceipt" class="flex-1 py-2.5 bg-primary text-white font-semibold text-xs rounded-xl hover:bg-primary-dark transition shadow-md shadow-primary/20 cursor-pointer flex items-center justify-center gap-1.5">
                <Icon icon="mdi:printer" /> Cetak Struk
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ order: Object });
const page = usePage();
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');
const showRejectionModal = ref(false);
const showReceiptModal = ref(false);
const rejectionReason = ref('');

function fmt(p) { return Number(p).toLocaleString('id-ID'); }
function sc(c) { return { warning:'bg-yellow-100 text-yellow-700',info:'bg-blue-100 text-blue-700',primary:'bg-orange-100 text-orange-700',success:'bg-green-100 text-green-700',danger:'bg-red-100 text-red-700' }[c]||'bg-gray-100 text-gray-700'; }

function updateStatus(status) { 
  router.put(`/admin/orders/${props.order.id}/status`, { status }); 
}

function openRejectionModal() {
  rejectionReason.value = '';
  showRejectionModal.value = true;
}

function submitRejection() {
  if (!rejectionReason.value.trim()) return;
  router.put(`/admin/orders/${props.order.id}/status`, { 
    status: 'ditolak', 
    rejection_reason: rejectionReason.value 
  }, {
    onSuccess: () => {
      showRejectionModal.value = false;
    }
  });
}

function printOrderReceipt() {
  const printContent = document.getElementById('order-receipt-print-area').innerHTML;
  const printWindow = window.open('', '_blank', 'width=380,height=600');
  printWindow.document.write(`
    <html>
      <head>
        <title>Struk - ${props.order.order_code}</title>
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
      <body>
        ${printContent}
        <script>window.onload=function(){window.print();window.close();};<\/script>
      </body>
    </html>
  `);
  printWindow.document.close();
}
</script>
