<script setup lang="ts">
import { ref } from 'vue';
import { RouterView } from 'vue-router'

import ExampleThaiDatetimePicker from './components/ExampleThaiDatetimePicker.vue';

const mobileMenuOpen = ref(false)
</script>

<template>
  <div class="sticky top-0 bg-white/80 backdrop-blur-md z-50">
    <div class="container max-w-7xl mx-auto px-4 py-2">
      <div class="flex flex-col lg:flex-row justify-between items-center">
        <div class="w-full lg:w-fit flex justify-between gap-x-4">
          <router-view to="/">
            <div class="flex justify-center items-center select-none">
              <img src="/src/assets/logo_MOPH@0.25x.png" alt="Logo" class="h-12 mr-3 logo-animation" draggable="false">
              <div class="min-w-fit text-emerald-800 font-bold flex flex-col-reverse"
                alt="กองกฎหมาย สำนักงานปลัดกระทรวงสาธารณสุข">
                <h1 alt="Legal Affairs Divisions">กองกฎหมาย</h1>
                <p class="text-sm" alt="Office of the Permanent Secretary, Ministry of Publich Health">
                  สำนักงานปลัดกระทรวงสาธารณสุข</p>
              </div>
            </div>
          </router-view>
          <button @click="mobileMenuOpen = !mobileMenuOpen"
            class="bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xl leading-0 px-3 rounded lg:hidden flex items-center transition-colors cursor-pointer">
            <i class="fi fi-rr-menu-burger"></i>
          </button>
        </div>

        <nav class="w-full lg:w-auto bg-gray-50 lg:bg-transparent mt-4 lg:mt-0 lg:flex"
          :class="mobileMenuOpen ? 'block' : 'hidden'">
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
                    class="fi fi-rr-rectangle-list mr-2 align-middle"></i>บริการ<i
                    class="fi fi-rr-angle-small-down ml-1 align-middle"></i></a></li>
              <li class="min-w-fit"><a href="#"
                  class="text-gray-500 hover:text-emerald-600 px-4 py-2 rounded block transition-colors"><i
                    class="fi fi-rr-envelope mr-2 align-middle"></i>ติดต่อเรา</a></li>
            </ul>
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <main class="container mx-auto flex flex-col">
    <h1 class="text-6xl text-emerald-500">Hello world</h1>

    <example-thai-datetime-picker />
  </main>

  <router-view />

  <footer class="w-full bg-emerald-600 border-t-2 border-emerald-500">
    <div class="container mx-auto px-4 py-1 flex justify-between items-center">
      <span class="text-xs text-emerald-950 truncate">ตึกสำนักงานปลัดกระทรวงสาธารณสุข อาคาร 6 ชั้น 3
        ซอยติวานนท์ ถนนติวานนท์
        ตำบลตลาดขวัญ อำเภอเมือง จังหวัดนนทบุรี 11000
      </span>
      <a href="#"
        class=" min-w-max text-xs text-emerald-950 hover:text-black pl-4 lg:px-4 rounded block transition-colors"><i
          class="fi fi-rr-sign-in-alt mr-2 align-middle"></i>สำหรับเจ้าหน้าที่</a>
    </div>
  </footer>
</template>
