<template>
  <Head title="Upload Bukti Pembayaran" />
  <UserLayout>
    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <Link href="/pesanan" class="text-sm text-gray-500 hover:text-primary flex items-center gap-1 mb-6"><Icon icon="mdi:arrow-left" /> Kembali</Link>
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h1 class="text-xl font-bold text-text mb-1">Upload Bukti Pembayaran</h1>
        <p class="text-sm text-gray-500 mb-6">Pesanan {{ order.order_code }} — Rp {{ fmt(order.total_amount) }}</p>
        <form @submit.prevent="submit" class="space-y-4">
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Pengirim *</label><input v-model="form.sender_name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Bank Pengirim *</label><select v-model="form.sender_bank" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"><option value="">Pilih Bank</option><option value="BRI">BRI</option><option value="BNI">BNI</option><option value="Mandiri">Mandiri</option><option value="BCA">BCA</option></select></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Nominal Transfer *</label><input v-model.number="form.amount" type="number" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transfer *</label><input v-model="form.transfer_date" type="date" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Bukti Transfer *</label>
            <input type="file" @change="form.proof_image = $event.target.files[0]" accept="image/jpeg,image/png,image/webp" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP. Maks 5MB</p>
            <p v-if="form.errors.proof_image" class="text-danger text-sm mt-1">{{ form.errors.proof_image }}</p>
          </div>
          <button type="submit" :disabled="form.processing" class="w-full py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50">{{ form.processing ? 'Mengupload...' : 'Upload Bukti Pembayaran' }}</button>
        </form>
      </div>
    </div>
  </UserLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';
const props = defineProps({ order: Object });
function fmt(p) { return Number(p).toLocaleString('id-ID'); }
const form = useForm({ sender_name: '', sender_bank: '', amount: null, transfer_date: '', proof_image: null });
function submit() { form.post(`/pesanan/${props.order.id}/bayar`, { forceFormData: true }); }
</script>
