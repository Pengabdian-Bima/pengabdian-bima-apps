<template>
  <Head title="Stok Produk - Admin" />
  <AdminLayout>
    <h1 class="text-2xl font-bold text-text mb-6">Stok Produk</h1>
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead><tr class="bg-gray-50 text-left whitespace-nowrap"><th class="px-4 py-3 font-medium text-gray-500">Produk</th><th class="px-4 py-3 font-medium text-gray-500">Kategori</th><th class="px-4 py-3 font-medium text-gray-500">Stok</th><th class="px-4 py-3 font-medium text-gray-500">Min Stok</th><th class="px-4 py-3 font-medium text-gray-500">Status</th><th class="px-4 py-3 font-medium text-gray-500">Aksi</th></tr></thead>
          <tbody>
            <tr v-for="p in products.data" :key="p.id" class="border-t border-gray-50 hover:bg-gray-50/50 whitespace-nowrap">
              <td class="px-4 py-3 font-medium text-text">{{ p.name }}</td>
              <td class="px-4 py-3 text-gray-500">{{ p.category }}</td>
              <td class="px-4 py-3 font-bold" :class="p.is_low ? 'text-danger' : 'text-text'">{{ p.stock }}</td>
              <td class="px-4 py-3 text-gray-500">{{ p.min_stock }}</td>
              <td class="px-4 py-3"><span :class="['text-xs px-2 py-1 rounded-full font-medium', p.is_low ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700']">{{ p.is_low ? 'Rendah' : 'Aman' }}</span></td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-1">
                  <button @click="openAdjust(p, 'in')" class="p-1.5 text-gray-400 hover:text-green-500 hover:bg-green-50 rounded-lg transition" title="Tambah Stok"><Icon icon="mdi:plus-circle" /></button>
                  <button @click="openAdjust(p, 'out')" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Kurangi Stok"><Icon icon="mdi:minus-circle" /></button>
                  <Link :href="`/admin/stock/${p.id}/history`" class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Riwayat"><Icon icon="mdi:history" /></Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Adjust Modal -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showModal = false">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
          <h2 class="text-lg font-bold text-text mb-4">{{ adjustForm.type === 'in' ? 'Tambah' : 'Kurangi' }} Stok — {{ selectedProduct?.name }}</h2>
          <form @submit.prevent="submitAdjust" class="space-y-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Jumlah *</label><input v-model.number="adjustForm.quantity" type="number" min="1" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label><input v-model="adjustForm.note" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
            <div class="flex gap-3"><button type="button" @click="showModal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl font-medium hover:bg-gray-50 transition">Batal</button><button type="submit" :disabled="adjustForm.processing" :class="['flex-1 py-2.5 text-white rounded-xl font-semibold transition-all disabled:opacity-50', adjustForm.type === 'in' ? 'bg-success hover:bg-green-600' : 'bg-danger hover:bg-red-600']">{{ adjustForm.type === 'in' ? 'Tambah' : 'Kurangi' }}</button></div>
          </form>
        </div>
      </div>
    </Transition>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineProps({ products: Object });
const showModal = ref(false);
const selectedProduct = ref(null);
const adjustForm = useForm({ type: 'in', quantity: 1, note: '' });
function openAdjust(p, type) { selectedProduct.value = p; adjustForm.type = type; adjustForm.quantity = 1; adjustForm.note = ''; showModal.value = true; }
function submitAdjust() { adjustForm.post(`/admin/stock/${selectedProduct.value.id}/adjust`, { onSuccess: () => { showModal.value = false; } }); }
</script>
