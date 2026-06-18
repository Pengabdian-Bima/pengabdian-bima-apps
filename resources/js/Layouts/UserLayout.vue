<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800 shadow-sm transition-colors duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <Link href="/" class="flex items-center gap-2">
            <div class="lg:w-72 h-28 w-42 bg-transparent rounded-xl flex items-center justify-center">
              <img :src="isDark ? '/img/logo-putih.png' : '/img/logo-hitam.png'" alt="Logo" class="w-full h-full object-contain transition-all duration-300" />
            </div>
          </Link>

          <div class="hidden md:flex items-center gap-1">
            <Link v-for="item in navItems" :key="item.href" :href="item.href"
              :class="['px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                isActive(item.href) ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white']">
              {{ item.label }}
            </Link>
          </div>

          <div class="flex items-center gap-3">
            <template v-if="$page.props.auth.user">
              <Link href="/keranjang" class="relative p-2 rounded-lg hover:bg-gray-100 transition">
                <Icon icon="mdi:cart-outline" class="text-xl text-gray-600" />
                <span v-if="$page.props.cartCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-primary text-white text-xs rounded-full flex items-center justify-center font-bold">{{ $page.props.cartCount }}</span>
              </Link>
              <div class="relative" ref="profileMenu">
                <button @click="showMenu = !showMenu" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition">
                  <div class="w-8 h-8 bg-gradient-to-br from-primary to-primary-dark rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-bold">{{ $page.props.auth.user.name.charAt(0) }}</span>
                  </div>
                  <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ $page.props.auth.user.name }}</span>
                </button>
                <Transition enter-active-class="transition duration-150" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
                  leave-active-class="transition duration-100" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                  <div v-if="showMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                    <Link href="/pesanan" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                      <Icon icon="mdi:package-variant-closed" class="text-lg" /> Pesanan Saya
                    </Link>
                    <Link href="/profil" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                      <Icon icon="mdi:account-outline" class="text-lg" /> Profil
                    </Link>
                    <hr class="my-1 border-gray-100">
                    <Link href="/logout" method="post" as="button" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-danger hover:bg-red-50">
                      <Icon icon="mdi:logout" class="text-lg" /> Keluar
                    </Link>
                  </div>
                </Transition>
              </div>
            </template>
            <div v-else class="hidden md:flex items-center gap-2">
              <Link href="/login" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition">Masuk</Link>
              <Link href="/register" class="px-5 py-2 bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300">Daftar</Link>
            </div>
            <button @click="toggleTheme" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 border-1 border-primary dark:border-primary hover:border-primary dark:hover:border-primary transition">
              <Icon :icon="isDark ? 'streamline-ultimate:weather-sun' : 'fluent:weather-moon-32-light'" class="text-xl text-primary dark:text-primary" />
            </button>
            <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-600 transition bg-primary text-white cursor-pointer">
              <Icon :icon="mobileMenu ? 'mdi:close' : 'mdi:menu'" class="text-xl text-white" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="mobileMenu" class="md:hidden bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 px-4 py-3 space-y-1 shadow-lg absolute w-full left-0 transition-colors duration-300">
          <Link v-for="item in navItems" :key="item.href" :href="item.href"
            :class="['block px-4 py-3 rounded-lg text-sm font-medium transition', isActive(item.href) ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800']">
            {{ item.label }}
          </Link>
          <template v-if="!$page.props.auth.user">
            <div class="pt-2 pb-1 border-t border-gray-100 dark:border-gray-800 mt-2 space-y-2">
              <Link href="/login" class="block px-4 py-3 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-center border border-gray-200 dark:border-gray-700">Masuk</Link>
              <Link href="/register" class="block px-4 py-3 rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary-dark text-center">Daftar</Link>
            </div>
          </template>
        </div>
      </Transition>
    </nav>

    <!-- Flash messages -->
    <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="$page.props.flash.success" class="fixed top-20 right-4 z-50 bg-success text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-2 animate-pulse">
        <Icon icon="mdi:check-circle" class="text-xl" /> {{ $page.props.flash.success }}
      </div>
    </Transition>
    <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0">
      <div v-if="$page.props.flash.error" class="fixed top-20 right-4 z-50 bg-danger text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-2">
        <Icon icon="mdi:alert-circle" class="text-xl" /> {{ $page.props.flash.error }}
      </div>
    </Transition>

    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div>
            <div class="flex items-center gap-2 -mb-2">
              <div class="lg:w-72 h-28 w-42 flex items-center justify-center">
                <img src="/img/logo-putih.png" alt="Logo" class="w-full h-full object-contain" />
              </div>
            </div>
            <p class="text-gray-400 text-sm leading-relaxed">Produsen Biskuit Ikan Huluu Danau Limboto yang kaya protein dan berkualitas tinggi dari Gorontalo.</p>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Kontak</h3>
            <div class="space-y-2 text-sm text-gray-400">
              <p class="flex items-center gap-2"><Icon icon="mdi:map-marker" class="text-primary" /> Gorontalo, Indonesia</p>
              <p class="flex items-center gap-2"><Icon icon="mdi:phone" class="text-primary" /> +62 812-3456-7890</p>
              <p class="flex items-center gap-2"><Icon icon="mdi:email" class="text-primary" /> info@udflamboyan.com</p>
            </div>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Sosial Media</h3>
            <div class="flex gap-3">
              <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary transition-colors duration-300">
                <Icon icon="mdi:instagram" class="text-xl" />
              </a>
              <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary transition-colors duration-300">
                <Icon icon="mdi:facebook" class="text-xl" />
              </a>
              <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary transition-colors duration-300">
                <Icon icon="mdi:whatsapp" class="text-xl" />
              </a>
            </div>
          </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500 text-sm">
          &copy; {{ new Date().getFullYear() }} UD Flamboyan. All rights reserved.
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const showMenu = ref(false);
const mobileMenu = ref(false);
const profileMenu = ref(null);
const isDark = ref(false);

function toggleTheme() {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.setItem('theme', 'dark');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('theme', 'light');
  }
}

const navItems = [
  { href: '/', label: 'Beranda' },
  { href: '/produk', label: 'Produk' },
];

const page = usePage();

function isActive(href) {
  if (href === '/') return page.url === '/';
  return page.url.startsWith(href);
}

function handleClickOutside(e) {
  if (profileMenu.value && !profileMenu.value.contains(e.target)) {
    showMenu.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  
  // Theme initialization
  if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true;
    document.documentElement.classList.add('dark');
  } else {
    isDark.value = false;
    document.documentElement.classList.remove('dark');
  }
});
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>
