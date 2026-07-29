<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Mobile Overlay -->
    <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-300" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>
    </Transition>

    <!-- Sidebar -->
    <aside :class="['fixed inset-y-0 left-0 z-40 bg-gray-900 text-white transition-all duration-300 flex flex-col', sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-20']">
      <div class="flex items-center gap-3 h-20 px-4 border-b border-gray-800">
        <div class="w-full lg:h-12 h-10 bg-transparent rounded-xl flex items-center justify-center flex-shrink-0">
          <img src="/img/logo-putih.png" alt="Logo" class="w-full h-full object-contain" />
        </div>
      </div>

      <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
        <Link v-for="item in menuItems" :key="item.href" :href="item.href"
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
            isActive(item.href) ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white']">
          <Icon :icon="item.icon" class="text-xl flex-shrink-0" />
          <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100">
            <span v-if="sidebarOpen" class="whitespace-nowrap">{{ item.label }}</span>
          </Transition>
        </Link>
      </nav>

      <div class="p-3 border-t border-gray-800 space-y-1">
        <Link href="/admin/profile"
          :class="['flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
            isActive('/admin/profile') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white']">
          <Icon icon="mdi:account-cog" class="text-xl flex-shrink-0" />
          <span v-if="sidebarOpen">Edit Profil</span>
        </Link>
        <button @click="showLogoutModal = true"
          class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:bg-red-500/10 hover:text-red-400 transition-all duration-200 cursor-pointer">
          <Icon icon="mdi:logout" class="text-xl flex-shrink-0" />
          <span v-if="sidebarOpen">Keluar</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div :class="['flex-1 transition-all duration-300 min-w-0 flex flex-col', sidebarOpen ? 'lg:ml-64' : 'lg:ml-20']">
      <!-- Top bar -->
      <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-gray-100 h-16 flex items-center justify-between px-6">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-100 transition border border-primary text-primary">
          <Icon :icon="sidebarOpen ? 'mdi:menu-open' : 'mdi:menu'" class="text-xl text-primary" />
        </button>
        <div class="flex items-center gap-3">
          <div class="text-right hidden sm:block">
            <p class="text-sm font-semibold text-gray-800">{{ $page.props.auth.user?.name }}</p>
            <p class="text-xs text-gray-500">{{ $page.props.auth.user?.role === 'kasir' ? 'Kasir' : 'Administrator' }}</p>
          </div>
          <div class="w-10 h-10 bg-gradient-to-br from-primary to-primary-dark rounded-full flex items-center justify-center">
            <span class="text-white font-bold">{{ $page.props.auth.user?.name?.charAt(0) }}</span>
          </div>
        </div>
      </header>

      <!-- Flash messages -->
      <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0">
        <div v-if="$page.props.flash.success" class="fixed top-20 right-4 z-50 bg-success text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-2">
          <Icon icon="mdi:check-circle" class="text-xl" /> {{ $page.props.flash.success }}
        </div>
      </Transition>
      <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0">
        <div v-if="$page.props.flash.error" class="fixed top-20 right-4 z-50 bg-danger text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-2">
          <Icon icon="mdi:alert-circle" class="text-xl" /> {{ $page.props.flash.error }}
        </div>
      </Transition>

      <main class="p-6">
        <slot />
      </main>
    </div>

    <!-- Logout Confirmation Modal -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showLogoutModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showLogoutModal = false">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95" appear>
          <div v-if="showLogoutModal" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon icon="mdi:logout" class="text-2xl text-danger" />
            </div>
            <h3 class="text-lg font-bold text-text">Konfirmasi Keluar</h3>
            <p class="text-sm text-gray-500 mt-2">Apakah Anda yakin ingin mengakhiri sesi ini?</p>
            <div class="flex gap-3 mt-6">
              <button @click="showLogoutModal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">Batal</button>
              <Link href="/logout" method="post" as="button" class="flex-1 py-2.5 bg-danger text-white text-sm font-semibold rounded-xl hover:bg-red-600 transition cursor-pointer">Ya, Keluar</Link>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const sidebarOpen = ref(true);
const showLogoutModal = ref(false);
const page = usePage();

function handleResize() {
  if (typeof window !== 'undefined') {
    sidebarOpen.value = window.innerWidth >= 1024;
  }
}

onMounted(() => {
  handleResize();
  window.addEventListener('resize', handleResize);
});
onUnmounted(() => window.removeEventListener('resize', handleResize));

const menuItems = computed(() => {
  const role = page.props.auth.user?.role;
  if (role === 'kasir') {
    return [
      { href: '/admin', label: 'Dashboard', icon: 'mdi:view-dashboard' },
      { href: '/admin/cashier', label: 'Kasir', icon: 'mdi:cash-register' },
      { href: '/admin/stock', label: 'Stok Produk', icon: 'mdi:warehouse' },
      { href: '/admin/orders', label: 'Pemesanan', icon: 'mdi:cart-check' },
    ];
  }
  return [
    { href: '/admin', label: 'Dashboard', icon: 'mdi:view-dashboard' },
    { href: '/admin/cashier', label: 'Kasir', icon: 'mdi:cash-register' },
    { href: '/admin/products', label: 'Produk', icon: 'mdi:package-variant-closed' },
    { href: '/admin/categories', label: 'Kategori', icon: 'mdi:tag-multiple' },
    { href: '/admin/stock', label: 'Stok Produk', icon: 'mdi:warehouse' },
    { href: '/admin/orders', label: 'Pemesanan', icon: 'mdi:cart-check' },
    { href: '/admin/reports', label: 'Laporan', icon: 'mdi:chart-bar' },
  ];
});

function isActive(href) {
  if (href === '/admin') return page.url === '/admin';
  return page.url.startsWith(href);
}
</script>
