<template>
  <Head title="Laporan - Admin" />
  <AdminLayout>
    <h1 class="text-2xl font-bold text-text mb-2">Laporan Penjualan</h1> 
    <!-- Sub Navigation Tabs -->
    <div class="flex border-b border-gray-200 mb-6">
      <Link href="/admin/reports" class="px-5 py-2.5 text-sm font-semibold border-b-2 border-primary text-primary transition">
        Laporan Penjualan Umum
      </Link>
      <Link href="/admin/reports/products" class="px-5 py-2.5 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-primary transition">
        Laporan Per Produk
      </Link>
    </div>

    <!-- Period Filter -->
    <div class="flex flex-wrap gap-2 mb-6">
      <Link v-for="p in periods" :key="p.value" :href="`/admin/reports?period=${p.value}`"
        :class="['px-4 py-2 rounded-xl text-sm font-medium transition-all', period === p.value ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'bg-white border border-gray-200 text-gray-600 hover:border-primary']">
        {{ p.label }}
      </Link>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Total Penjualan</p>
        <p class="text-2xl font-bold text-primary mt-1">Rp {{ fmt(stats.totalSales) }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Total Modal</p>
        <p class="text-2xl font-bold text-text mt-1">Rp {{ fmt(stats.totalCost) }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Laba/Keuntungan</p>
        <p class="text-2xl font-bold text-success mt-1">Rp {{ fmt(stats.profit) }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Jumlah Pesanan</p>
        <p class="text-2xl font-bold text-text mt-1">{{ stats.orderCount }}</p>
      </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
      <!-- Sales Chart -->
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="font-semibold text-text mb-4">Grafik Penjualan</h2>
        <apexchart v-if="salesData.length" type="bar" height="280" :options="chartOpts" :series="chartSeries" />
        <p v-else class="text-sm text-gray-500 text-center py-10">Belum ada data penjualan</p>
      </div>

      <!-- Best Selling -->
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-text">Produk Terlaris</h2>
          <div class="flex gap-1">
            <a :href="`/admin/reports/best-selling-pdf?period=${period}`" class="p-1.5 text-red-500 bg-red-100 border border-red-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="PDF"><Icon icon="mdi:file-pdf-box" class="text-lg" /></a>
            <a :href="`/admin/reports/best-selling-excel?period=${period}`" class="p-1.5 text-green-500 bg-green-100 border border-green-400 hover:text-green-500 hover:bg-green-50 rounded-lg transition" title="Excel"><Icon icon="mdi:file-excel" class="text-lg" /></a>
          </div>
        </div>
        <div v-if="bestSelling.length" class="space-y-3">
          <div v-for="(item, i) in bestSelling" :key="i" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
            <span class="w-8 h-8 bg-primary/10 text-primary rounded-lg flex items-center justify-center font-bold text-sm">{{ i + 1 }}</span>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-text truncate">{{ item.product_name }}</p>
              <p class="text-xs text-gray-500">{{ item.total_qty }} terjual</p>
            </div>
            <p class="text-sm font-semibold text-primary">Rp {{ fmt(item.total_revenue) }}</p>
          </div>
        </div>
        <p v-else class="text-sm text-gray-500 text-center py-10">Belum ada data</p>
      </div>
    </div>

    <!-- Export -->
    <div class="mt-6 bg-white rounded-2xl border border-gray-100 p-6">
      <h2 class="font-semibold text-text mb-4">Export Laporan Penjualan</h2>
      <div class="flex gap-3">
        <a :href="`/admin/reports/export-pdf?period=${period}`" class="px-5 py-2.5 bg-red-500 text-white text-sm font-semibold rounded-xl hover:bg-red-600 transition flex items-center gap-2"><Icon icon="mdi:file-pdf-box" /> Export PDF</a>
        <a :href="`/admin/reports/export-excel?period=${period}`" class="px-5 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition flex items-center gap-2"><Icon icon="mdi:file-excel" /> Export Excel</a>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import VueApexCharts from 'vue3-apexcharts';
const apexchart = VueApexCharts;

const props = defineProps({ stats: Object, bestSelling: Array, salesData: Array, period: String });

const periods = [
  { value: 'daily', label: 'Hari Ini' },
  { value: 'weekly', label: 'Minggu Ini' },
  { value: 'monthly', label: 'Bulan Ini' },
  { value: 'yearly', label: 'Tahun Ini' },
];

const chartOpts = { chart: { toolbar: { show: false }, fontFamily: 'Inter' }, colors: ['#ff970f'], xaxis: { categories: props.salesData.map(d => d.date) }, yaxis: { labels: { formatter: v => `Rp ${fmt(v)}` } }, tooltip: { y: { formatter: v => `Rp ${fmt(v)}` } }, dataLabels: { enabled: false }, plotOptions: { bar: { borderRadius: 6, columnWidth: '60%' } }, grid: { borderColor: '#f3f4f6' } };
const chartSeries = computed(() => [{ name: 'Penjualan', data: props.salesData.map(d => d.total) }]);

function fmt(p) { return Number(p || 0).toLocaleString('id-ID'); }
</script>
