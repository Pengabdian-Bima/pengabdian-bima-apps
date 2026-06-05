<template>
  <Head title="Edit Produk" />
  <AdminLayout>
    <div class="max-w-3xl">
      <Link href="/admin/products" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-4"><Icon icon="mdi:arrow-left" /> Kembali</Link>
      <h1 class="text-2xl font-bold text-text mb-6">Edit Produk</h1>
      <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label><input v-model="form.name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label><select v-model="form.category_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label><input v-model.number="form.price" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Harga Modal *</label><input v-model.number="form.cost_price" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Berat (gram) *</label><input v-model.number="form.weight" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
          <div class="flex items-center gap-2 pt-6"><input v-model="form.status" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"><label class="text-sm font-medium text-gray-700">Aktif</label></div>
        </div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label><textarea v-model="form.description" rows="4" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none resize-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea></div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Foto Utama</label>
          <img v-if="product.thumbnail_url" :src="product.thumbnail_url" class="w-24 h-24 rounded-xl object-cover mb-2 border">
          <input type="file" @change="form.thumbnail = $event.target.files[0]" accept="image/*" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary">
        </div>
        <div v-if="product.images?.length">
          <label class="block text-sm font-medium text-gray-700 mb-2">Galeri Saat Ini</label>
          <div class="flex gap-2 flex-wrap">
            <div v-for="img in product.images" :key="img.id" class="relative group">
              <img :src="img.url" class="w-20 h-20 rounded-lg object-cover border">
              <button type="button" @click="deleteImage(img.id)" class="absolute -top-2 -right-2 w-5 h-5 bg-danger text-white rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition">×</button>
            </div>
          </div>
        </div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Tambah Galeri</label><input type="file" @change="form.gallery = Array.from($event.target.files)" accept="image/*" multiple class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary"></div>
        <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50">{{ form.processing ? 'Menyimpan...' : 'Update Produk' }}</button>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
const props = defineProps({ product: Object, categories: Array });
const form = useForm({
  _method: 'PUT', name: props.product.name, category_id: props.product.category_id, description: props.product.description || '',
  price: props.product.price, cost_price: props.product.cost_price, weight: props.product.weight, status: props.product.status, thumbnail: null, gallery: [],
});
function submit() { form.post(`/admin/products/${props.product.id}`, { forceFormData: true }); }
function deleteImage(id) { if(confirm('Hapus gambar?')) router.delete(`/admin/product-images/${id}`); }
</script>
