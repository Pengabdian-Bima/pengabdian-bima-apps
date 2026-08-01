<template>
  <Head title="Edit Produk" />
  <AdminLayout>
    <div class="max-w-3xl">
      <Link href="/admin/products" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-4"><Icon icon="mdi:arrow-left" /> Kembali</Link>
      <h1 class="text-2xl font-bold text-text mb-6">Edit Produk</h1>
      <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label><input v-model="form.name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label><select v-model="form.category_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label><input v-model.number="form.price" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Harga Modal *</label><input v-model.number="form.cost_price" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Berat (gram) *</label><input v-model.number="form.weight" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
          <div class="flex items-center gap-2 pt-6"><input v-model="form.status" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"><label class="text-sm font-medium text-gray-700">Aktif</label></div>
        </div>

        <!-- Section Pengaturan Diskon -->
        <div class="p-4 bg-orange-50/60 rounded-2xl border border-orange-100 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Icon icon="mdi:ticket-percent" class="text-primary text-xl" />
              <h3 class="font-bold text-text text-sm">Pengaturan Diskon Produk</h3>
            </div>
            <!-- Checkbox Diskon Aktif -->
            <div class="flex items-center gap-2">
              <input v-model="form.is_discount_active" type="checkbox" id="is_discount_active_edit" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
              <label for="is_discount_active_edit" class="text-sm font-medium text-gray-700">Aktifkan Diskon</label>
            </div>
          </div>

          <div class="grid sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Diskon (%)</label>
              <input v-model.number="form.discount_percent" type="number" min="0" max="100" placeholder="0" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Waktu Mulai Diskon</label>
              <input v-model="form.discount_start_at" type="datetime-local" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Waktu Berakhir Diskon</label>
              <input v-model="form.discount_end_at" type="datetime-local" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
            </div>
          </div>

          <!-- Price Preview Calculator -->
          <div v-if="form.discount_percent > 0" class="pt-2 border-t border-orange-200/60 flex items-center justify-between text-xs">
            <span class="text-gray-600 font-medium">Kalkulasi Harga Setelah Diskon:</span>
            <div class="text-right">
              <span class="line-through text-gray-400 mr-2">Rp {{ fmt(form.price) }}</span>
              <span class="font-bold text-primary text-sm">Rp {{ fmt(calculatedFinalPrice) }}</span>
              <span class="ml-1.5 text-emerald-600 font-semibold">(Hemat Rp {{ fmt((form.price || 0) - calculatedFinalPrice) }})</span>
            </div>
          </div>
        </div>

        <div><label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label><textarea v-model="form.description" rows="4" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none resize-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea></div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
          <div v-if="product.thumbnail_url" class="relative inline-block group cursor-pointer" @click="openPreview(product.thumbnail_url)">
            <img :src="product.thumbnail_url" class="w-24 h-24 rounded-xl object-cover mb-2 border border-gray-200 group-hover:opacity-90 transition">
            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 rounded-xl flex items-center justify-center text-white text-xs font-semibold gap-1 transition">
              <Icon icon="mdi:magnify-plus" /> Perbesar
            </div>
          </div>
          <input type="file" @change="form.thumbnail = $event.target.files[0]" accept="image/*" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary">
        </div>
        <div v-if="product.images?.length">
          <label class="block text-sm font-medium text-gray-700 mb-2">Galeri Saat Ini</label>
          <div class="flex gap-2 flex-wrap">
            <div v-for="img in product.images" :key="img.id" class="relative group cursor-pointer" @click="openPreview(img.url)">
              <img :src="img.url" class="w-20 h-20 rounded-lg object-cover border border-gray-200 group-hover:opacity-90 transition">
              <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 rounded-lg flex items-center justify-center text-white text-xs gap-1 transition">
                <Icon icon="mdi:magnify-plus" />
              </div>
              <button type="button" @click.stop="deleteImage(img.id)" class="absolute -top-2 -right-2 w-6 h-6 bg-danger text-white rounded-full text-xs flex items-center justify-center opacity-90 hover:opacity-100 shadow z-10" title="Hapus Gambar">×</button>
            </div>
          </div>
        </div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Tambah Galeri</label><input type="file" @change="form.gallery = Array.from($event.target.files)" accept="image/*" multiple class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary"></div>
        <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50 cursor-pointer">{{ form.processing ? 'Menyimpan...' : 'Update Produk' }}</button>
      </form>
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
        <button 
          type="button"
          @click="showPreview = false" 
          class="absolute top-4 right-4 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-full transition z-50 cursor-pointer"
          title="Tutup"
        >
          <Icon icon="mdi:close" class="text-2xl" />
        </button>

        <button 
          type="button"
          v-if="allImages.length > 1"
          @click="prevImage" 
          class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-3 rounded-full transition z-45 cursor-pointer"
          title="Sebelumnya"
        >
          <Icon icon="mdi:chevron-left" class="text-3xl" />
        </button>

        <div class="relative max-w-4xl max-h-[80vh] flex flex-col items-center justify-center select-none">
          <img 
            :src="allImages[activeIndex]" 
            :alt="product.name" 
            class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-2xl border border-white/5"
          >
          <span class="mt-4 px-4 py-1.5 bg-white/10 text-white rounded-full text-xs font-semibold">
            {{ activeIndex + 1 }} / {{ allImages.length }}
          </span>
        </div>

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
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ product: Object, categories: Array });

const form = useForm({
  _method: 'PUT',
  name: props.product.name,
  category_id: props.product.category_id,
  description: props.product.description || '',
  price: props.product.price,
  cost_price: props.product.cost_price,
  discount_percent: props.product.discount_percent || 0,
  discount_start_at: props.product.discount_start_at || '',
  discount_end_at: props.product.discount_end_at || '',
  is_discount_active: props.product.is_discount_active ?? true,
  weight: props.product.weight,
  status: props.product.status,
  thumbnail: null,
  gallery: [],
});

const calculatedFinalPrice = computed(() => {
  if (!form.price || !form.discount_percent) return form.price || 0;
  return Math.round(form.price * (1 - form.discount_percent / 100));
});

function fmt(val) { return Number(val || 0).toLocaleString('id-ID'); }

// Lightbox state
const showPreview = ref(false);
const activeIndex = ref(0);

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

function submit() { form.post(`/admin/products/${props.product.id}`, { forceFormData: true }); }
function deleteImage(id) { if(confirm('Hapus gambar?')) router.delete(`/admin/product-images/${id}`); }
</script>
