<template>
  <Head title="Produk - Admin" />
  <AdminLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-text">Produk</h1>
      <Link href="/admin/products/create" class="px-5 py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all flex items-center gap-2">
        <Icon icon="mdi:plus" /> Tambah Produk
      </Link>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 font-medium text-gray-500">Produk</th><th class="px-4 py-3 font-medium text-gray-500">Kategori</th><th class="px-4 py-3 font-medium text-gray-500">Harga</th><th class="px-4 py-3 font-medium text-gray-500">Stok</th><th class="px-4 py-3 font-medium text-gray-500">Status</th><th class="px-4 py-3 font-medium text-gray-500">Aksi</th></tr></thead>
          <tbody>
            <tr v-for="p in products.data" :key="p.id" class="border-t border-gray-50 hover:bg-gray-50/50">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg bg-orange-50 flex-shrink-0 flex items-center justify-center overflow-hidden">
                    <img v-if="p.thumbnail_url" :src="p.thumbnail_url" class="w-full h-full object-cover"><Icon v-else icon="mdi:food-croissant" class="text-primary/40" />
                  </div>
                  <span class="font-medium text-text">{{ p.name }}</span>
                </div>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ p.category }}</td>
              <td class="px-4 py-3 font-medium text-primary">Rp {{ fmt(p.price) }}</td>
              <td class="px-4 py-3"><span :class="['text-xs px-2 py-1 rounded-full font-medium', p.stock <= p.min_stock ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700']">{{ p.stock }}</span></td>
              <td class="px-4 py-3"><span :class="['text-xs px-2 py-1 rounded-full font-medium', p.status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">{{ p.status ? 'Aktif' : 'Nonaktif' }}</span></td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-1">
                  <Link :href="`/admin/products/${p.id}/edit`" class="p-1.5 text-blue-500 bg-blue-100 border border-blue-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition"><Icon icon="mdi:pencil" /></Link>
                  <button @click="deleteProduct(p.id)" class="p-1.5 text-red-500 bg-red-100 border border-red-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"><Icon icon="mdi:trash-can" /></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineProps({ products: Object });
function fmt(p) { return Number(p).toLocaleString('id-ID'); }
function deleteProduct(id) { if(confirm('Yakin hapus produk ini?')) router.delete(`/admin/products/${id}`); }
</script>
