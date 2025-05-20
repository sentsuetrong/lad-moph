<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
      <h3 class="text-lg font-medium text-gray-500">รอการอนุมัติ</h3>
      <p class="text-2xl font-bold">{{ stats.pendingCount }} รายการ</p>
    </div>

    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
      <h3 class="text-lg font-medium text-gray-500">จองวันนี้</h3>
      <p class="text-2xl font-bold">{{ stats.todayCount }} รายการ</p>
    </div>

    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
      <h3 class="text-lg font-medium text-gray-500">จองเดือนก่อน</h3>
      <p class="text-2xl font-bold">{{ stats.lastMonthCount }} รายการ</p>
    </div>

    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
      <h3 class="text-lg font-medium text-gray-500">จองเดือนนี้</h3>
      <p class="text-2xl font-bold">{{ stats.currentMonthCount }} รายการ</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { getReservationStats } from '../services/fake-api';
import type { ReservationStats } from '../types';

// ข้อมูลสถิติ
const stats = ref<ReservationStats>({
  pendingCount: 0,
  todayCount: 0,
  lastMonthCount: 0,
  currentMonthCount: 0
});

// โหลดข้อมูลสถิติ
const loadStats = async () => {
  try {
    stats.value = await getReservationStats();
  } catch (error) {
    console.error('Error loading stats:', error);
  }
};

// โหลดข้อมูลเมื่อคอมโพเนนต์ถูกโหลด
onMounted(() => {
  loadStats();
});
</script>