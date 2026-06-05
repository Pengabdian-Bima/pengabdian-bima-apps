<template>
  <Head title="Kategori - Admin" />
  <AdminLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-text">Kategori Produk</h1>
      <button @click="showModal = true; editingCategory = null; form.reset()" class="px-5 py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all flex items-center gap-2"><Icon icon="mdi:plus" /> Tambah</button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 font-medium text-gray-500">Nama</th><th class="px-4 py-3 font-medium text-gray-500">Slug</th><th class="px-4 py-3 font-medium text-gray-500">Deskripsi</th><th class="px-4 py-3 font-medium text-gray-500">Produk</th><th class="px-4 py-3 font-medium text-gray-500">Aksi</th></tr></thead>
        <tbody>
          <tr v-for="cat in categories.data" :key="cat.id" class="border-t border-gray-50 hover:bg-gray-50/50">
            <td class="px-4 py-3 font-medium text-text">{{ cat.name }}</td>
            <td class="px-4 py-3 text-gray-500">{{ cat.slug }}</td>
            <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ cat.description || '-' }}</td>
            <td class="px-4 py-3"><span class="bg-primary/10 text-primary text-xs px-2 py-1 rounded-full font-medium">{{ cat.products_count }}</span></td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-1">
                <button @click="editCat(cat)" class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition"><Icon icon="mdi:pencil" /></button>
                <button @click="deleteCat(cat.id)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"><Icon icon="mdi:trash-can" /></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showModal = false">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
          <h2 class="text-lg font-bold text-text mb-4">{{ editingCategory ? 'Edit' : 'Tambah' }} Kategori</h2>
          <form @submit.prevent="submitCat" class="space-y-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label><input v-model="form.name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label><textarea v-model="form.description" rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none resize-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea></div>
            <div class="flex gap-3"><button type="button" @click="showModal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl font-medium hover:bg-gray-50 transition">Batal</button><button type="submit" :disabled="form.processing" class="flex-1 py-2.5 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl font-semibold hover:shadow-lg transition-all disabled:opacity-50">Simpan</button></div>
          </form>
        </div>
      </div>
    </Transition>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineProps({ categories: Object });
const showModal = ref(false);
const editingCategory = ref(null);
const form = useForm({ name: '', description: '' });
function editCat(cat) { editingCategory.value = cat; form.name = cat.name; form.description = cat.description || ''; showModal.value = true; }
function submitCat() {
  if (editingCategory.value) { form.put(`/admin/categories/${editingCategory.value.id}`, { onSuccess: () => { showModal.value = false; } }); }
  else { form.post('/admin/categories', { onSuccess: () => { showModal.value = false; form.reset(); } }); }
}
function deleteCat(id) { if(confirm('Yakin hapus?')) router.delete(`/admin/categories/${id}`); }
</script>
