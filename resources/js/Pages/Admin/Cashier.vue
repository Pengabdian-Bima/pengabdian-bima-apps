<template>
  <Head title="Kasir Toko" />
  <AdminLayout>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Kasir (Penjualan Langsung)</h1>
          <p class="text-sm text-gray-500 mt-1">Transaksi penjualan offline langsung di tempat pembayaran tunai.</p>
        </div>
      </div>

      <div class="grid lg:grid-cols-12 gap-6 items-start">
        <!-- Left Side: Product Catalog (7 Columns) -->
        <div class="lg:col-span-7 space-y-4">
          <!-- Search Bar -->
          <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm flex items-center gap-3">
            <div class="relative flex-1">
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Cari produk berdasarkan nama..." 
                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
              />
              <Icon icon="mdi:magnify" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg" />
            </div>
          </div>

          <!-- Product Grid -->
          <div v-if="filteredProducts.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div 
              v-for="product in filteredProducts" 
              :key="product.id" 
              @click="addToCart(product)"
              :class="[
                'bg-white rounded-2xl border border-gray-100 p-3 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer relative group flex flex-col justify-between overflow-hidden',
                product.stock === 0 ? 'opacity-60 cursor-not-allowed' : 'hover:-translate-y-0.5'
              ]"
            >
              <div>
                <div class="aspect-square bg-gray-50 rounded-xl overflow-hidden mb-3 relative flex items-center justify-center">
                  <img 
                    v-if="product.thumbnail_url" 
                    :src="product.thumbnail_url" 
                    :alt="product.name" 
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                  <Icon v-else icon="mdi:food-croissant" class="text-4xl text-gray-300" />
                  
                  <span 
                    :class="[
                      'absolute top-2 right-2 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase',
                      product.stock > 10 ? 'bg-green-50 text-green-600' : product.stock > 0 ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600'
                    ]"
                  >
                    Stok: {{ product.stock }}
                  </span>
                </div>
                <h3 class="font-bold text-gray-800 text-sm line-clamp-2 leading-tight group-hover:text-primary transition-colors">{{ product.name }}</h3>
              </div>
              <div class="mt-3 flex items-center justify-between">
                <span class="text-primary font-extrabold text-sm">Rp {{ formatPrice(product.price) }}</span>
                <span class="p-1.5 bg-primary/10 rounded-lg text-primary hover:bg-primary hover:text-white transition-all shrink-0">
                  <Icon icon="mdi:plus" class="text-base" />
                </span>
              </div>
            </div>
          </div>
          <div v-else class="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-sm">
            <Icon icon="mdi:package-variant-remove" class="text-5xl text-gray-300 mx-auto mb-3" />
            <p class="text-gray-500 text-sm">Tidak ada produk yang cocok dengan pencarian Anda.</p>
          </div>
        </div>

        <!-- Right Side: Transaction Cart (5 Columns) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-6">
          <div class="flex items-center justify-between pb-3 border-b border-gray-50">
            <h2 class="font-bold text-gray-900 text-lg flex items-center gap-2">
              <Icon icon="mdi:cart-outline" class="text-primary text-xl" /> Keranjang Belanja
            </h2>
            <button v-if="cart.length > 0" @click="clearCart" class="text-xs text-red-500 font-semibold hover:underline flex items-center gap-1">
              <Icon icon="mdi:trash-can-outline" /> Hapus Semua
            </button>
          </div>

          <!-- Selected Items List -->
          <div v-if="cart.length > 0" class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
            <div 
              v-for="(item, index) in cart" 
              :key="item.id" 
              class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100 relative group"
            >
              <div class="w-12 h-12 bg-white rounded-lg border border-gray-100 overflow-hidden flex items-center justify-center shrink-0">
                <img v-if="item.thumbnail_url" :src="item.thumbnail_url" class="w-full h-full object-cover" />
                <Icon v-else icon="mdi:food-croissant" class="text-xl text-gray-400" />
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-bold text-gray-800 text-xs truncate">{{ item.name }}</h4>
                <p class="text-xs text-primary font-semibold mt-0.5">Rp {{ formatPrice(item.price) }}</p>
                <div class="flex items-center justify-between mt-2">
                  <!-- Qty Adjuster -->
                  <div class="flex items-center border border-gray-200 bg-white rounded-lg overflow-hidden h-7">
                    <button @click="updateQty(index, item.qty - 1)" class="px-2 text-gray-500 hover:bg-gray-100 h-full flex items-center justify-center transition">-</button>
                    <input 
                      type="number" 
                      v-model.number="item.qty" 
                      @change="updateQty(index, item.qty)" 
                      class="w-8 text-center text-xs border-none outline-none focus:ring-0 h-full p-0 font-semibold"
                    />
                    <button @click="updateQty(index, item.qty + 1)" class="px-2 text-gray-500 hover:bg-gray-100 h-full flex items-center justify-center transition">+</button>
                  </div>
                  <span class="text-xs font-bold text-gray-900">Rp {{ formatPrice(item.price * item.qty) }}</span>
                </div>
              </div>
              <!-- Delete icon -->
              <button @click="removeFromCart(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                <Icon icon="mdi:close-circle" class="text-lg" />
              </button>
            </div>
          </div>
          <div v-else class="text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
            <Icon icon="mdi:cart-arrow-down" class="text-4xl text-gray-300 mx-auto mb-2" />
            <p class="text-xs text-gray-500">Keranjang masih kosong. Pilih produk di sebelah kiri.</p>
          </div>

          <!-- Checkout Information -->
          <div class="space-y-4 pt-4 border-t border-gray-100">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Pelanggan *</label>
                <input 
                  v-model="customerName" 
                  type="text" 
                  placeholder="Nama pembeli" 
                  class="w-full px-3 py-2 rounded-xl bg-gray-50 border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-xs"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">No. HP Pelanggan</label>
                <input 
                  v-model="customerPhone" 
                  type="text" 
                  placeholder="08xxxxxxxxxx" 
                  class="w-full px-3 py-2 rounded-xl bg-gray-50 border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-xs"
                />
              </div>
            </div>

            <!-- Billing Details -->
            <div class="space-y-2 bg-gray-50 p-4 rounded-2xl border border-gray-100">
              <div class="flex justify-between text-xs text-gray-500">
                <span>Total Belanja</span>
                <span class="font-semibold text-gray-800">Rp {{ formatPrice(totalAmount) }}</span>
              </div>
              <div class="flex justify-between text-base font-bold text-gray-900 pt-1">
                <span>Total Bayar</span>
                <span class="text-primary font-black">Rp {{ formatPrice(totalAmount) }}</span>
              </div>

              <!-- Payment Inputs -->
              <div class="pt-3 border-t border-gray-200/60 mt-2 space-y-3">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Uang Diterima (Tunai) *</label>
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-500">Rp</span>
                    <input 
                      v-model.number="cashReceived" 
                      type="number" 
                      placeholder="Masukkan jumlah uang" 
                      class="w-full pl-9 pr-3 py-2 rounded-xl bg-white border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-xs font-bold"
                    />
                  </div>
                </div>

                <!-- Fast cash buttons -->
                <div class="flex flex-wrap gap-1.5">
                  <button 
                    v-for="cash in fastCashOptions" 
                    :key="cash" 
                    type="button"
                    @click="setCashReceived(cash)"
                    class="px-2 py-1 bg-white border border-gray-200 hover:border-primary hover:bg-primary/5 text-[10px] font-bold text-gray-600 rounded-lg transition"
                  >
                    Rp {{ formatPrice(cash) }}
                  </button>
                  <button 
                    type="button"
                    @click="setCashReceived(totalAmount)"
                    class="px-2 py-1 bg-primary/10 border border-primary/20 hover:bg-primary hover:text-white text-[10px] font-bold text-primary rounded-lg transition"
                  >
                    Uang Pas
                  </button>
                </div>

                <!-- Kembalian -->
                <div class="flex justify-between text-xs pt-1">
                  <span class="text-gray-500">Kembalian</span>
                  <span :class="['font-extrabold text-sm', changeAmount >= 0 ? 'text-green-600' : 'text-red-500']">
                    {{ changeAmount >= 0 ? 'Rp ' + formatPrice(changeAmount) : 'Kurang Rp ' + formatPrice(Math.abs(changeAmount)) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <button 
              @click="submitTransaction" 
              :disabled="cart.length === 0 || !customerName || cashReceived < totalAmount || processing"
              class="w-full py-3 bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-2 cursor-pointer"
            >
              <Icon v-if="processing" icon="mdi:loading" class="animate-spin" />
              <Icon v-else icon="mdi:check-circle-outline" />
              {{ processing ? 'Memproses...' : 'Proses Transaksi (Bayar Tunai)' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Receipt Modal / Printable Receipt -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showReceiptModal && receiptData" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl transition-all">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Transaksi Berhasil</h3>
            <button @click="closeReceiptModal" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 transition">
              <Icon icon="mdi:close" class="text-xl" />
            </button>
          </div>
          
          <div class="p-6">
            <!-- Thermal Receipt Design -->
            <div id="receipt-print-area" class="bg-gray-50 border border-gray-200 rounded-2xl p-5 font-mono text-xs text-gray-800 space-y-4">
              <div class="text-center">
                <h4 class="font-bold text-sm uppercase">UD FLAMBOYAN</h4>
                <p class="text-[10px] text-gray-500 mt-0.5">Biskuit Ikan Huluu Danau Limboto</p>
                <p class="text-[9px] text-gray-400 mt-1">Gorontalo, Indonesia</p>
              </div>

              <div class="border-t border-dashed border-gray-300 my-2"></div>

              <div class="space-y-1 text-[10px]">
                <div class="flex justify-between"><span>No Struk:</span><span class="font-bold">{{ receiptData.order_code }}</span></div>
                <div class="flex justify-between"><span>Tanggal:</span><span>{{ receiptData.date }}</span></div>
                <div class="flex justify-between"><span>Kasir:</span><span>{{ $page.props.auth.user?.name }}</span></div>
                <div class="flex justify-between"><span>Pelanggan:</span><span>{{ receiptData.customer_name }}</span></div>
              </div>

              <div class="border-t border-dashed border-gray-300 my-2"></div>

              <table class="w-full text-[10px]">
                <tbody>
                  <tr v-for="(item, idx) in receiptData.items" :key="idx">
                    <td class="py-1">
                      <div>{{ item.name }}</div>
                      <div class="text-gray-500">{{ item.qty }} x Rp {{ formatPrice(item.price) }}</div>
                    </td>
                    <td class="text-right py-1 font-semibold align-bottom">Rp {{ formatPrice(item.subtotal) }}</td>
                  </tr>
                </tbody>
              </table>

              <div class="border-t border-dashed border-gray-300 my-2"></div>

              <div class="space-y-1 text-[10px]">
                <div class="flex justify-between font-bold"><span>Total Belanja:</span><span>Rp {{ formatPrice(receiptData.total_amount) }}</span></div>
                <div class="flex justify-between"><span>Bayar Tunai:</span><span>Rp {{ formatPrice(receiptData.cash_received) }}</span></div>
                <div class="flex justify-between font-bold text-primary"><span>Kembalian:</span><span>Rp {{ formatPrice(receiptData.change) }}</span></div>
              </div>

              <div class="border-t border-dashed border-gray-300 my-2"></div>

              <div class="text-center text-[9px] text-gray-400">
                <p>Terima kasih atas kunjungan Anda</p>
                <p class="mt-0.5">Produk Olahan Ikan Segar Gorontalo</p>
              </div>
            </div>

            <!-- Receipt Actions -->
            <div class="grid grid-cols-2 gap-3 mt-6">
              <button 
                @click="printReceipt" 
                class="py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition"
              >
                <Icon icon="mdi:printer" /> Cetak Struk
              </button>
              <button 
                @click="closeReceiptModal" 
                class="py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition hover:shadow-lg hover:shadow-primary/20"
              >
                <Icon icon="mdi:refresh" /> Transaksi Baru
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  products: Array,
});

const page = usePage();

const searchQuery = ref('');
const customerName = ref('Pelanggan Umum');
const customerPhone = ref('');
const cashReceived = ref(0);
const cart = ref([]);
const processing = ref(false);

const fastCashOptions = [10000, 20000, 50000, 100000, 200000];

// Filter products based on search query
const filteredProducts = computed(() => {
  if (!searchQuery.value.trim()) return props.products;
  return props.products.filter(p => 
    p.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

// Add a product to cashier cart
function addToCart(product) {
  if (product.stock === 0) return;
  
  const existingIdx = cart.value.findIndex(item => item.id === product.id);
  if (existingIdx !== -1) {
    if (cart.value[existingIdx].qty < product.stock) {
      cart.value[existingIdx].qty++;
    } else {
      alert(`Stok untuk ${product.name} tidak mencukupi.`);
    }
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      price: product.price,
      stock: product.stock,
      thumbnail_url: product.thumbnail_url,
      qty: 1,
    });
  }
}

// Remove a product from cashier cart
function removeFromCart(index) {
  cart.value.splice(index, 1);
}

// Clear the entire cashier cart
function clearCart() {
  cart.value = [];
}

// Update quantity of an item
function updateQty(index, newQty) {
  const item = cart.value[index];
  if (!item) return;
  
  if (newQty <= 0) {
    removeFromCart(index);
    return;
  }
  
  if (newQty > item.stock) {
    alert(`Stok hanya tersedia ${item.stock} item.`);
    item.qty = item.stock;
    return;
  }
  
  item.qty = newQty;
}

// Set fast cash amount
function setCashReceived(amount) {
  cashReceived.value = amount;
}

// Calculate totals
const totalAmount = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.qty), 0);
});

const changeAmount = computed(() => {
  return cashReceived.value - totalAmount.value;
});

// Watch totalAmount changes to automatically set cash received if needed
watch(totalAmount, (newTotal) => {
  if (cashReceived.value < newTotal) {
    cashReceived.value = newTotal;
  }
});

// Submit checkout to the backend
function submitTransaction() {
  if (cart.value.length === 0) return;
  if (cashReceived.value < totalAmount.value) return;
  
  processing.value = true;
  
  const payload = {
    customer_name: customerName.value,
    customer_phone: customerPhone.value,
    cash_received: cashReceived.value,
    items: cart.value.map(item => ({
      product_id: item.id,
      qty: item.qty,
    })),
  };
  
  router.post('/admin/cashier', payload, {
    onSuccess: () => {
      processing.value = false;
      // The receipt is passed through flash message, which will trigger the receipt modal.
    },
    onError: (errors) => {
      processing.value = false;
      const errorMsg = Object.values(errors).join('\n');
      alert(errorMsg || 'Gagal memproses transaksi kasir.');
    },
    onFinish: () => {
      processing.value = false;
    }
  });
}

// Receipt Modal Controls
const showReceiptModal = ref(false);
const receiptData = computed(() => page.props.flash?.receipt);

// Watch for receiptData flash message to open modal
watch(receiptData, (newReceipt) => {
  if (newReceipt) {
    showReceiptModal.value = true;
  }
}, { immediate: true });

function closeReceiptModal() {
  showReceiptModal.value = false;
  // Clear the cart & reset fields for the next transaction
  clearCart();
  customerName.value = 'Pelanggan Umum';
  customerPhone.value = '';
  cashReceived.value = 0;
  // Clear the flash session manually via route reload or by ignoring
  router.get('/admin/cashier', {}, { replace: true, preserveState: false });
}

function printReceipt() {
  const printContent = document.getElementById('receipt-print-area').innerHTML;
  const printWindow = window.open('', '_blank', 'width=380,height=600');
  
  printWindow.document.write(`
    <html>
      <head>
        <title>Struk Pembayaran - UD Flamboyan</title>
        <style>
          body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            padding: 10px;
            margin: 0;
            width: 300px;
            line-height: 1.3;
          }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .bold { font-weight: bold; }
          .divider { border-top: 1px dashed #000; margin: 8px 0; }
          table { width: 100%; border-collapse: collapse; }
          td { padding: 2px 0; vertical-align: top; }
          .footer { margin-top: 15px; text-align: center; font-size: 10px; }
        </style>
      </head>
      <body>
        ${printContent}
        <script>
          window.onload = function() {
            window.print();
            window.close();
          };
        <\/script>
      </body>
    </html>
  `);
  printWindow.document.close();
}

function formatPrice(price) {
  return Number(price).toLocaleString('id-ID');
}
</script>

<style scoped>
/* Remove spin buttons from number inputs */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>
