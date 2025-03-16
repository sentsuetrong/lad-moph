<script setup lang="ts">
import { ref } from 'vue';
import { RouterView } from 'vue-router'

const mobileMenuOpen = ref(false)
</script>

<template>
  <div class="sticky top-0 bg-white z-50">
    <div class="container mx-auto py-4">
      <div class="flex flex-col lg:flex-row justify-between items-center">
        <div class="w-full lg:w-fit px-4 lg:px-0 flex justify-between gap-x-4">
          <router-view to="/">
            <div class="flex justify-center items-center select-none">
              <img src="/src/assets/vue.svg" alt="Logo" class="h-12 mr-3 logo-animation" draggable="false">
              <div class="min-w-fit text-emerald-800 font-bold flex flex-col-reverse">
                <h1 class="text-xl">กองกฎหมาย</h1>
                <p class="text-sm">สำนักงานปลัดกระทรวงสาธารณสุข</p>
              </div>
            </div>
          </router-view>
          <button @click="mobileMenuOpen = !mobileMenuOpen"
            class="bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xl leading-0 px-3 rounded lg:hidden flex items-center transition-colors cursor-pointer">
            <i class="fi fi-rr-menu-burger"></i>
          </button>
        </div>

        <nav class="w-full lg:w-auto lg:flex" :class="mobileMenuOpen ? 'pt-4 block' : 'hidden'">
          <ul class="flex flex-col lg:flex-row lg:items-center">
            <ul class="flex flex-col lg:flex-row lg:items-center">
              <li class="min-w-fit">
                <a href="#" class="text-gray-500 hover:text-emerald-600 px-4 py-2 rounded block transition-colors">
                  <i class="fi fi-rr-home mr-2 align-middle"></i>กลับหน้าหลัก
                </a>
              </li>
              <li class="min-w-fit">
                <a href="#" class="text-gray-500 hover:text-emerald-600 px-4 py-2 rounded block transition-colors">
                  <i class="fi fi-rr-dashboard-monitor mr-2 align-middle"></i>แดชบอร์ด
                </a>
              </li>
              <li class="min-w-fit"><a href="#"
                  class="text-gray-500 hover:text-emerald-600 px-4 py-2 rounded block transition-colors"><i
                    class="fas fa-info-circle mr-2"></i>เกี่ยวกับเรา</a></li>
              <li class="min-w-fit"><a href="#"
                  class="text-gray-500 hover:text-emerald-600 px-4 py-2 rounded block transition-colors"><i
                    class="fas fa-chart-bar mr-2"></i>บริการ</a></li>
              <li class="min-w-fit"><a href="#"
                  class="text-gray-500 hover:text-emerald-600 px-4 py-2 rounded block transition-colors">
                  <i class="fi fi-rr-envelope mr-2 align-middle"></i>
                  ติดต่อเรา</a></li>
              <li class="min-w-fit"><a href="#"
                  class="text-gray-500 hover:text-emerald-600 px-4 py-2 rounded block transition-colors"><i
                    class="fas fa-sign-in-alt mr-2"></i>เข้าสู่ระบบ</a></li>
            </ul>
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <h1 class="text-6xl text-emerald-500">Hello world</h1>

  <div class="card">
    <h2 class="text-emerald-500">Card</h2>
  </div>

  <router-view />
</template>
