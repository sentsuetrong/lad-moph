<script setup lang="ts">
import { ref } from 'vue';
import type { NavigationItemType } from '../../types';

// ข้อมูลโครงสร้างเมนู
const navigationItems = ref<NavigationItemType[]>([
  { name: 'หน้าหลัก', to: '/', isOpen: false },
  {
    name: 'บริการของเรา',
    to: '/services',
    isOpen: false,
    children: [
      {
        name: 'ออกแบบเว็บไซต์',
        to: '/services/sub1',
        isOpen: false,
        children: [
          { name: 'แพ็กเกจเริ่มต้น', to: '/services/sub1/detailA', isOpen: false },
          { name: 'แพ็กเกจธุรกิจ', to: '/services/sub1/detailB', isOpen: false },
        ],
      },
      { name: 'พัฒนาโมบายแอป', to: '/services/sub2', isOpen: false },
      {
        name: 'การตลาดออนไลน์',
        to: '/services/sub3',
        isOpen: false,
        children: [
          { name: 'SEO', to: '/services/sub3/detailX', isOpen: false },
        ],
      },
    ],
  },
  { name: 'เกี่ยวกับเรา', to: '/about', isOpen: false },
  { name: 'ติดต่อเรา', to: '/contact', isOpen: false },
]);

// สถานะการเปิด/ปิดเมนูสำหรับหน้าจอมือถือ
const isMobileMenuOpen = ref(false);

// --- Helper Function สำหรับปิดเมนูย่อยทั้งหมดแบบ Recursive ---
function closeChildrenRecursively(items: NavigationItemType[] | undefined): void {
  if (!items) return;
  items.forEach(item => {
    item.isOpen = false;
    if (item.children) {
      closeChildrenRecursively(item.children);
    }
  });
}

// --- Logic สำหรับ Desktop Menu ---

// ฟังก์ชันสำหรับสลับการแสดงผลของเมนูหลัก (Desktop)
function toggleMainItem(clickedItem: NavigationItemType): void {
  const currentlyOpen = clickedItem.isOpen;

  // ปิดเมนูอื่นๆ ทั้งหมดในระดับบนสุด และเมนูย่อยของมันทั้งหมด
  navigationItems.value.forEach(item => {
    if (item !== clickedItem) {
      item.isOpen = false;
      closeChildrenRecursively(item.children);
    }
  });

  // สลับสถานะของเมนูที่คลิก (ถ้ามี children)
  if (clickedItem.children && clickedItem.children.length > 0) {
    clickedItem.isOpen = !currentlyOpen;
    // ถ้ากำลังปิดเมนูที่คลิก และมี children ให้ปิด children ของมันด้วย
    if (!clickedItem.isOpen) {
      closeChildrenRecursively(clickedItem.children);
    }
  } else {
    // ถ้าไม่มี children, การคลิกควรจะปิดเมนูนี้ (ถ้ามันเคยเปิดจากสถานะอื่น)
    // และถ้าเป็น link, router จะจัดการ navigation
    clickedItem.isOpen = false;
    closeAllDesktopDropdowns(); // ปิด dropdown อื่นๆ ทั้งหมดเมื่อคลิก link ตรง
  }
}

// ฟังก์ชันสำหรับสลับการแสดงผลของเมนูย่อย (Desktop)
function toggleSubItem(clickedSubItem: NavigationItemType, parentItem: NavigationItemType): void {
  const currentlyOpen = clickedSubItem.isOpen;

  // ปิดเมนูย่อยอื่นๆ ใน parent เดียวกัน และเมนูย่อยของ sibling เหล่านั้น
  if (parentItem.children) {
    parentItem.children.forEach(sibling => {
      if (sibling !== clickedSubItem) {
        sibling.isOpen = false;
        closeChildrenRecursively(sibling.children);
      }
    });
  }

  // สลับสถานะของเมนูย่อยที่คลิก (ถ้ามี children)
  if (clickedSubItem.children && clickedSubItem.children.length > 0) {
    clickedSubItem.isOpen = !currentlyOpen;
    // ถ้ากำลังปิดเมนูย่อยที่คลิก และมี children ให้ปิด children ของมันด้วย
    if (!clickedSubItem.isOpen) {
      closeChildrenRecursively(clickedSubItem.children);
    }
  } else {
    clickedSubItem.isOpen = false; // ปิดถ้าไม่มี children (เป็น link ปลายทาง)
    closeAllDesktopDropdowns(); // ปิด dropdown ทั้งหมดเมื่อคลิก link ย่อย
  }

  // ตรวจสอบให้แน่ใจว่า parent menu ยังคงเปิดอยู่ (สำคัญสำหรับ UX)
  if (!parentItem.isOpen) {
    parentItem.isOpen = true;
  }
}

// ฟังก์ชันสำหรับปิด dropdown ทั้งหมดบน Desktop
function closeAllDesktopDropdowns(): void {
  navigationItems.value.forEach(item => {
    item.isOpen = false;
    closeChildrenRecursively(item.children);
  });
}

// ฟังก์ชันจัดการการคลิก link ระดับบนสุดของ Desktop (ที่เป็น link โดยตรง ไม่มี children)
function handleDesktopLinkClick(item: NavigationItemType): void {
  closeAllDesktopDropdowns();
  // Vue Router จะจัดการ navigation
  if (item) void 0;
}

// ฟังก์ชันจัดการการคลิก link ระดับย่อยแรกของ Desktop (ที่เป็น link โดยตรง ไม่มี children)
function handleDesktopSubLinkClick(parentItem: NavigationItemType): void {
  closeAllDesktopDropdowns();
  // Vue Router จะจัดการ navigation
  if (parentItem) void 0;
}

// ฟังก์ชันจัดการการคลิก link ระดับย่อยที่สองของ Desktop
function handleDesktopGrandSubLinkClick(grandParentItem: NavigationItemType, parentItem: NavigationItemType): void {
  closeAllDesktopDropdowns();
  // Vue Router จะจัดการ navigation
  if (grandParentItem || parentItem) void 0;
}

// --- Event Handlers for events from HeaderNavigationItem ---
function handleItemToggle(itemToToggle: NavigationItemType, parentOfItemToToggle: NavigationItemType | undefined): void {
  if (!parentOfItemToToggle) { // This is a top-level item
    toggleMainItem(itemToToggle);
  } else { // This is a sub-item
    toggleSubItem(itemToToggle, parentOfItemToToggle);
  }
}

function handleItemLinkClick(
  itemClicked: NavigationItemType,
  parentOfItemClicked: NavigationItemType | undefined,
  grandParentOfItemClicked: NavigationItemType | undefined
): void {
  // The original handler functions already call closeAllDesktopDropdowns()
  if (!parentOfItemClicked && !grandParentOfItemClicked) {
    // Top-level link
    handleDesktopLinkClick(itemClicked);
  } else if (parentOfItemClicked && !grandParentOfItemClicked) {
    // Sub-level link (itemClicked is the sub-link, parentOfItemClicked is its direct parent)
    handleDesktopSubLinkClick(parentOfItemClicked);
  } else if (parentOfItemClicked && grandParentOfItemClicked) {
    // Grand-sub-level link (itemClicked is the grandchild, parentOfItemClicked is its parent, grandParentOfItemClicked is its grandparent)
    handleDesktopGrandSubLinkClick(grandParentOfItemClicked, parentOfItemClicked);
  }
}


// --- Logic สำหรับ Mobile Menu ---

// ฟังก์ชันสำหรับสลับการแสดงผลของเมนูทั้งหมดในมุมมองมือถือ
function toggleMobileMenu(): void {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
  // ถ้าปิด mobile menu, ให้ปิด dropdown ทั้งหมดภายใน mobile menu ด้วย
  if (!isMobileMenuOpen.value) {
    closeAllMenus();
  }
}

// ฟังก์ชันสำหรับปิดเมนูมือถือและ dropdown ทั้งหมด (ใช้เมื่อคลิก link ใน mobile menu)
function closeAllMenus(): void {
  isMobileMenuOpen.value = false;
  navigationItems.value.forEach(item => {
    item.isOpen = false;
    closeChildrenRecursively(item.children);
  });
}

// ฟังก์ชันสำหรับสลับเมนูหลักในมุมมองมือถือ (Accordion style)
function toggleMainItemMobile(clickedItem: NavigationItemType): void {
  const wasOpen = clickedItem.isOpen;

  // ปิดรายการหลักอื่นๆ ทั้งหมด (ยกเว้นรายการที่คลิก) และ children ของรายการเหล่านั้น
  navigationItems.value.forEach(item => {
    if (item !== clickedItem) {
      item.isOpen = false;
      closeChildrenRecursively(item.children);
    }
  });

  // สลับสถานะของรายการที่คลิก
  clickedItem.isOpen = !wasOpen;

  // ถ้ากำลังปิดรายการที่คลิก และมีเมนูย่อย ให้ปิดเมนูย่อยด้วย
  if (!clickedItem.isOpen) {
    closeChildrenRecursively(clickedItem.children);
  }
}

// ฟังก์ชันสำหรับสลับเมนูย่อยในมุมมองมือถือ (Accordion style)
function toggleSubItemMobile(clickedSubItem: NavigationItemType, parentItem: NavigationItemType): void {
  const wasOpen = clickedSubItem.isOpen;

  // ปิดรายการย่อยอื่นๆ ใน parent เดียวกัน และ children ของ sibling เหล่านั้น
  if (parentItem.children) {
    parentItem.children.forEach(sibling => {
      if (sibling !== clickedSubItem) {
        sibling.isOpen = false;
        closeChildrenRecursively(sibling.children);
      }
    });
  }
  // สลับสถานะของรายการย่อยที่คลิก
  clickedSubItem.isOpen = !wasOpen;

  // ถ้ากำลังปิดรายการย่อยที่คลิก และมี children ให้ปิด children ของมันด้วย
  if (!clickedSubItem.isOpen) {
    closeChildrenRecursively(clickedSubItem.children);
  }
}
</script>

<template>
  <nav class="bg-emerald-500 text-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex-shrink-0">
          <router-link to="/" class="text-2xl font-bold hover:text-emerald-100 transition-colors duration-150">
            เว็บของฉัน
          </router-link>
        </div>

        <div class="hidden md:block">
          <ul class="ml-10 flex items-baseline space-x-1">
            <HeaderNavigationItem v-for="item in navigationItems" :key="item.name" :item="item" :level="0"
              @toggle-item="handleItemToggle" @link-clicked="handleItemLinkClick" />
          </ul>
        </div>

        <div class="-mr-2 flex md:hidden">
          <button @click="toggleMobileMenu" type="button"
            class="bg-emerald-600 inline-flex items-center justify-center p-2 rounded-md text-emerald-100 hover:text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-emerald-500 focus:ring-white"
            aria-controls="mobile-menu" :aria-expanded="isMobileMenuOpen">
            <span class="sr-only">เปิดเมนูหลัก</span>
            <svg v-if="!isMobileMenuOpen" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div v-show="isMobileMenuOpen" class="md:hidden" id="mobile-menu">
      <ul class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        <li v-for="item in navigationItems" :key="item.name + '-mobile'" class="block">
          <router-link v-if="item.to && (!item.children || item.children.length === 0)" :to="item.to"
            @click="closeAllMenus"
            class="block px-3 py-2 rounded-md text-base font-medium hover:bg-emerald-600 transition-colors duration-150">
            {{ item.name }}
          </router-link>
          <button v-else @click="toggleMainItemMobile(item)" type="button"
            :class="['w-full text-left px-3 py-2 rounded-md text-base font-medium hover:bg-emerald-600 focus:outline-none focus:bg-emerald-600 transition-colors duration-150 flex items-center justify-between', { 'bg-emerald-600 text-white': item.isOpen }]">
            <span>{{ item.name }}</span>
            <svg v-if="item.children && item.children.length > 0" xmlns="http://www.w3.org/2000/svg"
              class="ml-1 h-5 w-5 transition-transform duration-150" :class="{ 'rotate-180': item.isOpen }"
              viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
            </svg>
          </button>

          <div v-if="item.children && item.children.length > 0 && item.isOpen" class="mt-1 pl-3">
            <ul class="space-y-1">
              <li v-for="child in item.children" :key="child.name + '-mobile-sub'" class="block">
                <router-link v-if="child.to && (!child.children || child.children.length === 0)" :to="child.to"
                  @click="closeAllMenus"
                  class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-emerald-700 transition-colors duration-150 w-full text-left">
                  {{ child.name }}
                </router-link>
                <button v-else @click.stop="toggleSubItemMobile(child, item)" type="button"
                  :class="['w-full text-left px-3 py-2 rounded-md text-sm font-medium hover:bg-emerald-700 focus:outline-none focus:bg-emerald-700 transition-colors duration-150 flex justify-between items-center', { 'bg-emerald-700 text-white': child.isOpen }]">
                  <span>{{ child.name }}</span>
                  <svg v-if="child.children && child.children.length > 0" xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 transition-transform duration-150" :class="{ 'rotate-180': child.isOpen }"
                    viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                      clip-rule="evenodd" />
                  </svg>
                </button>

                <div v-if="child.children && child.children.length > 0 && child.isOpen" class="mt-1 pl-3">
                  <ul class="space-y-1">
                    <li v-for="grandchild in child.children" :key="grandchild.name + '-mobile-grand'" class="block">
                      <router-link :to="grandchild.to!" @click="closeAllMenus"
                        class="block px-3 py-2 rounded-md text-xs font-medium hover:bg-emerald-800 transition-colors duration-150 w-full text-left">
                        {{ grandchild.name }}
                      </router-link>
                    </li>
                  </ul>
                </div>
              </li>
            </ul>
          </div>
        </li>
      </ul>
    </div>
  </nav>
</template>

<style scoped>
/* สามารถเพิ่ม CSS เฉพาะสำหรับคอมโพเนนต์นี้ได้ที่นี่ */
/* ตัวอย่างนี้ใช้ Tailwind CSS เป็นหลักในการจัดสไตล์ผ่าน class ใน template */
/* หากต้องการ override หรือเพิ่มสไตล์ที่ซับซ้อน สามารถเขียน CSS ที่นี่ได้เลย */

.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Optional: Ensure flyout menus have high enough z-index if overlap issues occur */
/* .group\/child .absolute {
  z-index: 50;
} */
</style>
