<template>
  <Head title="Beranda" />
  <UserLayout>
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50">
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
            <h1 class="text-4xl lg:text-6xl font-extrabold text-text leading-tight">
              Biskuit Ikan
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-primary-dark">Huluu</span>
              <br>Danau Limboto
            </h1>
            <p class="text-lg text-gray-600 mt-6 leading-relaxed max-w-lg">
              Nikmati kelezatan biskuit ikan segar dari Danau Limboto. Kaya protein, berkualitas tinggi, dan dibuat dengan resep tradisional khas Gorontalo.
            </p>
            <div class="flex flex-wrap gap-4 mt-8">
              <Link href="/produk" class="px-8 py-4 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-xl hover:shadow-primary/30 transition-all duration-300 flex items-center gap-2">
                <Icon icon="mdi:shopping" class="text-xl" /> Belanja Sekarang
              </Link>
              <a href="#keunggulan" class="px-8 py-4 bg-white text-text font-semibold rounded-xl border border-gray-200 hover:border-primary hover:text-primary transition-all duration-300 flex items-center gap-2">
                <Icon icon="mdi:information-outline" class="text-xl" /> Pelajari Lebih
              </a>
            </div>
          </div>
          <div class="relative hidden lg:block">
            <div class="w-96 h-96 bg-gradient-to-br from-primary/20 to-primary/5 rounded-full mx-auto flex items-center justify-center">
              <div class="w-72 h-72 bg-gradient-to-br from-primary/30 to-primary/10 rounded-full flex items-center justify-center animate-pulse">
                <Icon icon="mdi:food-croissant" class="text-[120px] text-primary/60" />
              </div>
            </div>
            <div class="absolute top-8 right-8 bg-white rounded-2xl shadow-xl p-4 flex items-center gap-3 animate-bounce" style="animation-duration:3s">
              <div class="w-10 h-10 bg-success/10 rounded-xl flex items-center justify-center"><Icon icon="mdi:leaf" class="text-success text-xl" /></div>
              <div><p class="text-sm font-semibold">100% Alami</p><p class="text-xs text-gray-500">Tanpa Pengawet</p></div>
            </div>
            <div class="absolute bottom-12 left-4 bg-white rounded-2xl shadow-xl p-4 flex items-center gap-3">
              <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center"><Icon icon="mdi:shield-check" class="text-primary text-xl" /></div>
              <div><p class="text-sm font-semibold">BPOM Certified</p><p class="text-xs text-gray-500">Aman Dikonsumsi</p></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Keunggulan -->
    <section id="keunggulan" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <h2 class="text-3xl font-bold text-text">Keunggulan Produk Kami</h2>
          <p class="text-gray-500 mt-3 max-w-2xl mx-auto">Biskuit Ikan Huluu Danau Limboto diproduksi dengan standar kualitas tinggi</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="(feat, i) in features" :key="i" class="group p-6 bg-gradient-to-br from-gray-50 to-white rounded-2xl border border-gray-100 hover:border-primary/30 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300">
            <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center mb-4 transition-all duration-300 group-hover:scale-110', feat.bgClass]">
              <Icon :icon="feat.icon" :class="['text-2xl', feat.iconClass]" />
            </div>
            <h3 class="text-lg font-semibold text-text mb-2">{{ feat.title }}</h3>
            <p class="text-sm text-gray-500 leading-relaxed">{{ feat.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Produk Unggulan -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h2 class="text-3xl font-bold text-text">Produk Unggulan</h2>
            <p class="text-gray-500 mt-2">Pilihan terbaik untuk Anda</p>
          </div>
          <Link href="/produk" class="hidden sm:flex items-center gap-1 text-primary font-semibold hover:gap-2 transition-all">
            Lihat Semua <Icon icon="mdi:arrow-right" />
          </Link>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="product in products" :key="product.id" class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl hover:shadow-gray-200/50 transition-all duration-300 hover:-translate-y-1">
            <div class="aspect-[4/3] bg-gradient-to-br from-orange-50 to-amber-50 relative overflow-hidden flex items-center justify-center">
              <img v-if="product.thumbnail_url" :src="product.thumbnail_url" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              <Icon v-else icon="mdi:food-croissant" class="text-6xl text-primary/30" />
              <div class="absolute top-3 left-3">
                <span class="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-medium text-primary">{{ product.category }}</span>
              </div>
            </div>
            <div class="p-5">
              <h3 class="font-semibold text-text group-hover:text-primary transition-colors">{{ product.name }}</h3>
              <div class="flex items-center justify-between mt-3">
                <p class="text-xl font-bold text-primary">Rp {{ formatPrice(product.price) }}</p>
                <span :class="['text-xs px-2 py-1 rounded-full font-medium', product.stock > 0 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger']">
                  {{ product.stock > 0 ? `Stok: ${product.stock}` : 'Habis' }}
                </span>
              </div>
              <Link :href="`/produk/${product.slug}`" class="mt-4 w-full py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 flex items-center justify-center gap-2">
                <Icon icon="mdi:cart-plus" /> Beli Sekarang
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimoni -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <h2 class="text-3xl font-bold text-text">Apa Kata Mereka?</h2>
          <p class="text-gray-500 mt-3">Testimoni dari pelanggan setia kami</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="(t, i) in testimonials" :key="i" class="p-6 bg-gradient-to-br from-gray-50 to-white rounded-2xl border border-gray-100">
            <div class="flex items-center gap-1 mb-3">
              <Icon v-for="s in 5" :key="s" icon="mdi:star" class="text-primary" />
            </div>
            <p class="text-gray-600 text-sm leading-relaxed mb-4">"{{ t.text }}"</p>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-primary to-primary-dark rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-bold">{{ t.name.charAt(0) }}</span>
              </div>
              <div>
                <p class="text-sm font-semibold text-text">{{ t.name }}</p>
                <p class="text-xs text-gray-500">{{ t.location }}</p>
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

const props = defineProps({ products: Array, categories: Array });

const features = [
  { icon: 'mdi:dumbbell', title: 'Tinggi Protein', desc: 'Kaya akan protein ikan segar dari Danau Limboto untuk nutrisi optimal.', bgClass: 'bg-blue-50', iconClass: 'text-blue-500' },
  { icon: 'mdi:map-marker-radius', title: 'Produk Lokal Gorontalo', desc: 'Mendukung UMKM lokal dengan produk asli Gorontalo berkualitas.', bgClass: 'bg-green-50', iconClass: 'text-green-500' },
  { icon: 'mdi:diamond-stone', title: 'Bahan Berkualitas', desc: 'Menggunakan bahan-bahan pilihan terbaik dan segar setiap hari.', bgClass: 'bg-purple-50', iconClass: 'text-purple-500' },
  { icon: 'mdi:shield-check', title: 'Aman Dikonsumsi', desc: 'Telah tersertifikasi dan aman untuk dikonsumsi segala usia.', bgClass: 'bg-orange-50', iconClass: 'text-orange-500' },
];

const testimonials = [
  { name: 'Siti Rahmawati', location: 'Gorontalo', text: 'Biskuitnya enak banget! Renyah dan gurih. Anak-anak saya sangat suka. Pasti repeat order.' },
  { name: 'Ahmad Fadil', location: 'Jakarta', text: 'Luar biasa, rasa ikannya terasa natural. Cocok untuk oleh-oleh dari Gorontalo. Recommended!' },
  { name: 'Nur Hasanah', location: 'Makassar', text: 'Pengemasan rapi, rasa istimewa. Senang bisa mendukung produk UMKM lokal Gorontalo.' },
];

function formatPrice(price) {
  return Number(price).toLocaleString('id-ID');
}
</script>
