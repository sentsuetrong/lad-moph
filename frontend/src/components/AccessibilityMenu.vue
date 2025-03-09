<script setup lang="ts">
import { ref } from 'vue'
import { useAccessibilityStore } from '../stores/accessibility'

const accessibilityStore = useAccessibilityStore()
const menuOpen = ref(false)

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value
}
</script>

<template>
  <div class="accessibility-menu">
    <button 
      @click="toggleMenu" 
      class="accessibility-toggle"
      aria-label="การตั้งค่าการเข้าถึง"
      :aria-expanded="menuOpen"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" v-if="accessibilityStore.theme === 'dark'" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" v-if="accessibilityStore.theme === 'light'" />
      </svg>
      <span class="sr-only">การตั้งค่าการเข้าถึง</span>
    </button>
    
    <transition name="fade">
      <div v-if="menuOpen" class="accessibility-panel" role="menu" aria-label="การตั้งค่าการเข้าถึง">
        <div class="accessibility-header">
          <h3>การตั้งค่าการเข้าถึง</h3>
          <button 
            @click="toggleMenu" 
            class="close-button"
            aria-label="ปิดเมนู"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="accessibility-options">
          <!-- ตั้งค่าธีม -->
          <div class="option-group">
            <div class="option-header">
              <span class="option-label">รูปแบบธีม</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="option-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
              </svg>
            </div>
            <div class="option-controls theme-controls">
              <button 
                @click="accessibilityStore.toggleTheme" 
                :class="[
                  'theme-button', 
                  { 'active': accessibilityStore.theme === 'light' }
                ]"
                :aria-pressed="accessibilityStore.theme === 'light'"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="theme-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="5"></circle>
                  <line x1="12" y1="1" x2="12" y2="3"></line>
                  <line x1="12" y1="21" x2="12" y2="23"></line>
                  <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                  <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                  <line x1="1" y1="12" x2="3" y2="12"></line>
                  <line x1="21" y1="12" x2="23" y2="12"></line>
                  <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                  <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
                <span>สว่าง</span>
              </button>
              <button 
                @click="accessibilityStore.toggleTheme" 
                :class="[
                  'theme-button', 
                  { 'active': accessibilityStore.theme === 'dark' }
                ]"
                :aria-pressed="accessibilityStore.theme === 'dark'"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="theme-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
                <span>มืด</span>
              </button>
            </div>
          </div>
          
          <!-- ตั้งค่าขนาดตัวอักษร -->
          <div class="option-group">
            <div class="option-header">
              <span class="option-label">ขนาดตัวอักษร</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="option-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="4 7 4 4 20 4 20 7"></polyline>
                <line x1="9" y1="20" x2="15" y2="20"></line>
                <line x1="12" y1="4" x2="12" y2="20"></line>
              </svg>
            </div>
            <div class="option-controls">
              <div class="font-size-slider-container">
                <button 
                  @click="accessibilityStore.decreaseFontSize" 
                  class="font-size-button"
                  aria-label="ลดขนาดตัวอักษร"
                  :disabled="accessibilityStore.fontSize <= 80"
                >
                  <span>A-</span>
                </button>
                
                <div class="font-size-slider">
                  <div class="font-size-track">
                    <div 
                      class="font-size-fill" 
                      :style="{ width: `${((accessibilityStore.fontSize - 80) / 120) * 100}%` }"
                    ></div>
                  </div>
                  <div class="font-size-value">{{ accessibilityStore.fontSize }}%</div>
                </div>
                
                <button 
                  @click="accessibilityStore.increaseFontSize" 
                  class="font-size-button"
                  aria-label="เพิ่มขนาดตัวอักษร"
                  :disabled="accessibilityStore.fontSize >= 200"
                >
                  <span>A+</span>
                </button>
                
                <button 
                  @click="accessibilityStore.resetFontSize" 
                  class="reset-button"
                  aria-label="รีเซ็ตขนาดตัวอักษร"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                    <path d="M3 3v5h5"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- ตั้งค่าคอนทราสต์สูงและลดการเคลื่อนไหว -->
          <div class="option-group">
            <div class="option-header">
              <span class="option-label">การเข้าถึงเพิ่มเติม</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="option-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                <line x1="15" y1="9" x2="15.01" y2="9"></line>
              </svg>
            </div>
            <div class="option-controls extra-controls">
              <label class="switch-control">
                <input 
                  type="checkbox" 
                  :checked="accessibilityStore.highContrast"
                  @change="accessibilityStore.toggleHighContrast"
                />
                <span class="switch-slider"></span>
                <span class="switch-label">คอนทราสต์สูง</span>
              </label>
              
              <label class="switch-control">
                <input 
                  type="checkbox" 
                  :checked="accessibilityStore.reducedMotion"
                  @change="accessibilityStore.toggleReducedMotion"
                />
                <span class="switch-slider"></span>
                <span class="switch-label">ลดการเคลื่อนไหว</span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>