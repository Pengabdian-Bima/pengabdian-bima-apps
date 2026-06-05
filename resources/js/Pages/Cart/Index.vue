  <template>
  <Head title="Keranjang" />
  <UserLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 class="text-3xl font-bold text-text mb-8">Keranjang Belanja</h1>

      <div v-if="items.length">
        <div class="space-y-4">
          <div v-for="item in items" :key="item.id" class="bg-white rounded-2xl border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-orange-50 to-amber-50 flex-shrink-0 flex items-center justify-center overflow-hidden">
              <img v-if="item.thumbnail_url" :src="item.thumbnail_url" class="w-full h-full object-cover">
              <Icon v-else icon="mdi:food-croissant" class="text-3xl text-primary/30" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-text truncate">{{ item.product_name }}</h3>
              <p class="text-primary font-bold">Rp {{ formatPrice(item.price) }}</p>
            </div>
            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
              <button @click="updateQty(item, item.qty - 1)" :disabled="item.qty <= 1" class="px-3 py-1.5 hover:bg-gray-50 disabled:opacity-30"><Icon icon="mdi:minus" /></button>
              <span class="px-3 py-1.5 text-sm font-medium min-w-[40px] text-center">{{ item.qty }}</span>
              <button @click="updateQty(item, item.qty + 1)" :disabled="item.qty >= item.stock" class="px-3 py-1.5 hover:bg-gray-50 disabled:opacity-30"><Icon icon="mdi:plus" /></button>
            </div>
            <p class="text-lg font-bold text-text min-w-[100px] text-right hidden sm:block">Rp {{ formatPrice(item.subtotal) }}</p>
            <button @click="removeItem(item)" class="p-2 text-gray-400 hover:text-danger hover:bg-red-50 rounded-lg transition">
              <Icon icon="mdi:trash-can-outline" class="text-xl" />
            </button>
          </div>
        </div>

        <div class="mt-8 bg-white rounded-2xl border border-gray-100 p-6">
          <div class="flex items-center justify-between text-xl font-bold">
            <span class="text-text">Total</span>
            <span class="text-primary">Rp {{ formatPrice(total) }}</span>
          </div>
          <Link href="/checkout" class="mt-4 w-full py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 flex items-center justify-center gap-2">
            <Icon icon="mdi:cart-check" class="text-xl" /> Checkout
          </Link>
        </div>
      </div>

      <div v-else class="text-center py-20">
        <Icon icon="mdi:cart-off" class="text-6xl text-gray-300 mx-auto mb-4" />
        <p class="text-gray-500 mb-4">Keranjang belanja kosong</p>
        <Link href="/produk" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl hover:shadow-lg transition">
          <Icon icon="mdi:shopping" /> Mulai Belanja
        </Link>
      </div>
    </div>
  </UserLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';

defineProps({ items: Array, total: Number });

function formatPrice(p) { return Number(p).toLocaleString('id-ID'); }

function updateQty(item, qty) {
  router.put(`/keranjang/${item.id}`, { qty }, { preserveScroll: true });
}

function removeItem(item) {
  router.delete(`/keranjang/${item.id}`, { preserveScroll: true });
}
</script>
