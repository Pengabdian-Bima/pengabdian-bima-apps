<template>
  <Head :title="product.name" />
  <UserLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
        <Link href="/" class="hover:text-primary">Beranda</Link>
        <Icon icon="mdi:chevron-right" />
        <Link href="/produk" class="hover:text-primary">Produk</Link>
        <Icon icon="mdi:chevron-right" />
        <span class="text-text font-medium">{{ product.name }}</span>
      </nav>

      <div class="grid lg:grid-cols-2 gap-10">
        <div>
          <div class="aspect-square bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl overflow-hidden flex items-center justify-center border border-gray-100">
            <img v-if="product.thumbnail_url" :src="product.thumbnail_url" :alt="product.name" class="w-full h-full object-cover">
            <Icon v-else icon="mdi:food-croissant" class="text-[100px] text-primary/30" />
          </div>
          <div v-if="product.images?.length" class="flex gap-3 mt-4 overflow-x-auto pb-2">
            <div v-for="img in product.images" :key="img.id" class="w-20 h-20 rounded-xl overflow-hidden border-2 border-gray-200 flex-shrink-0 cursor-pointer hover:border-primary transition">
              <img :src="img.url" class="w-full h-full object-cover">
            </div>
          </div>
        </div>

        <div>
          <span class="inline-block px-3 py-1 bg-primary/10 rounded-full text-sm font-medium text-primary">{{ product.category }}</span>
          <h1 class="text-3xl font-bold text-text mt-3">{{ product.name }}</h1>

          <div class="flex items-center gap-4 mt-4">
            <p class="text-3xl font-extrabold text-primary">Rp {{ formatPrice(product.price) }}</p>
            <span :class="['text-sm px-3 py-1 rounded-full font-medium', product.stock > 0 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger']">
              {{ product.stock > 0 ? `Stok: ${product.stock}` : 'Habis' }}
            </span>
          </div>

          <div class="flex items-center gap-6 mt-4 text-sm text-gray-500">
            <span class="flex items-center gap-1"><Icon icon="mdi:weight" /> {{ product.weight }}g</span>
          </div>

          <div class="border-t border-gray-100 mt-6 pt-6">
            <h3 class="font-semibold text-text mb-2">Deskripsi</h3>
            <p class="text-gray-600 leading-relaxed whitespace-pre-line">{{ product.description }}</p>
          </div>

          <div class="border-t border-gray-100 mt-6 pt-6">
            <div class="flex items-center gap-3">
              <label class="text-sm font-medium text-gray-700">Jumlah:</label>
              <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                <button @click="qty > 1 && qty--" class="px-3 py-2 hover:bg-gray-50 transition"><Icon icon="mdi:minus" /></button>
                <input v-model.number="qty" type="number" min="1" :max="product.stock" class="w-16 text-center border-x border-gray-200 py-2 outline-none">
                <button @click="qty < product.stock && qty++" class="px-3 py-2 hover:bg-gray-50 transition"><Icon icon="mdi:plus" /></button>
              </div>
            </div>

            <div class="flex gap-3 mt-6">
              <button @click="addToCart" :disabled="product.stock === 0 || addingToCart"
                class="flex-1 py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-2">
                <Icon icon="mdi:cart-plus" class="text-xl" />
                {{ addingToCart ? 'Menambahkan...' : 'Tambah ke Keranjang' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <div v-if="relatedProducts.length" class="mt-16">
        <h2 class="text-2xl font-bold text-text mb-6">Produk Terkait</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <Link v-for="p in relatedProducts" :key="p.id" :href="`/produk/${p.slug}`" class="bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group">
            <div class="aspect-square bg-gradient-to-br from-orange-50 to-amber-50 flex items-center justify-center">
              <img v-if="p.thumbnail_url" :src="p.thumbnail_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              <Icon v-else icon="mdi:food-croissant" class="text-4xl text-primary/30" />
            </div>
            <div class="p-3">
              <h3 class="text-sm font-semibold text-text truncate">{{ p.name }}</h3>
              <p class="text-primary font-bold mt-1">Rp {{ formatPrice(p.price) }}</p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </UserLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({ product: Object, relatedProducts: Array });
const qty = ref(1);
const addingToCart = ref(false);

function formatPrice(price) { return Number(price).toLocaleString('id-ID'); }

function addToCart() {
  addingToCart.value = true;
  router.post('/keranjang', { product_id: props.product.id, qty: qty.value }, {
    preserveScroll: true,
    onFinish: () => { addingToCart.value = false; },
  });
}
</script>
