<template>
  <Head title="Checkout" />
  <UserLayout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 class="text-3xl font-bold text-text mb-8">Checkout</h1>

      <form @submit.prevent="submit" class="grid lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Shipping Address Section (Shopee-like) -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-text mb-4 flex items-center gap-2">
              <Icon icon="mdi:map-marker-outline" class="text-primary text-2xl" /> Alamat Pengiriman
            </h2>

            <!-- Scenario A: User has saved addresses -->
            <div v-if="addresses.length > 0">
              <div v-if="selectedAddress" class="p-4 bg-primary/[0.01] border border-primary/20 rounded-2xl relative">
                <div class="flex items-center gap-2 mb-2">
                  <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-semibold uppercase tracking-wider">{{ selectedAddress.label }}</span>
                  <span v-if="selectedAddress.is_default" class="px-2 py-0.5 bg-primary text-white rounded text-xs font-semibold uppercase tracking-wider">Utama</span>
                </div>
                
                <h3 class="font-bold text-text text-base">
                  {{ selectedAddress.recipient_name }}
                  <span class="text-gray-400 font-normal text-sm border-l border-gray-200 pl-2">{{ selectedAddress.phone }}</span>
                </h3>
                <p class="text-sm text-gray-600 mt-1">{{ selectedAddress.address }}</p>
                <p class="text-xs text-gray-400 mt-1">
                  {{ [selectedAddress.village, selectedAddress.district, selectedAddress.city, selectedAddress.province, selectedAddress.postal_code].filter(Boolean).join(', ') }}
                </p>

                <div class="mt-4 pt-3 border-t border-gray-50 flex justify-end">
                  <button type="button" @click="showAddressListModal = true" class="text-sm text-primary font-semibold hover:underline flex items-center gap-1">
                    <Icon icon="mdi:arrow-swap" /> Pilih Alamat Lain
                  </button>
                </div>
              </div>
              <div v-else class="text-center py-6">
                <button type="button" @click="showAddressListModal = true" class="px-4 py-2 bg-primary text-white font-semibold rounded-xl text-sm transition-all">
                  Pilih Alamat Pengiriman
                </button>
              </div>
            </div>

            <!-- Scenario B: User has no saved addresses (Fill manually or create) -->
            <div v-else class="space-y-4">
              <div class="p-4 bg-yellow-50 text-yellow-800 rounded-xl text-sm flex items-start gap-2 mb-4">
                <Icon icon="mdi:alert-circle" class="text-lg shrink-0 mt-0.5" />
                <div>
                  <p class="font-semibold">Anda belum memiliki alamat tersimpan.</p>
                  <p class="text-xs text-yellow-700 mt-0.5">Silakan isi form di bawah atau <button type="button" @click="openNewAddressModal" class="underline font-semibold hover:text-primary">tambah alamat baru ke profil</button> agar tidak perlu mengisi lagi nanti.</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima *</label>
                  <input v-model="form.shipping_name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">No. HP *</label>
                  <input v-model="form.shipping_phone" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="08xxxxxxxxxx">
                </div>
                <div class="sm:col-span-2 relative">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Alamat (Cari Provinsi, Kota/Kab, Kecamatan, Kode Pos) *</label>
                  <div class="relative">
                    <input 
                      type="text" 
                      v-model="manualSearchQuery" 
                      @input="searchManualAddress" 
                      @focus="manualShowDropdown = true"
                      @blur="handleManualBlur"
                      required 
                      class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" 
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
                  <div v-if="manualShowDropdown && manualSearchResults.length > 0" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                    <div 
                      v-for="(result, index) in manualSearchResults" 
                      :key="index" 
                      @mousedown="selectManualLocation(result)"
                      class="px-4 py-3 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0"
                    >
                      {{ result.label }}
                    </div>
                  </div>
                  <div v-else-if="manualShowDropdown && manualSearching" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-4 text-center text-sm text-gray-500">
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap *</label>
                <textarea v-model="form.shipping_address" @input="manualAddressDetailValidationError = false" required rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none"></textarea>
                <p v-if="manualAddressDetailValidationError || form.errors.shipping_address" class="text-danger text-xs mt-1.5 font-medium flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Alamat lengkap (jalan, nomor rumah, RT/RW, dsb.) wajib diisi.
                </p>
              </div>
            </div>
            
            <!-- Notes (always shown) -->
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pesanan (Opsional)</label>
              <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none" placeholder="Catatan untuk penjual..."></textarea>
            </div>
          </div>

          <!-- Courier Selection Section (Shopee-like) -->
          <div v-if="selectedAddress || (form.shipping_address && form.shipping_city_id)" class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-text mb-4 flex items-center gap-2">
              <Icon icon="mdi:truck-delivery-outline" class="text-primary text-2xl" /> Opsi Pengiriman
            </h2>

            <div class="grid grid-cols-3 gap-3">
              <!-- Courier options -->
              <label 
                v-for="c in ['jne', 'jnt', 'sicepat', 'ninja', 'pos']" 
                :key="c" 
                :class="['flex items-center gap-2 p-3 rounded-2xl border cursor-pointer transition-all justify-center', form.courier === c ? 'border-primary bg-primary/[0.02]' : 'border-gray-200 hover:border-gray-300']"
              >
                <input type="radio" :value="c" v-model="form.courier" @change="calculateShippingCost" class="sr-only">
                <div :class="['w-4 h-4 rounded-full border flex items-center justify-center shrink-0', form.courier === c ? 'border-primary' : 'border-gray-300']">
                  <div v-if="form.courier === c" class="w-2 h-2 rounded-full bg-primary"></div>
                </div>
                <span class="font-bold text-text uppercase text-xs">{{ c }}</span>
              </label>
            </div>

            <!-- Courier Services -->
            <div v-if="loadingShipping" class="mt-6 p-4 text-center text-sm text-gray-500">
              <Icon icon="mdi:loading" class="animate-spin text-primary text-xl mx-auto mb-2" />
              Mencari layanan pengiriman...
            </div>
            
            <div v-else-if="shippingServices.length > 0" class="mt-6 space-y-3">
              <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Layanan Pengiriman:</label>
              <div class="grid grid-cols-1 gap-2">
                <label 
                  v-for="service in shippingServices" 
                  :key="service.service" 
                  :class="['flex items-center justify-between p-4 rounded-xl border cursor-pointer transition-all', form.courier_service === service.service ? 'border-primary bg-primary/[0.01]' : 'border-gray-100 hover:border-gray-200']"
                >
                  <input type="radio" :value="service.service" v-model="form.courier_service" @change="selectShippingService(service)" class="sr-only">
                  <div class="flex items-center gap-3">
                    <div :class="['w-4 h-4 rounded-full border flex items-center justify-center shrink-0', form.courier_service === service.service ? 'border-primary' : 'border-gray-300']">
                      <div v-if="form.courier_service === service.service" class="w-2 h-2 rounded-full bg-primary"></div>
                    </div>
                    <div>
                      <span class="font-semibold text-text text-sm">{{ service.service }}</span>
                      <span class="text-xs text-gray-400 block">{{ service.description }} (Estimasi: {{ service.cost[0].etd }} hari)</span>
                    </div>
                  </div>
                  <span class="font-bold text-primary text-sm">Rp {{ formatPrice(service.cost[0].value) }}</span>
                </label>
              </div>
            </div>

            <div v-else-if="form.courier" class="mt-6 p-4 bg-yellow-50 text-yellow-800 rounded-xl text-xs">
              Tidak ada layanan pengiriman yang tersedia untuk kurir ini. Silakan pilih kurir lain atau cek kembali alamat Anda.
            </div>
          </div>

          <!-- Payment Method Selection -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-text mb-4 flex items-center gap-2">
              <Icon icon="mdi:credit-card-outline" class="text-primary text-2xl" /> Metode Pembayaran
            </h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Transfer Bank -->
              <label :class="['flex items-start gap-4 p-4 rounded-2xl border cursor-pointer transition-all', form.payment_method === 'transfer' ? 'border-primary bg-primary/[0.02]' : 'border-gray-200 hover:border-gray-300']">
                <input type="radio" value="transfer" v-model="form.payment_method" class="sr-only">
                <div :class="['w-5 h-5 rounded-full border flex items-center justify-center shrink-0 mt-0.5', form.payment_method === 'transfer' ? 'border-primary' : 'border-gray-300']">
                  <div v-if="form.payment_method === 'transfer'" class="w-2.5 h-2.5 rounded-full bg-primary"></div>
                </div>
                <div>
                  <h3 class="font-bold text-text flex items-center gap-1.5">
                    <Icon icon="mdi:bank" class="text-lg text-primary" /> Transfer Bank
                  </h3>
                  <p class="text-xs text-gray-500 mt-1">Manual Transfer via BNI, BRI, BCA, Mandiri</p>
                </div>
              </label>

              <!-- QRIS -->
              <label :class="['flex items-start gap-4 p-4 rounded-2xl border cursor-pointer transition-all', form.payment_method === 'qris' ? 'border-primary bg-primary/[0.02]' : 'border-gray-200 hover:border-gray-300']">
                <input type="radio" value="qris" v-model="form.payment_method" class="sr-only">
                <div :class="['w-5 h-5 rounded-full border flex items-center justify-center shrink-0 mt-0.5', form.payment_method === 'qris' ? 'border-primary' : 'border-gray-300']">
                  <div v-if="form.payment_method === 'qris'" class="w-2.5 h-2.5 rounded-full bg-primary"></div>
                </div>
                <div>
                  <h3 class="font-bold text-text flex items-center gap-1.5">
                    <Icon icon="mdi:qrcode-scan" class="text-lg text-primary" /> QRIS Barcode
                  </h3>
                  <p class="text-xs text-gray-500 mt-1">Scan otomatis pakai GoPay, OVO, Dana, LinkAja, ShopeePay</p>
                </div>
              </label>
            </div>

            <!-- Dynamic Payment Instructions Panel -->
            <div class="mt-6 p-4 bg-gray-50 rounded-2xl border border-gray-100 transition-all">
              <div v-if="form.payment_method === 'transfer'">
                <p class="text-sm font-semibold text-text mb-2 flex items-center gap-1">
                  <Icon icon="mdi:information-outline" class="text-primary text-lg" /> Rekening Pembayaran:
                </p>
                <ul class="text-xs text-gray-600 space-y-2">
                  <li class="p-2.5 bg-white rounded-xl border border-gray-100 flex items-center justify-between">
                    <div>
                      <span class="font-bold text-text">Bank BRI</span>
                      <p class="mt-0.5 font-mono text-gray-500">0123-4567-8901</p>
                    </div>
                    <span class="text-gray-400 text-[10px]">A.N UDF Flamboyan</span>
                  </li>
                  <li class="p-2.5 bg-white rounded-xl border border-gray-100 flex items-center justify-between">
                    <div>
                      <span class="font-bold text-text">Bank BNI</span>
                      <p class="mt-0.5 font-mono text-gray-500">9876-5432-1098</p>
                    </div>
                    <span class="text-gray-400 text-[10px]">A.N UDF Flamboyan</span>
                  </li>
                </ul>
                <p class="text-[11px] text-gray-400 mt-3">Silakan transfer sesuai nominal total. Bukti transfer di-upload setelah pesanan dibuat.</p>
              </div>
              <div v-else-if="form.payment_method === 'qris'" class="flex items-start gap-3">
                <Icon icon="mdi:qrcode-scan" class="text-3xl text-primary shrink-0 mt-0.5" />
                <div>
                  <p class="text-sm font-semibold text-text">QRIS Code Barcode</p>
                  <p class="text-xs text-gray-500 mt-1">Barcode QRIS resmi akan ditampilkan di halaman rincian pesanan segera setelah Anda menekan tombol "Buat Pesanan". Anda cukup melakukan scan untuk melakukan pembayaran instan.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Sidebar (Order Summary) -->
        <div>
          <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24 shadow-sm">
            <h2 class="text-lg font-semibold text-text mb-4">Ringkasan Pesanan</h2>
            <div class="space-y-3">
              <div v-for="item in items" :key="item.id" class="flex justify-between text-sm">
                <span class="text-gray-600 max-w-[180px] truncate">{{ item.product_name }} x{{ item.qty }}</span>
                <span class="font-medium text-text">Rp {{ formatPrice(item.subtotal) }}</span>
              </div>
            </div>
            
            <hr class="my-4 border-gray-100">
            <div class="flex justify-between text-sm text-gray-600">
              <span>Subtotal Produk</span>
              <span class="font-medium text-text">Rp {{ formatPrice(total) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mt-2">
              <span>Ongkos Kirim</span>
              <span class="font-medium text-text">
                <template v-if="shippingCost > 0">
                  Rp {{ formatPrice(shippingCost) }}
                </template>
                <template v-else-if="loadingShipping">
                  Menghitung...
                </template>
                <template v-else>
                  Rp 0
                </template>
              </span>
            </div>
            <hr class="my-4 border-gray-100">
            <div class="flex justify-between text-lg font-bold">
              <span>Total</span>
              <span class="text-primary">Rp {{ formatPrice(total + shippingCost) }}</span>
            </div>

            <!-- Error Messages Debug -->
            <div v-if="Object.keys(form.errors).length > 0" class="mt-4 p-3 bg-red-50 border border-red-100 rounded-xl text-red-700 text-xs">
              <p class="font-semibold mb-1 flex items-center gap-1">
                <Icon icon="mdi:alert-circle" /> Harap perbaiki kesalahan berikut:
              </p>
              <ul class="list-disc pl-4 space-y-0.5">
                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
              </ul>
            </div>

            <button type="submit" :disabled="form.processing" class="mt-6 w-full py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-2">
              <Icon v-if="form.processing" icon="mdi:loading" class="animate-spin" />
              <Icon v-else icon="mdi:check-circle-outline" />
              {{ form.processing ? 'Memproses...' : 'Buat Pesanan' }}
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Modal Pilihan Alamat (Shopee-like list) -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showAddressListModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-gray-100 w-full max-w-xl overflow-hidden shadow-2xl transition-all">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-text">Pilih Alamat Pengiriman</h3>
            <button @click="showAddressListModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition">
              <Icon icon="mdi:close" class="text-xl" />
            </button>
          </div>

          <div class="p-6 max-h-[400px] overflow-y-auto space-y-3">
            <div v-for="addr in addresses" :key="addr.id" 
              @click="selectAddress(addr)"
              :class="['p-4 rounded-xl border cursor-pointer transition-all text-left relative', form.address_id === addr.id ? 'border-primary bg-primary/[0.02]' : 'border-gray-200 hover:border-gray-300']">
              <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-semibold uppercase tracking-wider">{{ addr.label }}</span>
                <span v-if="addr.is_default" class="px-2 py-0.5 bg-primary text-white rounded text-[10px] font-semibold uppercase tracking-wider">Utama</span>
              </div>
              <h4 class="font-bold text-text text-sm">
                {{ addr.recipient_name }}
                <span class="text-gray-400 font-normal text-xs border-l border-gray-200 pl-2">{{ addr.phone }}</span>
              </h4>
              <p class="text-xs text-gray-600 mt-1">{{ addr.address }}</p>
              <p class="text-[10px] text-gray-400 mt-0.5">
                {{ [addr.village, addr.district, addr.city, addr.province, addr.postal_code].filter(Boolean).join(', ') }}
              </p>
              <div v-if="form.address_id === addr.id" class="absolute right-4 top-1/2 -translate-y-1/2 text-primary">
                <Icon icon="mdi:check-circle" class="text-xl" />
              </div>
            </div>
          </div>

          <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50">
            <button type="button" @click="openNewAddressModal" class="text-sm text-primary font-semibold hover:underline flex items-center gap-1">
              <Icon icon="mdi:plus" /> Tambah Alamat Baru
            </button>
            <button type="button" @click="showAddressListModal = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl text-xs transition-all">Tutup</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal Form Alamat Baru (Quick Add from Checkout) -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showNewAddressModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-gray-100 w-full max-w-xl overflow-hidden shadow-2xl transition-all">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-text">Tambah Alamat Baru</h3>
            <button @click="showNewAddressModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition">
              <Icon icon="mdi:close" class="text-xl" />
            </button>
          </div>
          
          <form @submit.prevent="submitNewAddress" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Label Alamat (Contoh: Rumah, Kantor) *</label>
                <input v-model="newAddressForm.label" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="Rumah, Kantor, dsb.">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima *</label>
                <input v-model="newAddressForm.recipient_name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Penerima *</label>
                <input v-model="newAddressForm.phone" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" placeholder="08xxxxxxxxxx">
              </div>
              <div class="col-span-2 relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat (Cari Provinsi, Kota/Kab, Kecamatan, Kode Pos) *</label>
                <div class="relative">
                  <input 
                    type="text" 
                    v-model="modalSearchQuery" 
                    @input="searchModalAddress" 
                    @focus="modalShowDropdown = true"
                    @blur="handleModalBlur"
                    required 
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" 
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
                <div v-if="modalShowDropdown && modalSearchResults.length > 0" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                  <div 
                    v-for="(result, index) in modalSearchResults" 
                    :key="index" 
                    @mousedown="selectModalLocation(result)"
                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0"
                  >
                    {{ result.label }}
                  </div>
                </div>
                <div v-else-if="modalShowDropdown && modalSearching" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-4 text-center text-sm text-gray-500">
                  <span class="flex items-center justify-center gap-2 text-xs">
                    <Icon icon="mdi:loading" class="animate-spin text-primary" /> Mencari alamat...
                  </span>
                </div>
                <p v-if="modalAddressValidationError" class="text-danger text-xs mt-1.5 font-medium flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Alamat wilayah wajib dicari dan dipilih dari hasil pencarian.
                </p>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap *</label>
                <textarea v-model="newAddressForm.address" @input="modalAddressDetailValidationError = false" required rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none"></textarea>
                <p v-if="modalAddressDetailValidationError || newAddressForm.errors.address" class="text-danger text-xs mt-1.5 font-medium flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Alamat lengkap (jalan, nomor rumah, RT/RW, dsb.) wajib diisi.
                </p>
              </div>
              <div class="col-span-2 flex items-center gap-2 py-2">
                <input type="checkbox" v-model="newAddressForm.is_default" id="checkout_is_default" class="w-4.5 h-4.5 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="checkout_is_default" class="text-sm font-medium text-gray-700 select-none cursor-pointer">Atur sebagai alamat utama</label>
              </div>

              <!-- Error Messages Debug -->
              <div v-if="Object.keys(newAddressForm.errors).length > 0" class="col-span-2 p-3 bg-red-50 border border-red-100 rounded-xl text-red-700 text-xs">
                <p class="font-semibold mb-1 flex items-center gap-1">
                  <Icon icon="mdi:alert-circle" /> Harap perbaiki kesalahan berikut:
                </p>
                <ul class="list-disc pl-4 space-y-0.5">
                  <li v-for="(error, key) in newAddressForm.errors" :key="key">{{ error }}</li>
                </ul>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
              <button type="button" @click="showNewAddressModal = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition-all">Batal</button>
              <button type="submit" :disabled="newAddressForm.processing" class="px-5 py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl text-sm hover:shadow-lg transition-all disabled:opacity-50">
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
import { Head, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({
  items: Array,
  total: Number,
  user: Object,
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
  address_id: null,
  payment_method: 'transfer',
  shipping_name: props.user?.name || '',
  shipping_phone: props.user?.phone || '',
  shipping_address: '',
  shipping_province: '',
  shipping_city: '',
  shipping_city_id: '',
  shipping_district: '',
  shipping_village: '',
  shipping_postal_code: '',
  notes: '',
  courier: '',
  courier_service: '',
  shipping_cost: 0,
});

const newAddressForm = useForm({
  label: 'Rumah',
  recipient_name: props.user?.name || '',
  phone: props.user?.phone || '',
  address: '',
  province: '',
  city: '',
  city_id: '',
  district: '',
  village: '',
  postal_code: '',
  is_default: true,
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
  form.shipping_city_id = result.city_id;
  form.shipping_district = result.district;
  form.shipping_village = result.village;
  form.shipping_postal_code = result.postal_code;
  
  manualSearchQuery.value = result.label;
  manualShowDropdown.value = false;
  
  calculateShippingCost();
}

function clearManualAddressSelection() {
  manualSearchQuery.value = '';
  form.shipping_province = '';
  form.shipping_city = '';
  form.shipping_city_id = '';
  form.shipping_district = '';
  form.shipping_village = '';
  form.shipping_postal_code = '';
  manualSearchResults.value = [];
  shippingCost.value = 0;
  shippingServices.value = [];
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

// Shipping Cost refs & logic
const shippingCost = ref(0);
const shippingServices = ref([]);
const loadingShipping = ref(false);

const totalWeight = computed(() => {
  return props.items.reduce((sum, item) => sum + ((item.weight || 200) * item.qty), 0);
});

async function calculateShippingCost() {
  const cityId = selectedAddress.value ? selectedAddress.value.city_id : form.shipping_city_id;
  
  if (!cityId || !form.courier) {
    shippingCost.value = 0;
    shippingServices.value = [];
    return;
  }
  
  loadingShipping.value = true;
  shippingServices.value = [];
  
  try {
    const response = await axios.post('/api/shipping-cost', {
      destination_city_id: cityId,
      weight: totalWeight.value,
      courier: form.courier
    });
    
    if (response.data && response.data.length > 0) {
      shippingServices.value = response.data[0].costs || [];
      
      if (shippingServices.value.length > 0) {
        const regService = shippingServices.value.find(s => s.service === 'REG' || s.service.includes('Reguler')) || shippingServices.value[0];
        selectShippingService(regService);
      } else {
        form.courier_service = '';
        form.shipping_cost = 0;
        shippingCost.value = 0;
      }
    }
  } catch (e) {
    console.error("Gagal menghitung ongkir:", e);
  } finally {
    loadingShipping.value = false;
  }
}

function selectShippingService(service) {
  form.courier_service = service.service;
  const cost = service.cost[0]?.value || 0;
  form.shipping_cost = cost;
  shippingCost.value = cost;
}

function formatPrice(p) {
  return Number(p).toLocaleString('id-ID');
}

function selectAddress(addr) {
  selectedAddress.value = addr;
  form.address_id = addr.id;
  
  form.shipping_name = addr.recipient_name;
  form.shipping_phone = addr.phone;
  form.shipping_address = addr.address;
  form.shipping_province = addr.province;
  form.shipping_city = addr.city;
  form.shipping_city_id = addr.city_id;
  form.shipping_district = addr.district;
  form.shipping_village = addr.village;
  form.shipping_postal_code = addr.postal_code;
  
  showAddressListModal.value = false;
  calculateShippingCost();
}

function openNewAddressModal() {
  newAddressForm.reset();
  newAddressForm.recipient_name = props.user?.name || '';
  newAddressForm.phone = props.user?.phone || '';
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
    onSuccess: (page) => {
      showNewAddressModal.value = false;
      if (props.addresses.length > 0) {
        const latest = props.addresses[0];
        if (latest) {
          selectAddress(latest);
        }
      }
    }
  });
}

function submit() {
  if (!form.address_id) {
    let hasError = false;

    if (!form.shipping_city_id) {
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
      // Smooth scroll to manual address warning
      setTimeout(() => {
        const warningElement = document.querySelector('.text-danger');
        if (warningElement) {
          warningElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }, 50);
      return;
    }
  } else {
    manualAddressValidationError.value = false;
    manualAddressDetailValidationError.value = false;
  }

  console.log("Submitting checkout form...", form.data());
  form.post('/checkout', {
    onBefore: () => console.log("Inertia onBefore triggered"),
    onStart: () => console.log("Inertia onStart triggered"),
    onSuccess: (page) => console.log("Inertia onSuccess triggered", page),
    onError: (errors) => console.error("Inertia onError triggered", errors),
    onFinish: () => console.log("Inertia onFinish triggered"),
  });
}

// Initial setup to select default address
onMounted(() => {
  if (props.addresses && props.addresses.length > 0) {
    const defaultAddr = props.addresses.find(a => a.is_default) || props.addresses[0];
    if (defaultAddr) {
      selectAddress(defaultAddr);
    }
  }
});
</script>
