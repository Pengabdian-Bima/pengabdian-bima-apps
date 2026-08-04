<template>
  <Head title="Beranda" />
  <UserLayout>
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition-colors duration-300">
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-primary/5 rounded-full blur-3xl"></div>
      </div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div>
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 rounded-full text-primary text-sm font-medium mb-6">
              <Icon icon="mdi:star" class="text-lg" /> Produk Unggulan Gorontalo
            </div>
            <h1 class="text-4xl lg:text-6xl font-extrabold text-text dark:text-white leading-tight">
              Biskuit Ikan
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-primary-dark">Hulu'u</span>
              <br>Danau Limboto
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-300 mt-6 leading-relaxed max-w-lg">
              Nikmati kelezatan biskuit ikan segar dari Danau Limboto. Kaya protein, berkualitas tinggi, dan dibuat dengan resep tradisional khas Gorontalo.
            </p>
            <!-- button click ke keranjang -->
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-3 sm:gap-4 mt-8">
              <Link href="/produk" class="justify-center px-3 py-3 sm:px-8 sm:py-4 bg-gradient-to-r from-primary to-primary-dark text-white text-xs sm:text-base font-semibold rounded-xl hover:shadow-xl hover:shadow-primary/30 transition-all duration-300 flex items-center gap-1.5 sm:gap-2">
                <Icon icon="mdi:shopping" class="text-lg sm:text-xl" /> Belanja Sekarang
              </Link>
              <a href="#keunggulan" class="justify-center px-3 py-3 sm:px-8 sm:py-4 bg-white dark:bg-gray-800 text-text dark:text-white text-xs sm:text-base font-semibold rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary hover:text-primary transition-all duration-300 flex items-center gap-1.5 sm:gap-2">
                <Icon icon="mdi:information-outline" class="text-lg sm:text-xl" /> Pelajari Lebih
              </a>
            </div>
          </div>
          <div class="relative hidden lg:block">
            <div class="w-[420px] h-[420px] mx-auto relative">
              <!-- Image container -->
              <div class="relative w-full h-full rounded-[2rem] overflow-hidden shadow-2xl shadow-primary/20 border-4 border-white">
                <img src="/img/maskot.png" alt="Biskuit Ikan Huluu" class="w-full h-full object-cover">
              </div>
            </div>
            <!-- Floating badge: 100% Alami -->
            <div class="absolute top-4 -right-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl dark:shadow-black/50 p-4 flex items-center gap-3 animate-bounce" style="animation-duration:3s">
              <div class="w-10 h-10 bg-success/10 rounded-xl flex items-center justify-center"><Icon icon="mdi:leaf" class="text-success text-xl" /></div>
              <div><p class="text-sm font-semibold dark:text-white">100% Alami</p><p class="text-xs text-gray-500 dark:text-gray-400">Tanpa Pengawet</p></div>
            </div>
            <!-- Floating badge: BPOM -->
            <div class="absolute bottom-8 -left-4 bg-white dark:bg-gray-800 rounded-2xl shadow-xl dark:shadow-black/50 p-4 flex items-center gap-3">
              <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center"><Icon icon="mdi:shield-check" class="text-primary text-xl" /></div>
              <div><p class="text-sm font-semibold dark:text-white">BPOM Certified</p><p class="text-xs text-gray-500 dark:text-gray-400">Aman Dikonsumsi</p></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Keunggulan -->
<!--     <section id="keunggulan" class="py-20 bg-white dark:bg-gray-900 transition-colors duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <h2 class="text-3xl font-bold text-text dark:text-white">Keunggulan Produk Kami</h2>
          <p class="text-gray-500 dark:text-gray-400 mt-3 max-w-2xl mx-auto">Biskuit Ikan Huluu Danau Limboto diproduksi dengan standar kualitas tinggi</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
          <div v-for="(feat, i) in features" :key="i" class="group p-4 sm:p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-primary/30 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300">
            <div :class="['w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl flex items-center justify-center mb-3 sm:mb-4 transition-all duration-300 group-hover:scale-110', feat.bgClass]">
              <Icon :icon="feat.icon" :class="['text-xl sm:text-2xl', feat.iconClass]" />
            </div>
            <h3 class="text-sm sm:text-lg font-semibold text-text dark:text-white mb-1 sm:mb-2">{{ feat.title }}</h3>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ feat.desc }}</p>
          </div>
        </div>
      </div>
    </section> -->

    <!-- Produk Unggulan -->
    <section class="py-20 bg-gray-50 dark:bg-gray-800 transition-colors duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h2 class="text-3xl font-bold text-text dark:text-white">Produk Unggulan</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Pilihan terbaik untuk Anda</p>
          </div>
          <Link href="/produk" class="hidden sm:flex items-center gap-1 text-primary font-semibold hover:gap-2 transition-all">
            Lihat Semua <Icon icon="mdi:arrow-right" />
          </Link>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
          <div v-for="product in products" :key="product.id" class="group bg-white dark:bg-gray-900 rounded-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl dark:hover:shadow-black/30 hover:shadow-gray-200/50 transition-all duration-300 hover:-translate-y-1 flex flex-col">
            <div class="aspect-square bg-gradient-to-br from-orange-50 to-amber-50 dark:from-gray-800 dark:to-gray-700 relative overflow-hidden flex items-center justify-center">
              <img v-if="product.thumbnail_url" :src="product.thumbnail_url" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              <Icon v-else icon="mdi:food-croissant" class="text-4xl sm:text-6xl text-primary/30" />
              <div class="absolute top-2 left-2 sm:top-3 sm:left-3">
                <span class="px-2 py-0.5 sm:px-3 sm:py-1 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm rounded-full text-[10px] sm:text-xs font-medium text-primary">{{ product.category }}</span>
              </div>
              <div v-if="product.is_discount_active" class="absolute top-2 right-2 sm:top-3 sm:right-3 bg-danger text-white text-[10px] sm:text-xs font-extrabold px-2.5 py-0.5 rounded-lg shadow-md">
                -{{ product.discount_percent }}%
              </div>
            </div>
            <div class="p-3 sm:p-5 flex flex-col flex-1">
              <h3 class="text-xs sm:text-base font-semibold text-text dark:text-white group-hover:text-primary transition-colors line-clamp-2 leading-snug">{{ product.name }}</h3>
              <div class="mt-auto pt-2 sm:pt-3">
                <div v-if="product.is_discount_active">
                  <span class="text-[10px] sm:text-xs text-gray-400 line-through">Rp {{ formatPrice(product.price) }}</span>
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-0">
                    <p class="text-sm sm:text-xl font-extrabold text-primary">Rp {{ formatPrice(product.final_price) }}</p>
                    <span :class="['text-[9px] sm:text-xs px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full font-medium w-fit', product.stock > 0 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger']">
                      {{ product.stock > 0 ? `Stok: ${product.stock}` : 'Habis' }}
                    </span>
                  </div>
                </div>
                <div v-else class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-0">
                  <p class="text-sm sm:text-xl font-bold text-primary">Rp {{ formatPrice(product.price) }}</p>
                  <span :class="['text-[9px] sm:text-xs px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full font-medium w-fit', product.stock > 0 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger']">
                    {{ product.stock > 0 ? `Stok: ${product.stock}` : 'Habis' }}
                  </span>
                </div>
                <Link :href="`/produk/${product.slug}`" class="mt-2.5 sm:mt-4 w-full py-1.5 sm:py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white text-xs sm:text-sm font-semibold rounded-lg sm:rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 flex items-center justify-center gap-1 sm:gap-2">
                  <Icon icon="mdi:cart-plus" class="text-sm sm:text-base" /> Beli
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Ulasan / Testimoni Pelanggan (Hanya Tampil Jika Ada Ulasan) -->
    <section v-if="reviews && reviews.length > 0" class="py-20 bg-white dark:bg-gray-900 transition-colors duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <h2 class="text-3xl font-bold text-text dark:text-white">Ulasan Pembeli</h2>
          <p class="text-gray-500 dark:text-gray-400 mt-3">Pendapat pembeli setia kami tentang Biskuit Ikan Hulu'u</p>
        </div>

        <!-- Grid Ulasan Dinamis (Database-driven) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="review in reviews" :key="review.id" class="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between gap-2 mb-3">
                <div class="flex items-center gap-0.5">
                  <Icon v-for="s in 5" :key="s" icon="mdi:star" :class="s <= review.rating ? 'text-amber-400' : 'text-gray-200'" class="text-lg" />
                </div>
                <span class="text-[10px] text-gray-400">{{ review.created_at }}</span>
              </div>
              <span class="inline-block text-[10px] font-semibold text-primary bg-primary/5 px-2.5 py-1 rounded-lg mb-3">
                {{ review.product_name }}
              </span>
              <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-4 italic">"{{ review.comment || 'Puas dengan produk ini.' }}"</p>
            </div>
            <div class="flex items-center gap-3 border-t border-gray-50 dark:border-gray-700 pt-4 mt-2">
              <div class="w-9 h-9 bg-gradient-to-br from-primary/80 to-primary rounded-full flex items-center justify-center">
                <span class="text-white text-xs font-bold">{{ review.user_name.charAt(0) }}</span>
              </div>
              <div>
                <p class="text-xs font-bold text-text dark:text-white">{{ review.user_name }}</p>
                <p class="text-[10px] text-gray-400">Pembeli Terverifikasi</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </UserLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({ products: Array, categories: Array, reviews: Array });

const features = [
  { icon: 'mdi:dumbbell', title: 'Tinggi Protein', desc: 'Kaya akan protein ikan segar dari Danau Limboto untuk nutrisi optimal.', bgClass: 'bg-blue-50', iconClass: 'text-blue-500' },
  { icon: 'mdi:map-marker-radius', title: 'Produk Lokal Gorontalo', desc: 'Mendukung UMKM lokal dengan produk asli Gorontalo berkualitas.', bgClass: 'bg-green-50', iconClass: 'text-green-500' },
  { icon: 'mdi:diamond-stone', title: 'Bahan Berkualitas', desc: 'Menggunakan bahan-bahan pilihan terbaik dan segar setiap hari.', bgClass: 'bg-purple-50', iconClass: 'text-purple-500' },
  { icon: 'mdi:shield-check', title: 'Aman Dikonsumsi', desc: 'Telah tersertifikasi dan aman untuk dikonsumsi segala usia.', bgClass: 'bg-orange-50', iconClass: 'text-orange-500' },
];

function formatPrice(price) {
  return Number(price).toLocaleString('id-ID');
}
</script>
