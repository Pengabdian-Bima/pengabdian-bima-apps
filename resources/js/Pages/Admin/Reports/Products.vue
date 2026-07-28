<template>
  <Head title="Laporan Per Produk - Admin" />
  <AdminLayout>
    <h1 class="text-2xl font-bold text-text mb-6">Laporan Per Produk</h1>

    <!-- Sub Navigation Tabs -->
    <div class="flex border-b border-gray-200 mb-6">
      <Link href="/admin/reports" class="px-5 py-2.5 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-primary transition">
        Laporan Penjualan Umum
      </Link>
      <Link href="/admin/reports/products" class="px-5 py-2.5 text-sm font-semibold border-b-2 border-primary text-primary transition">
        Laporan Per Produk
      </Link>
    </div>
 
    <div class="flex flex-wrap gap-2 mb-6">
      <Link v-for="p in periods" :key="p.value" :href="`/admin/reports/products?period=${p.value}`"
        :class="['px-4 py-2 rounded-xl text-sm font-medium transition-all', period === p.value ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'bg-white border border-gray-200 text-gray-600 hover:border-primary']">
        {{ p.label }}
      </Link>
    </div>
 
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Total Produk Terjual</p>
        <p class="text-2xl font-bold text-primary mt-1">{{ fmt(conclusion.total_qty) }} pcs</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Total Omset Produk</p>
        <p class="text-2xl font-bold text-text mt-1">Rp {{ fmt(conclusion.total_sales) }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Total Laba Bersih</p>
        <p class="text-2xl font-bold text-success mt-1">Rp {{ fmt(conclusion.total_profit) }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Rata-rata Margin Profit</p>
        <p class="text-2xl font-bold text-text mt-1">{{ avgMargin }}%</p>
      </div>
    </div>

    <!-- Kesimpulan Laporan Per Produk -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
      <h2 class="font-semibold text-text mb-4">Kesimpulan Laporan Per Produk</h2>
      
      <!-- Highlight Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
          <p class="text-xs text-gray-500 font-medium">🔥 Produk Terlaris (Volume)</p>
          <p class="font-bold text-text mt-1 truncate" v-if="conclusion.top_seller">{{ conclusion.top_seller.name }}</p>
          <p class="text-xs text-primary font-semibold mt-0.5" v-if="conclusion.top_seller">{{ fmt(conclusion.top_seller.total_qty) }} pcs terjual</p>
          <p class="text-xs text-gray-400 mt-1" v-else>Belum ada data</p>
        </div>

        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
          <p class="text-xs text-gray-500 font-medium">💰 Omset Terbesar</p>
          <p class="font-bold text-text mt-1 truncate" v-if="conclusion.top_revenue">{{ conclusion.top_revenue.name }}</p>
          <p class="text-xs text-primary font-semibold mt-0.5" v-if="conclusion.top_revenue">Rp {{ fmt(conclusion.top_revenue.total_revenue) }}</p>
          <p class="text-xs text-gray-400 mt-1" v-else>Belum ada data</p>
        </div>

        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
          <p class="text-xs text-gray-500 font-medium">📈 Laba Bersih Tertinggi</p>
          <p class="font-bold text-text mt-1 truncate" v-if="conclusion.top_profit">{{ conclusion.top_profit.name }}</p>
          <p class="text-xs text-success font-semibold mt-0.5" v-if="conclusion.top_profit">Rp {{ fmt(conclusion.top_profit.net_profit) }} ({{ conclusion.top_profit.margin }}%)</p>
          <p class="text-xs text-gray-400 mt-1" v-else>Belum ada data</p>
        </div>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
      <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h2 class="font-semibold text-text">Rincian Kinerja Per Produk</h2>
        
        <!-- Search filter -->
        <div class="relative w-full sm:w-64">
          <input type="text" v-model="searchQuery" placeholder="Cari nama produk..." class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
          <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base" />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">
              <th class="px-4 py-3 text-center">No</th>
              <th class="px-4 py-3">Produk</th>
              <th class="px-4 py-3">Kategori</th>
              <th class="px-4 py-3 text-right">Harga Jual</th>
              <th class="px-4 py-3 text-right">Harga Modal</th>
              <th class="px-4 py-3 text-center">Sisa Stok</th>
              <th class="px-4 py-3 text-center">Terjual</th>
              <th class="px-4 py-3 text-right">Total Omset</th>
              <th class="px-4 py-3 text-right">Laba Bersih</th>
              <th class="px-4 py-3 text-center">Margin</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(p, index) in filteredProducts" :key="p.id" class="hover:bg-gray-50/60 transition whitespace-nowrap">
              <td class="px-4 py-3 text-center text-xs text-gray-400">{{ index + 1 }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0 flex items-center justify-center">
                    <img v-if="p.thumbnail_url" :src="p.thumbnail_url" class="w-full h-full object-cover" />
                    <Icon v-else icon="mdi:food-croissant" class="text-primary/40 text-xl" />
                  </div>
                  <div>
                    <p class="font-medium text-text text-sm">{{ p.name }}</p>
                    <p class="text-[10px] text-gray-400">ID: #{{ p.id }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-xs text-gray-600 font-medium">{{ p.category }}</td>
              <td class="px-4 py-3 text-right text-xs font-medium">Rp {{ fmt(p.price) }}</td>
              <td class="px-4 py-3 text-right text-xs text-gray-500">Rp {{ fmt(p.cost_price) }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="['text-[11px] px-2.5 py-1 rounded-full font-semibold inline-block', p.stock <= p.min_stock ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-600']">
                  {{ p.stock }} {{ p.stock <= p.min_stock ? '(Kritis)' : 'pcs' }}
                </span>
              </td>
              <td class="px-4 py-3 text-center font-bold text-text text-xs">
                {{ fmt(p.total_qty) }} pcs
              </td>
              <td class="px-4 py-3 text-right font-bold text-primary text-xs">
                Rp {{ fmt(p.total_revenue) }}
              </td>
              <td class="px-4 py-3 text-right font-bold text-success text-xs">
                Rp {{ fmt(p.net_profit) }}
              </td>
              <td class="px-4 py-3 text-center text-xs font-semibold text-gray-700">
                <span :class="p.margin >= 20 ? 'text-success font-bold' : 'text-amber-600'">{{ p.margin }}%</span>
              </td>
            </tr>

            <tr v-if="!filteredProducts.length">
              <td colspan="10" class="text-center py-12 text-gray-400 text-xs">
                Tidak ada data produk yang cocok dengan pencarian
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Export (Disamakan persis dengan Laporan Penjualan Umum) -->
    <div class="mt-6 bg-white rounded-2xl border border-gray-100 p-6">
      <h2 class="font-semibold text-text mb-4">Export Laporan Per Produk</h2>
      <div class="flex gap-3">
        <a :href="`/admin/reports/products/export-pdf?period=${period}`" class="px-5 py-2.5 bg-red-500 text-white text-sm font-semibold rounded-xl hover:bg-red-600 transition flex items-center gap-2"><Icon icon="mdi:file-pdf-box" /> Export PDF</a>
        <a :href="`/admin/reports/products/export-excel?period=${period}`" class="px-5 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition flex items-center gap-2"><Icon icon="mdi:file-excel" /> Export Excel</a>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  products: Array,
  conclusion: Object,
  period: String,
  periodLabel: String,
});

const searchQuery = ref('');

const periods = [
  { value: 'daily', label: 'Hari Ini' },
  { value: 'weekly', label: 'Minggu Ini' },
  { value: 'monthly', label: 'Bulan Ini' },
  { value: 'yearly', label: 'Tahun Ini' },
];

const filteredProducts = computed(() => {
  if (!searchQuery.value) return props.products;
  const q = searchQuery.value.toLowerCase();
  return props.products.filter(p => 
    p.name.toLowerCase().includes(q) || 
    p.category.toLowerCase().includes(q)
  );
});

const avgMargin = computed(() => {
  if (!props.conclusion.total_sales) return 0;
  return roundNum((props.conclusion.total_profit / props.conclusion.total_sales) * 100, 1);
});

function fmt(val) {
  return Number(val || 0).toLocaleString('id-ID');
}

function roundNum(num, dec) {
  return Math.round(num * Math.pow(10, dec)) / Math.pow(10, dec);
}
</script>
