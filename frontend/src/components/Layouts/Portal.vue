<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAccessibilityStore } from '../../stores/accessibility'
import AccessibilityMenu from '../AccessibilityMenu.vue'

const mobileMenuOpen = ref(false)
const accessibilityStore = useAccessibilityStore()

// เมื่อ component ถูกโหลด จะเริ่มใช้การตั้งค่าการเข้าถึง
onMounted(() => {
  accessibilityStore.initAccessibilitySettings()
})
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <header class="bg-primary shadow-lg">
      <div class="container mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
          <div class="flex items-center">
            <img src="/src/assets/vue.svg" alt="Logo" class="h-12 mr-3 logo-animation">
            <h1 class="text-xl md:text-2xl font-bold agency-name">สำนักงานปลัดกระทรวงสาธารณสุข</h1>
          </div>
          <div class="flex items-center gap-2">
            <!-- เพิ่ม Accessibility Menu -->
            <AccessibilityMenu class="mr-2" />
            
            <nav class="hidden md:flex space-x-1">
              <router-link to="/" class="nav-link">
                <span>หน้าหลัก</span>
              </router-link>
              <router-link to="/about" class="nav-link">
                <span>เกี่ยวกับเรา</span>
              </router-link>
              <router-link to="/news" class="nav-link">
                <span>ข่าวสาร</span>
              </router-link>
              <router-link to="/contact" class="nav-link">
                <span>ติดต่อเรา</span>
              </router-link>
              <router-link to="/login" class="login-button">
                <svg xmlns="http://www.w3.org/2000/svg" class="login-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                  <polyline points="10 17 15 12 10 7"></polyline>
                  <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                <span>เข้าสู่ระบบ</span>
              </router-link>
            </nav>
            <button 
              @click="mobileMenuOpen = !mobileMenuOpen" 
              class="md:hidden hamburger-button"
              aria-label="เปิด/ปิดเมนู"
              :aria-expanded="mobileMenuOpen"
            >
              <span class="sr-only">เมนู</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
      <!-- เมนูมือถือ -->
      <div v-if="mobileMenuOpen" class="mobile-menu md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1">
          <router-link to="/" class="mobile-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="mobile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
              <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>หน้าหลัก</span>
          </router-link>
          <router-link to="/about" class="mobile-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="mobile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>เกี่ยวกับเรา</span>
          </router-link>
          <router-link to="/news" class="mobile-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="mobile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>ข่าวสาร</span>
          </router-link>
          <router-link to="/contact" class="mobile-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="mobile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <span>ติดต่อเรา</span>
          </router-link>
          <router-link to="/login" class="mobile-login">
            <svg xmlns="http://www.w3.org/2000/svg" class="mobile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
              <polyline points="10 17 15 12 10 7"></polyline>
              <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            <span>เข้าสู่ระบบ</span>
          </router-link>
        </div>
      </div>
    </header>

    <main class="flex-grow">
      <div class="container mx-auto px-4 py-6">
        <slot />
      </div>
    </main>

    <footer class="bg-accent">
      <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="footer-section">
            <h2 class="footer-heading">สำนักงานปลัดกระทรวงสาธารณสุข</h2>
            <div class="footer-contact">
              <div class="footer-contact-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="footer-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <p>กระทรวงสาธารณสุข ถนนติวานนท์ อำเภอเมือง จังหวัดนนทบุรี 11000</p>
              </div>
              <div class="footer-contact-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="footer-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                <p>โทรศัพท์: 0-2590-1000</p>
              </div>
              <div class="footer-contact-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="footer-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <p>อีเมล: contact@moph.go.th</p>
              </div>
            </div>
          </div>
          
          <div class="footer-section">
            <h3 class="footer-heading">ลิงก์ที่เกี่ยวข้อง</h3>
            <ul class="footer-links">
              <li>
                <a href="https://www.moph.go.th" target="_blank" rel="noopener" class="footer-link">
                  <svg xmlns="http://www.w3.org/2000/svg" class="footer-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                  </svg>
                  กระทรวงสาธารณสุข
                </a>
              </li>
              <li>
                <a href="https://ops.moph.go.th" target="_blank" rel="noopener" class="footer-link">
                  <svg xmlns="http://www.w3.org/2000/svg" class="footer-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                  </svg>
                  สำนักงานปลัดกระทรวงสาธารณสุข
                </a>
              </li>
              <li>
                <a href="https://www.moph.go.th/index.php/law" target="_blank" rel="noopener" class="footer-link">
                  <svg xmlns="http://www.w3.org/2000/svg" class="footer-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                  </svg>
                  กฎหมายที่เกี่ยวข้อง
                </a>
              </li>
            </ul>
          </div>
          
          <div class="footer-section">
            <h3 class="footer-heading">ติดตามเรา</h3>
            <div class="social-links">
              <a href="#" class="social-link" aria-label="Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" class="social-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                </svg>
              </a>
              <a href="#" class="social-link" aria-label="Twitter">
                <svg xmlns="http://www.w3.org/2000/svg" class="social-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                </svg>
              </a>
              <a href="#" class="social-link" aria-label="YouTube">
                <svg xmlns="http://www.w3.org/2000/svg" class="social-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                  <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                </svg>
              </a>
              <a href="#" class="social-link" aria-label="Line">
                <svg xmlns="http://www.w3.org/2000/svg" class="social-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                  <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
              </a>
            </div>
          </div>
        </div>
        
        <div class="footer-bottom">
          <p class="copyright">&copy; {{ new Date().getFullYear() }} สำนักงานปลัดกระทรวงสาธารณสุข. สงวนลิขสิทธิ์.</p>
          <div class="footer-policy-links">
            <router-link to="/privacy-policy" class="footer-policy-link">นโยบายความเป็นส่วนตัว</router-link>
            <span class="separator">|</span>
            <router-link to="/terms-of-service" class="footer-policy-link">ข้อกำหนดการใช้งาน</router-link>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>