<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';

const mobileMenuOpen = ref(false)
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <header class="bg-primary text-white shadow-md">
      <div class="container mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
          <div class="flex items-center">
            <img src="@/assets/vue.svg" alt="Logo" class="h-12 mr-3">
            <h1 class="text-xl font-bold">สำนักงานปลัดกระทรวงสาธารณสุข</h1>
          </div>
          <nav class="hidden md:flex space-x-4">
            <router-link to="/" class="hover:text-secondary transition-colors">หน้าหลัก</router-link>
            <router-link to="/about" class="hover:text-secondary transition-colors">เกี่ยวกับเรา</router-link>
            <router-link to="/news" class="hover:text-secondary transition-colors">ข่าวสาร</router-link>
            <router-link to="/contact" class="hover:text-secondary transition-colors">ติดต่อเรา</router-link>
            <router-link to="/login" class="bg-white text-primary px-3 py-1 rounded hover:bg-secondary hover:text-white transition-colors">เข้าสู่ระบบ</router-link>
          </nav>
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-white">
            <span class="sr-only">เมนู</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
      <!-- เมนูมือถือ -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-primary">
        <div class="px-2 pt-2 pb-3 space-y-1">
          <router-link to="/" class="block px-3 py-2 hover:bg-blue-700 rounded">หน้าหลัก</router-link>
          <router-link to="/about" class="block px-3 py-2 hover:bg-blue-700 rounded">เกี่ยวกับเรา</router-link>
          <router-link to="/news" class="block px-3 py-2 hover:bg-blue-700 rounded">ข่าวสาร</router-link>
          <router-link to="/contact" class="block px-3 py-2 hover:bg-blue-700 rounded">ติดต่อเรา</router-link>
          <router-link to="/login" class="block px-3 py-2 bg-white text-primary rounded">เข้าสู่ระบบ</router-link>
        </div>
      </div>
    </header>

    <main class="flex-grow">
      <div class="container mx-auto px-4 py-6">
        <slot />
      </div>
    </main>

    <footer class="bg-accent text-white">
      <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between">
          <div class="mb-4 md:mb-0">
            <h2 class="text-lg font-semibold mb-2">สำนักงานปลัดกระทรวงสาธารณสุข</h2>
            <p>ที่อยู่: กระทรวงสาธารณสุข ถนนติวานนท์ อำเภอเมือง จังหวัดนนทบุรี 11000</p>
            <p>โทรศัพท์: 0-2590-1000</p>
          </div>
          <div class="flex flex-col">
            <h3 class="text-lg font-semibold mb-2">ลิงก์ที่เกี่ยวข้อง</h3>
            <a href="https://www.moph.go.th" target="_blank" class="hover:text-secondary">กระทรวงสาธารณสุข</a>
            <a href="https://ops.moph.go.th" target="_blank" class="hover:text-secondary">สำนักงานปลัดกระทรวงสาธารณสุข</a>
            <a href="https://www.moph.go.th/index.php/law" target="_blank" class="hover:text-secondary">กฎหมายที่เกี่ยวข้อง</a>
          </div>
        </div>
        <div class="mt-6 text-center text-sm">
          <p>&copy; {{ new Date().getFullYear() }} สำนักงานปลัดกระทรวงสาธารณสุข. สงวนลิขสิทธิ์.</p>
          <p class="mt-1">
            <router-link to="/privacy-policy" class="hover:text-secondary">นโยบายความเป็นส่วนตัว</router-link> | 
            <router-link to="/terms-of-service" class="hover:text-secondary">ข้อกำหนดการใช้งาน</router-link>
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>