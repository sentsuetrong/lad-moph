<template>
  <div class="container mx-auto py-8 px-4">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">จองรถยนต์ไปติดต่อราชการ</h1>
        <button @click="goBack"
          class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
          &larr; กลับ
        </button>
      </div>

      <ReservationForm @submitted="handleSubmitted" @cancel="goBack" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import ReservationForm from '../components/ReservationForm.vue';

const router = useRouter();

const goBack = () => {
  router.push('/admin');
};

const handleSubmitted = () => {
  alert('บันทึกการจองเรียบร้อยแล้ว');
  router.push('/admin');
};
</script>