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

      <!-- 24 HOURS PAYMENT DEADLINE COUNTDOWN BANNER -->
      <div v-if="order.status === 'menunggu_pembayaran'" class="mb-6 p-5 bg-primary text-white rounded-2xl shadow-sm relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative z-10">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-white shrink-0">
              <Icon icon="mdi:clock-outline" class="text-2xl animate-pulse" />
            </div>
            <div>
              <h3 class="font-bold text-base">Batas Waktu Pembayaran & Upload (24 Jam)</h3>
              <p class="text-xs text-white/90 mt-0.5">Selesaikan pembayaran sebelum: <strong>{{ order.payment_due_at_formatted }}</strong></p>
            </div>
          </div>

          <!-- Live Countdown Timer -->
          <div class="bg-black/20 backdrop-blur-md px-4 py-2 rounded-xl text-center border border-white/20 w-full sm:w-auto">
            <span class="text-[10px] text-white/80 uppercase tracking-wider block font-semibold">Sisa Waktu Pembayaran:</span>
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
              <div v-for="item in order.items" :key="item.id" class="border-b border-gray-100 pb-5 last:border-0 last:pb-0">
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
                        <Icon v-for="s in 5" :key="s" icon="mdi:star" :class="s <= item.review.rating ? 'text-amber-400' : 'text-gray-300'" class="text-lg" />
                      </div>
                    </div>
                    <p class="text-sm text-gray-700 italic">"{{ item.review.comment || 'Tidak ada komentar ulasan.' }}"</p>
                  </div>

                  <!-- Case B: Form to create review -->
                  <div v-else-if="reviewForms[item.product_id]" class="space-y-3">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Berikan Ulasan Produk</p>
                    
                    <!-- Star Rating Interactive Selector -->
                    <div class="flex items-center gap-1.5">
                      <button 
                        type="button" 
                        v-for="star in 5" 
                        :key="star" 
                        @click="reviewForms[item.product_id].rating = star"
                        @mouseover="reviewForms[item.product_id].hoverRating = star"
                        @mouseleave="reviewForms[item.product_id].hoverRating = 0"
                        class="focus:outline-none transition-all duration-150 transform hover:scale-125 cursor-pointer"
                      >
                        <Icon 
                          icon="mdi:star" 
                          :class="[
                            'text-2xl', 
                            (reviewForms[item.product_id].hoverRating || reviewForms[item.product_id].rating) >= star 
                              ? 'text-amber-400 drop-shadow-sm' 
                              : 'text-gray-300'
                          ]" 
                        />
                      </button>
                      <span class="text-xs font-semibold text-amber-500 ml-2">
                        {{ ['Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'][reviewForms[item.product_id].rating - 1] }}
                      </span>
                    </div>

                    <!-- Comment input -->
                    <div>
                      <textarea 
                        v-model="reviewForms[item.product_id].comment" 
                        rows="2" 
                        placeholder="Tulis pendapat Anda tentang produk ini..."
                        class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm transition resize-none"
                      ></textarea>
                    </div>

                    <!-- Submit Review Button -->
                    <button 
                      type="button" 
                      @click="submitReview(item.product_id)"
                      class="px-4 py-2 bg-primary text-white font-semibold rounded-xl text-xs hover:shadow-lg transition-all flex items-center gap-1 w-fit cursor-pointer"
                    >
                      <Icon icon="mdi:send-outline" /> Kirim Ulasan
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-100 space-y-2 text-sm">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal Produk</span>
                <span class="font-medium text-text">Rp {{ fmt(order.subtotal || (order.total_amount - (order.shipping_cost || 0))) }}</span>
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
          <div v-if="!['dibatalkan', 'ditolak', 'menunggu_pembayaran'].includes(order.status)" class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-semibold text-text mb-3 flex items-center gap-2">
              <Icon icon="mdi:receipt-text-outline" class="text-primary text-xl" />
              Struk Pemesanan
            </h2>
            <p class="text-xs text-gray-500 mb-4">Anda dapat melihat atau mencetak struk untuk pesanan ini.</p>
            <button @click="showReceiptModal = true" class="w-full py-2.5 bg-primary/10 hover:bg-primary/20 text-primary font-semibold text-sm rounded-xl flex items-center justify-center gap-2 transition cursor-pointer">
              <Icon icon="mdi:printer-eye" class="text-lg" />
              Lihat &amp; Cetak Struk
            </button>
          </div>
        </div>

        <!-- Right Side Panel -->
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
              
              <!-- QRIS Display Interactive with Preview & Download -->
              <div v-if="order.payment_method === 'qris'" class="text-center bg-gray-50 p-4 rounded-xl border border-gray-100">
                <p class="text-xs font-semibold text-text mb-2">SCAN BARCODE QRIS BERIKUT</p>
                
                <!-- Clickable QRIS Image to Open Preview Modal -->
                <div @click="showQrisModal = true" class="relative group cursor-pointer bg-white p-2 rounded-xl inline-block border border-gray-200 shadow-sm mb-3">
                  <img src="/img/qris-barcode.png" alt="QRIS Barcode" class="w-48 h-48 object-contain mx-auto transition duration-300 group-hover:opacity-90" />
                  <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition duration-300 rounded-xl flex items-center justify-center text-white text-xs font-semibold gap-1 backdrop-blur-[1px]">
                    <Icon icon="mdi:magnify-plus-outline" class="text-lg" /> Klik Preview
                  </div>
                </div>
 
                <div class="mb-3">
                  <button type="button" @click="downloadQrisImage" class="w-full py-2.5 px-4 bg-primary text-white font-semibold text-xs rounded-xl hover:bg-primary-dark transition flex items-center justify-center gap-2 shadow-sm shadow-primary/20 cursor-pointer">
                    <Icon icon="mdi:download" class="text-base" /> Download QR
                  </button>
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

              <button type="button" @click="showPaymentModal = true" class="mt-4 w-full py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-semibold rounded-xl flex items-center justify-center gap-2 hover:shadow-lg transition-all cursor-pointer">
                <Icon icon="mdi:upload" /> Upload Bukti Pembayaran
              </button>
            </div>
          </div>
          
          <div v-if="order.status === 'menunggu_pembayaran'">
            <button @click="router.post(`/pesanan/${order.id}/batal`)" class="w-full py-2.5 bg-red-50 text-danger text-sm font-semibold rounded-xl hover:bg-red-100 transition cursor-pointer">Batalkan Pesanan</button>
          </div>
          <div v-if="order.status === 'dikirim'">
            <button @click="router.post(`/pesanan/${order.id}/selesai`)" class="w-full py-2.5 bg-green-50 text-success text-sm font-semibold rounded-xl hover:bg-green-100 transition cursor-pointer">Pesanan Diterima</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL LIGHTBOX PREVIEW QRIS -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showQrisModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm p-4" @click.self="showQrisModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95" appear>
          <div class="bg-white rounded-3xl p-6 max-w-sm w-full text-center relative shadow-2xl">
            <button @click="showQrisModal = false" class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition">
              <Icon icon="mdi:close" class="text-xl" />
            </button>
            
            <h3 class="text-base font-bold text-text mb-1">Preview QRIS Code</h3>
            <p class="text-xs text-gray-500 mb-4">UD Flamboyan - Biskuit Ikan Hulu'u</p>

            <div class="bg-white p-3 border border-gray-200 rounded-2xl shadow-inner mb-5 inline-block">
              <img src="/img/qris-barcode.png" alt="Preview QRIS Code" class="w-64 h-64 object-contain mx-auto" />
            </div>

            <div class="flex gap-2">
              <button @click="downloadQrisImage" class="flex-1 py-2.5 bg-primary text-white font-semibold text-xs rounded-xl hover:bg-primary-dark transition flex items-center justify-center gap-1.5 shadow-md shadow-primary/20 cursor-pointer">
                <Icon icon="mdi:download" class="text-base" /> Unduh QR Code
              </button>
              <button @click="showQrisModal = false" class="px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold text-xs rounded-xl hover:bg-gray-50 transition cursor-pointer">
                Tutup
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- MODAL UPLOAD BUKTI PEMBAYARAN -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" @click.self="showPaymentModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95" appear>
          <div class="bg-white rounded-3xl p-6 max-w-md w-full relative shadow-2xl">
            <button @click="showPaymentModal = false" class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition cursor-pointer">
              <Icon icon="mdi:close" class="text-xl" />
            </button>
            
            <h3 class="text-lg font-bold text-text mb-1">Upload Bukti Pembayaran</h3>
            <p class="text-xs text-gray-500 mb-4">Pesanan #{{ order.order_code }} • Total: <strong class="text-primary">Rp {{ fmt(order.total_amount) }}</strong></p>

            <form @submit.prevent="submitPaymentProof" class="space-y-3.5 text-left">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Foto Bukti Transfer / Pembayaran *</label>
                <input type="file" @change="paymentForm.proof_image = $event.target.files[0]" accept="image/*" required class="w-full text-xs file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                <p v-if="paymentForm.errors.proof_image" class="text-xs text-danger mt-1">{{ paymentForm.errors.proof_image }}</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pengirim (Opsional)</label>
                <input v-model="paymentForm.sender_name" type="text" placeholder="Nama Pengirim" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-primary focus:bg-white transition">
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Bank / E-Wallet Pengirim (Opsional)</label>
                <input v-model="paymentForm.sender_bank" type="text" placeholder="Contoh: BRI / GoPay / OVO" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-primary focus:bg-white transition">
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah Transfer</label>
                <div class="relative rounded-xl">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <span class="text-sm font-semibold text-gray-500">Rp</span>
                  </div>
                  <input v-model.number="paymentForm.amount" type="number" class="w-full pl-10 pr-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-primary focus:bg-white transition">
                </div>
              </div>

              <div class="flex gap-2 pt-2">
                <button type="button" @click="showPaymentModal = false" class="flex-1 py-2.5 border border-gray-200 text-gray-600 font-semibold text-xs rounded-xl hover:bg-gray-50 transition cursor-pointer">
                  Batal
                </button>
                <button type="submit" :disabled="paymentForm.processing" class="flex-1 py-2.5 bg-primary text-white font-semibold text-xs rounded-xl hover:bg-primary-dark transition shadow-md shadow-primary/20 cursor-pointer disabled:opacity-50">
                  {{ paymentForm.processing ? 'Mengunggah...' : 'Kirim Bukti Pembayaran' }}
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- Modal Struk Pemesanan -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showReceiptModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95" appear>
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden text-gray-900">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
              <h3 class="font-bold flex items-center gap-2">
                <Icon icon="mdi:receipt-text-outline" class="text-primary" /> Struk Pemesanan
              </h3>
              <button @click="showReceiptModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition cursor-pointer">
                <Icon icon="mdi:close" class="text-xl" />
              </button>
            </div>

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
  </UserLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({ order: Object });

const reviewForms = ref({});
const showQrisModal = ref(false);
const showPaymentModal = ref(false);
const showReceiptModal = ref(false);
const countdownText = ref('24:00:00');
let timerInterval = null;

const paymentForm = useForm({
  proof_image: null,
  sender_name: '',
  sender_bank: '',
  amount: props.order.total_amount,
});

function submitPaymentProof() {
  paymentForm.post(`/pesanan/${props.order.id}/bayar`, {
    forceFormData: true,
    onSuccess: () => {
      showPaymentModal.value = false;
      paymentForm.reset();
    },
  });
}

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

function downloadQrisImage() {
  const link = document.createElement('a');
  link.href = '/img/qris-barcode.png';
  link.download = `QRIS-UD-Flamboyan-${props.order.order_code}.png`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function fmt(p) { return Number(p).toLocaleString('id-ID'); }
function statusClass(c) { return { warning:'bg-yellow-100 text-yellow-700',info:'bg-blue-100 text-blue-700',primary:'bg-orange-100 text-orange-700',success:'bg-green-100 text-green-700',danger:'bg-red-100 text-red-700' }[c]||'bg-gray-100 text-gray-700'; }

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
      <body>${printContent}</body>
    </html>
  `);
  printWindow.document.close();
  setTimeout(() => { printWindow.print(); printWindow.close(); }, 400);
}
</script>
