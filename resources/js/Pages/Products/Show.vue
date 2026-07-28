<template>
  <Head :title="product.name" />
  <UserLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-8">
        <Link href="/" class="hover:text-primary">Beranda</Link>
        <Icon icon="mdi:chevron-right" />
        <Link href="/produk" class="hover:text-primary">Produk</Link>
        <Icon icon="mdi:chevron-right" />
        <span class="text-text dark:text-white font-medium">{{ product.name }}</span>
      </nav>

      <div class="grid lg:grid-cols-2 gap-10">
        <div>
          <!-- Main Display Image -->
          <div 
            class="aspect-square bg-gradient-to-br from-orange-50 to-amber-50 dark:from-gray-800 dark:to-gray-700 rounded-2xl overflow-hidden flex items-center justify-center border border-gray-100 dark:border-gray-800 cursor-pointer group relative" 
            @click="currentMainImage && openPreview(currentMainImage)"
          >
            <img v-if="currentMainImage" :src="currentMainImage" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
            <Icon v-else icon="mdi:food-croissant" class="text-[100px] text-primary/30" />
            <div v-if="currentMainImage" class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300 text-white font-semibold text-sm gap-2">
              <Icon icon="mdi:magnify-plus" class="text-2xl" /> Klik untuk Perbesar
            </div>
          </div>

          <!-- Thumbnails (Main + Gallery) -->
          <div v-if="allImages.length > 1" class="flex gap-3 mt-4 overflow-x-auto pb-2">
            <div 
              v-for="(img, idx) in allImages" 
              :key="idx" 
              @click="currentMainImage = img"
              :class="[
                'w-20 h-20 rounded-xl overflow-hidden border-2 flex-shrink-0 cursor-pointer transition relative group',
                currentMainImage === img ? 'border-primary shadow-md scale-105' : 'border-gray-200 dark:border-gray-700 hover:border-primary/50'
              ]"
            >
              <img :src="img" class="w-full h-full object-cover">
              <button 
                type="button"
                @click.stop="openPreview(img)"
                class="absolute bottom-1 right-1 p-1 bg-black/60 hover:bg-black text-white rounded-md text-xs opacity-0 group-hover:opacity-100 transition"
                title="Perbesar Foto Ini"
              >
                <Icon icon="mdi:fullscreen" />
              </button>
            </div>
          </div>
        </div>

        <div>
          <span class="inline-block px-3 py-1 bg-primary/10 rounded-full text-sm font-medium text-primary">{{ product.category }}</span>
          <h1 class="text-3xl font-bold text-text dark:text-white mt-3">{{ product.name }}</h1>

          <div class="flex items-center gap-4 mt-4">
            <p class="text-3xl font-extrabold text-primary">Rp {{ formatPrice(product.price) }}</p>
            <span :class="['text-sm px-3 py-1 rounded-full font-medium', product.stock > 0 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger']">
              {{ product.stock > 0 ? `Stok: ${product.stock}` : 'Habis' }}
            </span>
          </div>

          <div class="flex items-center gap-6 mt-4 text-sm text-gray-500 dark:text-gray-400">
            <span class="flex items-center gap-1"><Icon icon="mdi:weight" /> {{ product.weight }}g</span>
          </div>

          <div class="border-t border-gray-100 dark:border-gray-800 mt-6 pt-6">
            <h3 class="font-semibold text-text dark:text-white mb-2">Deskripsi</h3>
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">{{ product.description }}</p>
          </div>

          <div class="border-t border-gray-100 dark:border-gray-800 mt-6 pt-6">
            <div class="flex items-center gap-3">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah:</label>
              <div class="flex items-center border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <button @click="qty > 1 && qty--" class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-300 transition"><Icon icon="mdi:minus" /></button>
                <input v-model.number="qty" type="number" min="1" :max="product.stock" class="w-16 text-center border-x border-gray-200 dark:border-gray-700 py-2 outline-none dark:bg-gray-900 dark:text-white">
                <button @click="qty < product.stock && qty++" class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 dark:text-gray-300 transition"><Icon icon="mdi:plus" /></button>
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
        <h2 class="text-2xl font-bold text-text dark:text-white mb-6">Produk Terkait</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <Link v-for="p in relatedProducts" :key="p.id" :href="`/produk/${p.slug}`" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden hover:shadow-lg dark:hover:shadow-black/30 transition-all duration-300 group">
            <div class="aspect-square bg-gradient-to-br from-orange-50 to-amber-50 dark:from-gray-800 dark:to-gray-700 flex items-center justify-center">
              <img v-if="p.thumbnail_url" :src="p.thumbnail_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              <Icon v-else icon="mdi:food-croissant" class="text-4xl text-primary/30" />
            </div>
            <div class="p-3">
              <h3 class="text-sm font-semibold text-text dark:text-white truncate">{{ p.name }}</h3>
              <p class="text-primary font-bold mt-1">Rp {{ formatPrice(p.price) }}</p>
            </div>
          </Link>
        </div>
      </div>

      <!-- Lightbox Preview Modal -->
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-if="showPreview && allImages.length" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 md:p-8" @click.self="showPreview = false">
          <!-- Close button -->
          <button 
            type="button"
            @click="showPreview = false" 
            class="absolute top-4 right-4 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-full transition z-50 cursor-pointer"
            title="Tutup"
          >
            <Icon icon="mdi:close" class="text-2xl" />
          </button>

          <!-- Navigation Arrow Left -->
          <button 
            type="button"
            v-if="allImages.length > 1"
            @click="prevImage" 
            class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-3 rounded-full transition z-45 cursor-pointer"
            title="Sebelumnya"
          >
            <Icon icon="mdi:chevron-left" class="text-3xl" />
          </button>

          <!-- Main Large Image Container -->
          <div class="relative max-w-4xl max-h-[80vh] flex flex-col items-center justify-center select-none">
            <img 
              :src="allImages[activeIndex]" 
              :alt="product.name" 
              class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-2xl border border-white/5"
            >
            <!-- Counter badge -->
            <span class="mt-4 px-4 py-1.5 bg-white/10 text-white rounded-full text-xs font-semibold">
              {{ activeIndex + 1 }} / {{ allImages.length }}
            </span>
          </div>

          <!-- Navigation Arrow Right -->
          <button 
            type="button"
            v-if="allImages.length > 1"
            @click="nextImage" 
            class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-3 rounded-full transition z-45 cursor-pointer"
            title="Berikutnya"
          >
            <Icon icon="mdi:chevron-right" class="text-3xl" />
          </button>
        </div>
      </Transition>
    </div>
  </UserLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({ product: Object, relatedProducts: Array });
const qty = ref(1);
const addingToCart = ref(false);

const currentMainImage = ref(props.product.thumbnail_url || null);

// Lightbox state
const showPreview = ref(false);
const activeIndex = ref(0);

// Combine thumbnail and gallery images into one unique list for the previewer
const allImages = computed(() => {
  const imgs = [];
  if (props.product.thumbnail_url) {
    imgs.push(props.product.thumbnail_url);
  }
  if (props.product.images) {
    props.product.images.forEach(img => {
      if (img.url && !imgs.includes(img.url)) {
        imgs.push(img.url);
      }
    });
  }
  return imgs;
});

watch(() => props.product, (newP) => {
  if (newP?.thumbnail_url) {
    currentMainImage.value = newP.thumbnail_url;
  } else if (allImages.value.length > 0) {
    currentMainImage.value = allImages.value[0];
  }
}, { immediate: true });

function openPreview(url) {
  let index = allImages.value.indexOf(url);
  if (index === -1) {
    index = allImages.value.findIndex(i => i === url || i.includes(url) || url.includes(i));
  }
  if (index === -1) {
    index = 0;
  }
  activeIndex.value = index;
  showPreview.value = true;
}

function prevImage() {
  if (allImages.value.length === 0) return;
  activeIndex.value = (activeIndex.value - 1 + allImages.value.length) % allImages.value.length;
}

function nextImage() {
  if (allImages.value.length === 0) return;
  activeIndex.value = (activeIndex.value + 1) % allImages.value.length;
}

function handleKeyDown(e) {
  if (!showPreview.value) return;
  if (e.key === 'ArrowLeft') prevImage();
  if (e.key === 'ArrowRight') nextImage();
  if (e.key === 'Escape') showPreview.value = false;
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function formatPrice(price) { return Number(price).toLocaleString('id-ID'); }

function addToCart() {
  addingToCart.value = true;
  router.post('/keranjang', { product_id: props.product.id, qty: qty.value }, {
    preserveScroll: true,
    onFinish: () => { addingToCart.value = false; },
  });
}
</script>
