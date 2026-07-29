<template>
  <Head title="Buat Pre-Order" />
  <UserLayout>
    <div class="max-w-4xl mx-auto px-4 py-10">
      <Link href="/pre-order" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-6">
        <Icon icon="mdi:arrow-left" /> Kembali ke Pre-Order Saya
      </Link>

      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Buat Pre-Order</h1>
        <p class="text-sm text-gray-500 mt-1">Isi formulir di bawah untuk mengajukan permintaan pre-order produk.</p>
      </div>

      <form @submit.prevent="submit" class="grid lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Product Selection -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2 text-base">
              <Icon icon="mdi:package-variant-closed" class="text-primary text-xl" />
              Produk yang Dipesan
            </h2>

            <!-- Added items -->
            <div v-if="form.items.length > 0" class="space-y-3 mb-4">
              <div v-for="(item, idx) in form.items" :key="idx"
                class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                <img v-if="getProduct(item.product_id)?.thumbnail_url" :src="getProduct(item.product_id).thumbnail_url"
                  class="w-12 h-12 rounded-lg object-cover" />
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ getProduct(item.product_id)?.name }}</p>
                  <p class="text-xs text-primary font-semibold">Rp {{ fmt(getProduct(item.product_id)?.price * item.qty) }}</p>
                </div>
                <div class="flex items-center gap-2">
                  <input v-model.number="item.qty" type="number" min="1" class="w-16 text-center px-2 py-1.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm outline-none focus:border-primary text-text dark:text-white" />
                  <button type="button" @click="removeItem(idx)" class="p-1.5 text-gray-400 hover:text-danger rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition cursor-pointer">
                    <Icon icon="mdi:close" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Product picker -->
            <div class="border border-dashed border-gray-200 dark:border-gray-600 rounded-xl p-4">
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Tambah Produk</p>
              <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-auto pr-1">
                <button v-for="p in availableProducts" :key="p.id" type="button" @click="addProduct(p)"
                  class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-primary/5 border border-transparent hover:border-primary/20 transition text-left cursor-pointer">
                  <img v-if="p.thumbnail_url" :src="p.thumbnail_url" class="w-10 h-10 rounded-lg object-cover shrink-0" />
                  <div v-else class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0">
                    <Icon icon="mdi:package-variant-closed" class="text-gray-300 dark:text-gray-500" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ p.name }}</p>
                    <p class="text-xs text-primary font-semibold">Rp {{ fmt(p.price) }}</p>
                  </div>
                  <Icon icon="mdi:plus-circle-outline" class="text-primary text-xl shrink-0" />
                </button>
              </div>
              <p v-if="availableProducts.length === 0" class="text-xs text-gray-400 dark:text-gray-500 text-center py-4">Semua produk sudah ditambahkan</p>
            </div>
            <p v-if="form.errors.items" class="text-danger text-xs mt-2 font-medium flex items-center gap-1">
              <Icon icon="mdi:alert-circle" /> {{ form.errors.items }}
            </p>
          </div>

          <!-- Shipping Address (Checkout Style) -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Icon icon="mdi:map-marker-outline" class="text-primary text-2xl" /> Alamat Pengiriman
            </h2>

            <!-- Scenario A: User has saved addresses -->
            <div v-if="addresses.length > 0">
              <div v-if="selectedAddress" class="p-4 bg-primary/[0.01] dark:bg-primary/[0.02] border border-primary/20 rounded-2xl relative">
                <div class="flex items-center gap-2 mb-2">
                  <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-xs font-semibold uppercase tracking-wider">{{ selectedAddress.label }}</span>
                  <span v-if="selectedAddress.is_default" class="px-2 py-0.5 bg-primary text-white rounded text-xs font-semibold uppercase tracking-wider">Utama</span>
                </div>
                
                <h3 class="font-bold text-gray-900 dark:text-white text-base">
                  {{ selectedAddress.recipient_name }}
                  <span class="text-gray-400 dark:text-gray-500 font-normal text-sm border-l border-gray-200 dark:border-gray-700 pl-2">{{ selectedAddress.phone }}</span>
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ selectedAddress.address }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                  {{ [selectedAddress.village, selectedAddress.district, selectedAddress.city, selectedAddress.province, selectedAddress.postal_code].filter(Boolean).join(', ') }}
                </p>

                <div class="mt-4 pt-3 border-t border-gray-50 dark:border-gray-700 flex justify-end">
                  <button type="button" @click="showAddressListModal = true" class="text-sm text-primary font-semibold hover:underline flex items-center gap-1 cursor-pointer">
                    <Icon icon="mdi:arrow-swap" /> Pilih Alamat Lain
                  </button>
                </div>
              </div>
              <div v-else class="text-center py-6">
                <button type="button" @click="showAddressListModal = true" class="px-4 py-2 bg-primary text-white font-semibold rounded-xl text-sm transition-all cursor-pointer">
                  Pilih Alamat Pengiriman
                </button>
              </div>
            </div>

            <!-- Scenario B: User has no saved addresses (Fill manually or create) -->
            <div v-else class="space-y-4">
              <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300 rounded-xl text-sm flex items-start gap-2 mb-4">
                <Icon icon="mdi:alert-circle" class="text-lg shrink-0 mt-0.5" />
                <div>
                  <p class="font-semibold">Anda belum memiliki alamat tersimpan.</p>
                  <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-0.5">Silakan isi form di bawah atau <button type="button" @click="openNewAddressModal" class="underline font-semibold hover:text-primary cursor-pointer">tambah alamat baru ke profil</button> agar tidak perlu mengisi lagi nanti.</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Penerima *</label>
                  <input v-model="form.shipping_name" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-text dark:text-white">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. HP *</label>
                  <input v-model="form.shipping_phone" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-text dark:text-white" placeholder="08xxxxxxxxxx">
                </div>
                <div class="sm:col-span-2 relative">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat (Cari Provinsi, Kota/Kab, Kecamatan, Kode Pos) *</label>
                  <div class="relative">
                    <input 
                      type="text" 
                      v-model="manualSearchQuery" 
                      @input="searchManualAddress" 
                      @focus="manualShowDropdown = true"
                      @blur="handleManualBlur"
                      required 
                      class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-text dark:text-white" 
                      placeholder="Masukkan alamat (misal: Joglo, Kebon Jeruk, 11640)..."
                      autocomplete="off"
                    >
                    <!-- Clear button -->
                    <button 
                      v-if="manualSearchQuery" 
                      type="button" 
                      @click="clearManualAddressSelection" 
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                      <Icon icon="mdi:close-circle" class="text-lg" />
                    </button>
                  </div>
                  
                  <!-- Autocomplete Dropdown List -->
                  <div v-if="manualShowDropdown && manualSearchResults.length > 0" class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                    <div 
                      v-for="(result, index) in manualSearchResults" 
                      :key="index" 
                      @mousedown="selectManualLocation(result)"
                      class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-750 cursor-pointer text-sm text-gray-750 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 last:border-0"
                    >
                      {{ result.label }}
                    </div>
                  </div>
                  <div v-else-if="manualShowDropdown && manualSearching" class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg p-4 text-center text-sm text-gray-500">
                    <span class="flex items-center justify-center gap-2 text-xs">
                      <Icon icon="mdi:loading" class="animate-spin text-primary" /> Mencari alamat...
                    </span>
                  </div>
                  <p v-if="manualAddressValidationError" class="text-danger text-xs mt-1.5 font-medium flex items-center gap-1">
                    <Icon icon="mdi:alert-circle" /> Alamat wilayah pengiriman wajib dicari dan dipilih dari hasil pencarian.
                  </p>
                </div>
              </div>
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Lengkap *</label>
                <textarea v-model="form.shipping_address" @input="manualAddressDetailValidationError = false" required rows="3" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none text-text dark:text-white"></textarea>
                <p v-if="manualAddressDetailValidationError || form.errors.shipping_address" class="text-danger text-xs mt-1.5 font-medium flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Alamat lengkap (jalan, nomor rumah, RT/RW, dsb.) wajib diisi.
                </p>
              </div>
            </div>
            
            <!-- Notes (always shown) -->
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan (Opsional)</label>
              <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none text-text dark:text-white" placeholder="Butuh packing khusus, mohon konfirmasi via WA, dll..."></textarea>
            </div>
          </div>
        </div>

        <!-- Right Sidebar (Order Summary & Actions) -->
        <div>
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 sticky top-24 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ringkasan Pre-Order</h2>
            <div class="space-y-3 max-h-48 overflow-y-auto pr-1">
              <div v-for="item in form.items" :key="item.product_id" class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400 max-w-[180px] truncate block">{{ getProduct(item.product_id)?.name }} x{{ item.qty }}</span>
                <span class="font-medium text-gray-950 dark:text-white">Rp {{ fmt(getProduct(item.product_id)?.price * item.qty) }}</span>
              </div>
              <div v-if="form.items.length === 0" class="text-xs text-gray-400 dark:text-gray-500 py-2 text-center">Belum ada produk dipilih</div>
            </div>
            
            <hr class="my-4 border-gray-100 dark:border-gray-700">

            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
              <span>Subtotal Produk</span>
              <span class="font-medium text-gray-900 dark:text-white">Rp {{ fmt(totalAmount) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mt-2">
              <span>Ongkos Kirim</span>
              <span class="text-xs font-semibold text-amber-600 italic bg-amber-500/10 px-2 py-0.5 rounded">Dihitung Nanti</span>
            </div>

            <hr class="my-4 border-gray-100 dark:border-gray-700">
            
            <div class="flex justify-between text-lg font-bold">
              <span class="text-gray-900 dark:text-white">Estimasi Total</span>
              <span class="text-primary">Rp {{ fmt(totalAmount) }}</span>
            </div>

            <button type="submit" :disabled="form.processing || form.items.length === 0" class="mt-6 w-full py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer">
              <Icon v-if="form.processing" icon="mdi:loading" class="animate-spin" />
              <Icon v-else icon="mdi:send-outline" />
              {{ form.processing ? 'Memproses...' : 'Kirim Permintaan PO' }}
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Modal Pilihan Alamat (Shopee-like list) -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showAddressListModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 w-full max-w-xl overflow-hidden shadow-2xl transition-all">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-text dark:text-white">Pilih Alamat Pengiriman</h3>
            <button @click="showAddressListModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 transition cursor-pointer">
              <Icon icon="mdi:close" class="text-xl" />
            </button>
          </div>

          <div class="p-6 max-h-[400px] overflow-y-auto space-y-3">
            <div v-for="addr in addresses" :key="addr.id" 
              @click="selectAddress(addr)"
              :class="['p-4 rounded-xl border cursor-pointer transition-all text-left relative', form.address_id === addr.id ? 'border-primary bg-primary/[0.02]' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600']">
              <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-[10px] font-semibold uppercase tracking-wider">{{ addr.label }}</span>
                <span v-if="addr.is_default" class="px-2 py-0.5 bg-primary text-white rounded text-[10px] font-semibold uppercase tracking-wider">Utama</span>
              </div>
              <h4 class="font-bold text-gray-900 dark:text-white text-sm">
                {{ addr.recipient_name }}
                <span class="text-gray-400 dark:text-gray-500 font-normal text-xs border-l border-gray-200 dark:border-gray-700 pl-2">{{ addr.phone }}</span>
              </h4>
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ addr.address }}</p>
              <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">
                {{ [addr.village, addr.district, addr.city, addr.province, addr.postal_code].filter(Boolean).join(', ') }}
              </p>
              <div v-if="form.address_id === addr.id" class="absolute right-4 top-1/2 -translate-y-1/2 text-primary">
                <Icon icon="mdi:check-circle" class="text-xl" />
              </div>
            </div>
          </div>

          <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-750">
            <button type="button" @click="openNewAddressModal" class="text-sm text-primary font-semibold hover:underline flex items-center gap-1 cursor-pointer">
              <Icon icon="mdi:plus" /> Tambah Alamat Baru
            </button>
            <button type="button" @click="showAddressListModal = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl text-xs transition-all cursor-pointer">Tutup</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal Form Alamat Baru (Quick Add) -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showNewAddressModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 w-full max-w-xl overflow-hidden shadow-2xl transition-all">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Alamat Baru</h3>
            <button @click="showNewAddressModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 transition cursor-pointer">
              <Icon icon="mdi:close" class="text-xl" />
            </button>
          </div>
          
          <form @submit.prevent="submitNewAddress" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label Alamat (Contoh: Rumah, Kantor) *</label>
                <input v-model="newAddressForm.label" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-text dark:text-white" placeholder="Rumah, Kantor, dsb.">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Penerima *</label>
                <input v-model="newAddressForm.recipient_name" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-text dark:text-white">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. HP Penerima *</label>
                <input v-model="newAddressForm.phone" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-text dark:text-white" placeholder="08xxxxxxxxxx">
              </div>
              <div class="col-span-2 relative">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat (Cari Provinsi, Kota/Kab, Kecamatan, Kode Pos) *</label>
                <div class="relative">
                  <input 
                    type="text" 
                    v-model="modalSearchQuery" 
                    @input="searchModalAddress" 
                    @focus="modalShowDropdown = true"
                    @blur="handleModalBlur"
                    required 
                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-text dark:text-white" 
                    placeholder="Masukkan alamat (misal: Joglo, Kebon Jeruk, 11640)..."
                    autocomplete="off"
                  >
                  <!-- Clear button -->
                  <button 
                    v-if="modalSearchQuery" 
                    type="button" 
                    @click="clearModalAddressSelection" 
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                  >
                    <Icon icon="mdi:close-circle" class="text-lg" />
                  </button>
                </div>
                
                <!-- Autocomplete Dropdown List -->
                <div v-if="modalShowDropdown && modalSearchResults.length > 0" class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                  <div 
                    v-for="(result, index) in modalSearchResults" 
                    :key="index" 
                    @mousedown="selectModalLocation(result)"
                    class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-750 cursor-pointer text-sm text-gray-750 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 last:border-0"
                  >
                    {{ result.label }}
                  </div>
                </div>
                <div v-else-if="modalShowDropdown && modalSearching" class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg p-4 text-center text-sm text-gray-500">
                  <span class="flex items-center justify-center gap-2 text-xs">
                    <Icon icon="mdi:loading" class="animate-spin text-primary" /> Mencari alamat...
                  </span>
                </div>
                <p v-if="modalAddressValidationError" class="text-danger text-xs mt-1.5 font-medium flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Alamat wilayah wajib dicari dan dipilih dari hasil pencarian.
                </p>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Lengkap *</label>
                <textarea v-model="newAddressForm.address" @input="modalAddressDetailValidationError = false" required rows="3" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none text-text dark:text-white"></textarea>
                <p v-if="modalAddressDetailValidationError || newAddressForm.errors.address" class="text-danger text-xs mt-1.5 font-medium flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Alamat lengkap (jalan, nomor rumah, RT/RW, dsb.) wajib diisi.
                </p>
              </div>
              <div class="col-span-2 flex items-center gap-2 py-2">
                <input type="checkbox" v-model="newAddressForm.is_default" id="preorder_is_default" class="w-4.5 h-4.5 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="preorder_is_default" class="text-sm font-medium text-gray-700 dark:text-gray-300 select-none cursor-pointer">Atur sebagai alamat utama</label>
              </div>

              <!-- Error Messages Debug -->
              <div v-if="Object.keys(newAddressForm.errors).length > 0" class="col-span-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-100 rounded-xl text-red-700 text-xs">
                <p class="font-semibold mb-1 flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Harap perbaiki kesalahan berikut:
                </p>
                <ul class="list-disc pl-4 space-y-0.5">
                  <li v-for="(error, key) in newAddressForm.errors" :key="key">{{ error }}</li>
                </ul>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
              <button type="button" @click="showNewAddressModal = false" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-750 text-gray-700 dark:text-gray-300 font-semibold rounded-xl text-sm transition-all cursor-pointer">Batal</button>
              <button type="submit" :disabled="newAddressForm.processing" class="px-5 py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl text-sm hover:shadow-lg transition-all disabled:opacity-50 cursor-pointer">
                {{ newAddressForm.processing ? 'Menyimpan...' : 'Simpan & Pilih' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </UserLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import axios from 'axios';

const props = defineProps({
  products: Array,
  addresses: {
    type: Array,
    default: () => [],
  },
});

const showAddressListModal = ref(false);
const showNewAddressModal = ref(false);
const selectedAddress = ref(null);
const manualAddressValidationError = ref(false);
const modalAddressValidationError = ref(false);
const manualAddressDetailValidationError = ref(false);
const modalAddressDetailValidationError = ref(false);

const form = useForm({
  items: [],
  notes: '',
  address_id: null,
  shipping_name: '',
  shipping_phone: '',
  shipping_address: '',
  shipping_province: '',
  shipping_city: '',
  city_id: '',
  shipping_district: '',
  shipping_village: '',
  shipping_postal_code: '',
});

const newAddressForm = useForm({
  label: 'Rumah',
  recipient_name: '',
  phone: '',
  address: '',
  province: '',
  city: '',
  city_id: '',
  district: '',
  village: '',
  postal_code: '',
  is_default: true,
});

// Initialize with default address if exists
onMounted(() => {
  if (props.addresses.length > 0) {
    const def = props.addresses.find(a => a.is_default) || props.addresses[0];
    if (def) selectAddress(def);
  }
});

// Manual address autocomplete refs
const manualSearchQuery = ref('');
const manualSearchResults = ref([]);
const manualSearching = ref(false);
const manualShowDropdown = ref(false);
let manualSearchTimeout = null;

function searchManualAddress() {
  if (manualSearchTimeout) clearTimeout(manualSearchTimeout);
  if (manualSearchQuery.value.length < 2) {
    manualSearchResults.value = [];
    return;
  }
  manualSearching.value = true;
  manualShowDropdown.value = true;
  manualSearchTimeout = setTimeout(async () => {
    try {
      const response = await axios.get('/api/locations/search', {
        params: { q: manualSearchQuery.value }
      });
      manualSearchResults.value = response.data;
    } catch (e) {
      console.error("Gagal mencari alamat manual:", e);
    } finally {
      manualSearching.value = false;
    }
  }, 300);
}

function selectManualLocation(result) {
  form.shipping_province = result.province;
  form.shipping_city = result.city;
  form.city_id = result.city_id;
  form.shipping_district = result.district;
  form.shipping_village = result.village;
  form.shipping_postal_code = result.postal_code;
  
  manualSearchQuery.value = result.label;
  manualShowDropdown.value = false;
}

function clearManualAddressSelection() {
  manualSearchQuery.value = '';
  form.shipping_province = '';
  form.shipping_city = '';
  form.city_id = '';
  form.shipping_district = '';
  form.shipping_village = '';
  form.shipping_postal_code = '';
  manualSearchResults.value = [];
}

function handleManualBlur() {
  setTimeout(() => {
    manualShowDropdown.value = false;
  }, 250);
}

// Modal address autocomplete refs
const modalSearchQuery = ref('');
const modalSearchResults = ref([]);
const modalSearching = ref(false);
const modalShowDropdown = ref(false);
let modalSearchTimeout = null;

function searchModalAddress() {
  if (modalSearchTimeout) clearTimeout(modalSearchTimeout);
  if (modalSearchQuery.value.length < 2) {
    modalSearchResults.value = [];
    return;
  }
  modalSearching.value = true;
  modalShowDropdown.value = true;
  modalSearchTimeout = setTimeout(async () => {
    try {
      const response = await axios.get('/api/locations/search', {
        params: { q: modalSearchQuery.value }
      });
      modalSearchResults.value = response.data;
    } catch (e) {
      console.error("Gagal mencari alamat modal:", e);
    } finally {
      modalSearching.value = false;
    }
  }, 300);
}

function selectModalLocation(result) {
  newAddressForm.province = result.province;
  newAddressForm.city = result.city;
  newAddressForm.city_id = result.city_id;
  newAddressForm.district = result.district;
  newAddressForm.village = result.village;
  newAddressForm.postal_code = result.postal_code;
  
  modalSearchQuery.value = result.label;
  modalShowDropdown.value = false;
}

function clearModalAddressSelection() {
  modalSearchQuery.value = '';
  newAddressForm.province = '';
  newAddressForm.city = '';
  newAddressForm.city_id = '';
  newAddressForm.district = '';
  newAddressForm.village = '';
  newAddressForm.postal_code = '';
  modalSearchResults.value = [];
}

function handleModalBlur() {
  setTimeout(() => {
    modalShowDropdown.value = false;
  }, 250);
}

function selectAddress(addr) {
  selectedAddress.value = addr;
  form.address_id = addr.id;
  
  form.shipping_name = addr.recipient_name;
  form.shipping_phone = addr.phone;
  form.shipping_address = addr.address;
  form.shipping_province = addr.province;
  form.shipping_city = addr.city;
  form.city_id = addr.city_id;
  form.shipping_district = addr.district;
  form.shipping_village = addr.village;
  form.shipping_postal_code = addr.postal_code;
  
  showAddressListModal.value = false;
}

function openNewAddressModal() {
  newAddressForm.reset();
  modalSearchQuery.value = '';
  modalSearchResults.value = [];
  modalAddressValidationError.value = false;
  modalAddressDetailValidationError.value = false;
  showAddressListModal.value = false;
  showNewAddressModal.value = true;
}

function submitNewAddress() {
  let hasError = false;

  if (!newAddressForm.city_id) {
    modalAddressValidationError.value = true;
    hasError = true;
  } else {
    modalAddressValidationError.value = false;
  }

  if (!newAddressForm.address || !newAddressForm.address.trim()) {
    modalAddressDetailValidationError.value = true;
    hasError = true;
  } else {
    modalAddressDetailValidationError.value = false;
  }

  if (hasError) {
    return;
  }

  newAddressForm.post('/alamat', {
    onSuccess: () => {
      showNewAddressModal.value = false;
      // After success, select the newly added address
      if (props.addresses.length > 0) {
        const latest = props.addresses[0];
        if (latest) {
          selectAddress(latest);
        }
      }
    }
  });
}

const availableProducts = computed(() => {
  const addedIds = form.items.map(i => i.product_id);
  return props.products.filter(p => !addedIds.includes(p.id));
});

const totalAmount = computed(() => {
  return form.items.reduce((sum, item) => {
    const product = getProduct(item.product_id);
    return sum + (product?.price || 0) * item.qty;
  }, 0);
});

function getProduct(id) {
  return props.products.find(p => p.id === id);
}

function addProduct(product) {
  form.items.push({ product_id: product.id, qty: 1 });
}

function removeItem(idx) {
  form.items.splice(idx, 1);
}

function fmt(p) {
  return Number(p || 0).toLocaleString('id-ID');
}

function submit() {
  if (!form.address_id) {
    let hasError = false;

    if (!form.city_id) {
      manualAddressValidationError.value = true;
      hasError = true;
    } else {
      manualAddressValidationError.value = false;
    }

    if (!form.shipping_address || !form.shipping_address.trim()) {
      manualAddressDetailValidationError.value = true;
      hasError = true;
    } else {
      manualAddressDetailValidationError.value = false;
    }

    if (hasError) {
      return;
    }
  }

  form.post('/pre-order');
}
</script>
