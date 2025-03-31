<template>
  <div class="container mx-auto py-8 px-4">
    <div class="bg-white rounded-lg shadow p-6">
      <h1 class="text-2xl font-bold mb-4">จัดการการจองรถยนต์</h1>

      <!-- สถิติการจอง -->
      <DashboardStats />

      <!-- ปุ่มจองรถใหม่ -->
      <div class="mb-6">
        <button @click="goToReservation"
          class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          จองรถยนต์เพิ่มเติม
        </button>
      </div>

      <!-- ปฏิทินการจอง -->
      <Calendar />
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import Calendar from '../components/Calendar.vue';
import DashboardStats from '../components/DashboardStats.vue';

const router = useRouter();

const goToReservation = () => {
  router.push('/reservation');
};
</script>