<template>
  <Head title="Dashboard Admin" />
  <AdminLayout>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-text">Dashboard</h1>
      <p class="text-gray-500 text-sm">Selamat datang, {{ $page.props.auth.user?.name }}</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div v-for="(stat, i) in statCards" :key="i" class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">{{ stat.label }}</p>
            <p class="text-2xl font-bold text-text mt-1">{{ stat.value }}</p>
          </div>
          <div :class="['w-12 h-12 rounded-xl flex items-center justify-center', stat.bgClass]">
            <Icon :icon="stat.icon" :class="['text-2xl', stat.iconClass]" />
          </div>
        </div>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Chart -->
      <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="font-semibold text-text mb-4">Penjualan Bulanan {{ new Date().getFullYear() }}</h2>
        <apexchart type="area" height="300" :options="chartOptions" :series="chartSeries" />
      </div>

      <!-- Recent Orders -->
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="font-semibold text-text mb-4">Pesanan Terbaru</h2>
        <div class="space-y-3">
          <Link v-for="order in rRecentOrders" :key="order.id" :href="`/admin/orders/${order.id}`" class="block p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
            <div class="flex items-center justify-between mb-1">
              <span class="text-sm font-medium">{{ order.order_code }}</span>
              <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusClass(order.status_color)]">{{ order.status_label }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500">
              <span>{{ order.user_name }}</span>
              <span class="font-medium text-primary">Rp {{ fmt(order.total_amount) }}</span>
            </div>
          </Link>
          <p v-if="!rRecentOrders.length" class="text-sm text-gray-500 text-center py-4">Belum ada pesanan</p>
        </div>
      </div>
    </div>

    <!-- Quick alerts -->
    <div class="grid sm:grid-cols-2 gap-4 mt-6">
      <div v-if="rStats.pendingPayments > 0" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-center gap-3">
        <Icon icon="mdi:clock-alert" class="text-2xl text-yellow-600" />
        <div><p class="font-medium text-yellow-800">{{ rStats.pendingPayments }} pembayaran menunggu verifikasi</p><Link href="/admin/orders?status=menunggu_verifikasi" class="text-sm text-yellow-600 hover:underline">Lihat →</Link></div>
      </div>
      <div v-if="rStats.lowStockProducts > 0" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
        <Icon icon="mdi:alert" class="text-2xl text-red-600" />
        <div><p class="font-medium text-red-800">{{ rStats.lowStockProducts }} produk stok hampir habis</p><Link href="/admin/stock" class="text-sm text-red-600 hover:underline">Lihat →</Link></div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import VueApexCharts from 'vue3-apexcharts';

const apexchart = VueApexCharts;

const props = defineProps({ stats: Object, salesChart: Array, recentOrders: Array, monthlyData: Array });

// Reactive state initialized from props
const rStats = ref(props.stats);
const rRecentOrders = ref(props.recentOrders);
const rMonthlyData = ref(props.monthlyData);
let pollInterval = null;

onMounted(() => {
  pollInterval = setInterval(async () => {
    try {
      const res = await fetch('/admin/realtime');
      if (res.ok) {
        const data = await res.json();
        rStats.value = data.stats;
        rRecentOrders.value = data.recentOrders;
        rMonthlyData.value = data.monthlyData;
      }
    } catch (e) {
      console.error('Realtime polling failed:', e);
    }
  }, 3000); // Poll every 5 seconds
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});

const statCards = computed(() => [
  { label: 'Total Produk', value: rStats.value.totalProducts, icon: 'mdi:package-variant-closed', bgClass: 'bg-blue-50', iconClass: 'text-blue-500' },
  { label: 'Total Pesanan', value: rStats.value.totalOrders, icon: 'mdi:cart-check', bgClass: 'bg-green-50', iconClass: 'text-green-500' },
  { label: 'Total Penjualan', value: `Rp ${fmt(rStats.value.totalSales)}`, icon: 'mdi:cash-multiple', bgClass: 'bg-orange-50', iconClass: 'text-orange-500' },
  { label: 'Total Pelanggan', value: rStats.value.totalUsers, icon: 'mdi:account-group', bgClass: 'bg-purple-50', iconClass: 'text-purple-500' },
]);

const chartOptions = {
  chart: { toolbar: { show: false }, fontFamily: 'Inter', animations: { enabled: true, dynamicAnimation: { speed: 1000 } } },
  colors: ['#ff970f'],
  stroke: { curve: 'smooth', width: 3 },
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
  xaxis: { categories: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] },
  yaxis: { labels: { formatter: (v) => `Rp ${fmt(v)}` } },
  tooltip: { y: { formatter: (v) => `Rp ${fmt(v)}` } },
  dataLabels: { enabled: false },
  grid: { borderColor: '#f3f4f6' },
};

const chartSeries = computed(() => [{ name: 'Penjualan', data: rMonthlyData.value }]);

function fmt(p) { return Number(p || 0).toLocaleString('id-ID'); }
function statusClass(c) { return { warning:'bg-yellow-100 text-yellow-700',info:'bg-blue-100 text-blue-700',primary:'bg-orange-100 text-orange-700',success:'bg-green-100 text-green-700',danger:'bg-red-100 text-red-700' }[c]||'bg-gray-100 text-gray-700'; }
</script>
