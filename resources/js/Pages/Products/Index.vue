<template>
  <Head title="Produk" />
  <UserLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-text">Semua Produk</h1>
        <p class="text-gray-500 mt-2">Temukan produk berkualitas dari UD Flamboyan</p>
      </div>
      <div v-if="products.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="product in products.data" :key="product.id" class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
          <Link :href="`/produk/${product.slug}`">
            <div class="aspect-square bg-gradient-to-br from-orange-50 to-amber-50 flex items-center justify-center overflow-hidden">
              <img v-if="product.thumbnail_url" :src="product.thumbnail_url" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              <Icon v-else icon="mdi:food-croissant" class="text-6xl text-primary/30" />
            </div>
          </Link>
          <div class="p-4">
            <span class="text-xs text-primary font-medium">{{ product.category }}</span>
            <h3 class="font-semibold text-text mt-1 group-hover:text-primary transition-colors">{{ product.name }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ product.weight }}g</p>
            <div class="flex items-center justify-between mt-3">
              <p class="text-lg font-bold text-primary">Rp {{ formatPrice(product.price) }}</p>
              <span :class="['text-xs px-2 py-1 rounded-full font-medium', product.stock > 0 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger']">
                {{ product.stock > 0 ? `Stok: ${product.stock}` : 'Habis' }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-20">
        <Icon icon="mdi:package-variant-remove" class="text-6xl text-gray-300 mx-auto mb-4" />
        <p class="text-gray-500">Belum ada produk tersedia.</p>
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
