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
                <div>
                  <div v-if="product.is_discount_active" class="flex items-center gap-1.5">
                    <span class="text-primary font-extrabold text-sm">Rp {{ formatPrice(product.final_price) }}</span>
                    <span class="px-1.5 py-0.5 bg-red-100 text-red-600 text-[10px] font-bold rounded-md">-{{ product.discount_percent }}%</span>
                  </div>
                  <div v-if="product.is_discount_active" class="text-[11px] line-through text-gray-400">Rp {{ formatPrice(product.price) }}</div>
                  <span v-else class="text-primary font-extrabold text-sm">Rp {{ formatPrice(product.price) }}</span>
                </div>
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
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span class="text-xs text-primary font-bold">Rp {{ formatPrice(item.price) }}</span>
                  <span v-if="item.is_discount_active" class="text-[10px] line-through text-gray-400">Rp {{ formatPrice(item.original_price) }}</span>
                  <span v-if="item.is_discount_active" class="text-[9px] font-bold text-red-600 bg-red-50 px-1 rounded">-{{ item.discount_percent }}%</span>
                </div>
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
                <span>Total Belanja:</span>
                <span class="font-bold text-gray-900 text-sm">Rp {{ formatPrice(totalAmount) }}</span>
              </div>

              <!-- Fast Cash Options -->
              <div class="pt-2 border-t border-gray-200/60">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uang Diterima *</label>
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xs font-bold text-gray-400">Rp</span>
                  <input 
                    v-model.number="cashReceived" 
                    type="number" 
                    min="0" 
                    step="1000" 
                    class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-900 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
                  />
                </div>
                <div class="grid grid-cols-5 gap-1">
                  <button 
                    v-for="opt in fastCashOptions" 
                    :key="opt" 
                    @click="setCashReceived(opt)"
                    class="py-1 bg-white border border-gray-200 hover:border-primary hover:text-primary text-[10px] font-semibold text-gray-600 rounded-lg transition"
                  >
                    {{ opt / 1000 }}rb
                  </button>
                </div>
              </div>

              <!-- Change calculation -->
              <div class="pt-2 border-t border-gray-200/60 flex justify-between text-xs">
                <span class="font-semibold text-gray-700">Kembalian:</span>
                <span :class="['font-extrabold', changeAmount >= 0 ? 'text-green-600' : 'text-red-500']">
                  Rp {{ formatPrice(changeAmount) }}
                </span>
              </div>
            </div>

            <!-- Submit Button -->
            <button 
              @click="triggerConfirm" 
              :disabled="cart.length === 0 || cashReceived < totalAmount || processing"
              class="w-full py-3.5 bg-gradient-to-r from-primary to-primary-dark text-white font-bold text-sm rounded-xl shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer"
            >
              <Icon v-if="processing" icon="mdi:loading" class="animate-spin text-lg" />
              <Icon v-else icon="mdi:cash-register" class="text-lg" />
              {{ processing ? 'Memproses...' : 'Proses Bayar Tunai' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- RECEIPT MODAL -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showReceiptModal && receiptData" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden text-gray-900">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold flex items-center gap-2">
              <Icon icon="mdi:check-circle" class="text-success text-xl" /> Struk Pembayaran
            </h3>
            <button @click="closeReceiptModal" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 transition">
              <Icon icon="mdi:close" class="text-xl" />
            </button>
          </div>

          <div class="p-5 max-h-[75vh] overflow-y-auto">
            <!-- Receipt Printable Content -->
            <div id="receipt-print-area" class="bg-gray-50 border border-gray-200 rounded-2xl p-5 font-mono text-xs text-gray-800 space-y-4">
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
                      <div class="text-gray-500">
                        {{ item.qty }} x Rp {{ formatPrice(item.price) }}
                        <span v-if="item.is_discount_active" class="text-[9px] text-red-600 font-bold ml-1">(-{{ item.discount_percent }}%)</span>
                      </div>
                    </td>
                    <td class="text-right py-1 font-semibold align-bottom">Rp {{ formatPrice(item.subtotal) }}</td>
                  </tr>
                </tbody>
              </table>

              <div class="border-t border-dashed border-gray-300 my-2"></div>

              <div class="space-y-1 text-[10px]">
                <div class="flex justify-between"><span>Subtotal:</span><span>Rp {{ formatPrice(receiptData.items.reduce((sum, item) => sum + (Number(item.original_price || item.price) * item.qty), 0)) }}</span></div>
                <div v-if="receiptData.items.some(item => Number(item.original_price || item.price) > Number(item.price))" class="flex justify-between text-red-600 font-semibold">
                  <span>Total Diskon:</span>
                  <span>-Rp {{ formatPrice(receiptData.items.reduce((sum, item) => sum + (Number(item.original_price || item.price) - Number(item.price)) * item.qty, 0)) }}</span>
                </div>
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
                class="py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition cursor-pointer"
              >
                <Icon icon="mdi:printer" /> Cetak Struk
              </button>
              <button 
                @click="closeReceiptModal" 
                class="py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition hover:shadow-lg hover:shadow-primary/20 cursor-pointer"
              >
                <Icon icon="mdi:refresh" /> Transaksi Baru
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- CONFIRMATION MODAL -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showConfirmModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95" appear>
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 text-primary">
              <Icon icon="mdi:help-circle-outline" class="text-3xl" />
            </div>
            <h3 class="text-lg font-bold text-gray-900">Konfirmasi Transaksi</h3>
            <p class="text-sm text-gray-500 mt-2">
              Apakah Anda yakin ingin memproses transaksi tunai ini?
            </p>
            <div class="mt-4 p-3 bg-gray-50 rounded-xl space-y-1 text-left text-xs">
              <div class="flex justify-between"><span>Pelanggan:</span><span class="font-bold text-gray-800">{{ customerName }}</span></div>
              <div class="flex justify-between"><span>Total Belanja:</span><span class="font-bold text-gray-800">Rp {{ formatPrice(totalAmount) }}</span></div>
              <div class="flex justify-between"><span>Uang Diterima:</span><span class="font-bold text-gray-800">Rp {{ formatPrice(cashReceived) }}</span></div>
              <div class="flex justify-between text-green-600 font-bold border-t border-gray-200/60 pt-1 mt-1"><span>Kembalian:</span><span>Rp {{ formatPrice(changeAmount) }}</span></div>
            </div>
            <div class="flex gap-3 mt-6">
              <button @click="showConfirmModal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer">Batal</button>
              <button @click="confirmSubmit" class="flex-1 py-2.5 bg-primary text-white text-xs font-semibold rounded-xl hover:bg-primary-dark transition shadow-md shadow-primary/20 cursor-pointer">Ya, Proses</button>
            </div>
          </div>
        </Transition>
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
  if (!searchQuery.value) return props.products || [];
  const query = searchQuery.value.toLowerCase();
  return (props.products || []).filter(p => p.name.toLowerCase().includes(query));
});

// Add a product to cashier cart
function addToCart(product) {
  if (product.stock === 0) return;
  
  const effectivePrice = product.is_discount_active ? product.final_price : product.price;

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
      original_price: product.price,
      price: effectivePrice,
      is_discount_active: product.is_discount_active,
      discount_percent: product.discount_percent,
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

const showConfirmModal = ref(false);

function triggerConfirm() {
  if (cart.value.length === 0) return;
  if (cashReceived.value < totalAmount.value) return;
  showConfirmModal.value = true;
}

function confirmSubmit() {
  showConfirmModal.value = false;
  submitTransaction();
}

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

watch(receiptData, (newReceipt) => {
  if (newReceipt) {
    showReceiptModal.value = true;
  }
}, { immediate: true });

function closeReceiptModal() {
  showReceiptModal.value = false;
  cart.value = [];
  customerName.value = 'Pelanggan Umum';
  customerPhone.value = '';
  cashReceived.value = 0;
}

function printReceipt() {
  const printElement = document.getElementById('receipt-print-area');
  if (!printElement) return;

  const printWindow = window.open('', '_blank', 'width=380,height=600');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Struk Pembayaran - UD Flamboyan</title>
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
            max-width: 100px;
            height: auto;
            display: block;
            margin: 0 auto 6px auto;
          }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .font-bold, .bold { font-weight: bold; }
          .uppercase { text-transform: uppercase; }
          .border-t { border-top: 1px dashed #000; margin: 8px 0; }
          table { width: 100%; border-collapse: collapse; margin: 4px 0; }
          td, th { padding: 2px 0; vertical-align: top; font-size: 11px; }
          .flex { display: flex; justify-content: space-between; }
        </style>
      </head>
      <body>
        ${printElement.innerHTML}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 350);
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
