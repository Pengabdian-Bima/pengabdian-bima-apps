<template>
  <Head title="Produk" />
  <UserLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 transition-colors duration-300">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-text dark:text-white">Semua Produk</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Temukan produk berkualitas dari UD Flamboyan</p>
      </div>
      <div v-if="products.data.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-6">
        <div v-for="product in products.data" :key="product.id" class="group bg-white dark:bg-gray-900 rounded-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl dark:hover:shadow-black/30 transition-all duration-300 hover:-translate-y-1 flex flex-col">
          <Link :href="`/produk/${product.slug}`" class="block">
            <div class="aspect-square bg-gradient-to-br from-orange-50 to-amber-50 dark:from-gray-800 dark:to-gray-700 flex items-center justify-center overflow-hidden relative">
              <img v-if="product.thumbnail_url" :src="product.thumbnail_url" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              <Icon v-else icon="mdi:food-croissant" class="text-4xl sm:text-6xl text-primary/30" />
              <div class="absolute top-2 left-2 sm:top-3 sm:left-3">
                <span class="px-2 py-0.5 sm:px-3 sm:py-1 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm rounded-full text-[10px] sm:text-xs font-medium text-primary">{{ product.category }}</span>
              </div>
            </div>
          </Link>
          <div class="p-3 sm:p-4 flex flex-col flex-1">
            <h3 class="font-semibold text-xs sm:text-base text-text dark:text-white mt-1 group-hover:text-primary transition-colors line-clamp-2 leading-snug">{{ product.name }}</h3>
            <p class="text-[10px] sm:text-sm text-gray-500 dark:text-gray-400 mt-1">{{ product.weight }}g</p>
            <div class="mt-auto pt-2 sm:pt-3">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-0">
                <p class="text-sm sm:text-lg font-bold text-primary">Rp {{ formatPrice(product.price) }}</p>
                <span :class="['text-[9px] sm:text-xs px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full font-medium w-fit', product.stock > 0 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger']">
                  {{ product.stock > 0 ? `Stok: ${product.stock}` : 'Habis' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-20">
        <Icon icon="mdi:package-variant-remove" class="text-6xl text-gray-300 dark:text-gray-600 mx-auto mb-4" />
        <p class="text-gray-500 dark:text-gray-400">Belum ada produk tersedia.</p>
      </div>
    </div>
  </UserLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

defineProps({ products: Object, categories: Array });

function formatPrice(price) { return Number(price).toLocaleString('id-ID'); }
</script>
