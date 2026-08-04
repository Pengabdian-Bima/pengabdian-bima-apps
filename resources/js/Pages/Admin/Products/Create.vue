<template>
  <Head title="Tambah Produk" />
  <AdminLayout>
    <div class="max-w-3xl">
      <Link href="/admin/products" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-4"><Icon icon="mdi:arrow-left" /> Kembali</Link>
      <h1 class="text-2xl font-bold text-text mb-6">Tambah Produk</h1>
      <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label><input v-model="form.name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"><p v-if="form.errors.name" class="text-danger text-sm mt-1">{{ form.errors.name }}</p></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label><select v-model="form.category_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"><option value="">Pilih</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label><input v-model.number="form.price" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Harga Modal *</label><input v-model.number="form.cost_price" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Stok *</label><input v-model.number="form.stock" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stok</label><input v-model.number="form.min_stock" type="number" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Berat (gram) *</label><input v-model.number="form.weight" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
          <div class="flex items-center gap-2 pt-6"><input v-model="form.status" type="checkbox" id="status" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"><label for="status" class="text-sm font-medium text-gray-700">Aktif</label></div>
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
              <input v-model="form.is_discount_active" type="checkbox" id="is_discount_active" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
              <label for="is_discount_active" class="text-sm font-medium text-gray-700">Aktifkan Diskon</label>
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

        <div><label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label><textarea v-model="form.description" rows="4" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none resize-none"></textarea></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label><input type="file" @change="form.thumbnail = $event.target.files[0]" accept="image/*" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Galeri Foto</label><input type="file" @change="handleGallery" accept="image/*" multiple class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary"></div>
        <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50 cursor-pointer">{{ form.processing ? 'Menyimpan...' : 'Simpan Produk' }}</button>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ categories: Array });

const form = useForm({
  name: '',
  category_id: '',
  description: '',
  price: null,
  cost_price: null,
  discount_percent: 0,
  discount_start_at: '',
  discount_end_at: '',
  is_discount_active: true,
  stock: 0,
  min_stock: 5,
  weight: null,
  thumbnail: null,
  gallery: [],
  status: true
});

const calculatedFinalPrice = computed(() => {
  if (!form.price || !form.discount_percent) return form.price || 0;
  return Math.round(form.price * (1 - form.discount_percent / 100));
});

function fmt(val) { return Number(val || 0).toLocaleString('id-ID'); }
function handleGallery(e) { form.gallery = Array.from(e.target.files); }
function submit() { form.post('/admin/products', { forceFormData: true }); }
</script>
