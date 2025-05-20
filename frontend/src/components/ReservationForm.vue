<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-6">แบบฟอร์มจองรถยนต์ไปติดต่อราชการ</h2>

    <form @submit.prevent="submitForm" class="space-y-4">
      <!-- ชื่อผู้จองรถยนต์ -->
      <div>
        <label for="requesterName" class="block mb-1 font-medium">ชื่อผู้จองรถยนต์ <span
            class="text-red-500">*</span></label>
        <input id="requesterName" v-model="form.requesterName" type="text"
          class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>

      <!-- สถานที่ปลายทาง -->
      <div>
        <label for="destination" class="block mb-1 font-medium">สถานที่ปลายทางที่จะไปติดต่อราชการ <span
            class="text-red-500">*</span></label>
        <input id="destination" v-model="form.destination" type="text"
          class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>

      <!-- รายละเอียดการติดต่อราชการ -->
      <div>
        <label for="details" class="block mb-1 font-medium">รายละเอียดการติดต่อราชการ <span
            class="text-red-500">*</span></label>
        <textarea id="details" v-model="form.details"
          class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"
          required></textarea>
      </div>

      <!-- ประเภทรถที่ต้องการ -->
      <div>
        <label class="block mb-1 font-medium">ประเภทรถที่ต้องการ <span class="text-red-500">*</span></label>
        <div class="flex space-x-4">
          <label class="flex items-center">
            <input type="radio" v-model="form.vehicleType" value="sedan" class="mr-2" required />
            รถเก๋ง
          </label>
          <label class="flex items-center">
            <input type="radio" v-model="form.vehicleType" value="pickup" class="mr-2" />
            รถยนต์กระบะ
          </label>
          <label class="flex items-center">
            <input type="radio" v-model="form.vehicleType" value="van" class="mr-2" />
            รถตู้
          </label>
        </div>
      </div>

      <!-- จำนวนผู้ไปติดต่อราชการ -->
      <div>
        <label for="passengerCount" class="block mb-1 font-medium">จำนวนผู้ไปติดต่อราชการ (ไม่รวมคนขับ) <span
            class="text-red-500">*</span></label>
        <input id="passengerCount" v-model.number="form.passengerCount" type="number" min="1" max="15"
          class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>

      <!-- วันเวลาที่ต้องไปถึงสถานที่ -->
      <div>
        <label for="arrivalDateTime" class="block mb-1 font-medium">วันเวลาที่ต้องไปถึงสถานที่ <span
            class="text-red-500">*</span></label>
        <input id="arrivalDateTime" v-model="form.arrivalDateTime" type="datetime-local"
          class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>

      <!-- ปุ่มส่งฟอร์ม -->
      <div class="pt-4">
        <button type="submit"
          class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          :disabled="isSubmitting">
          {{ isSubmitting ? 'กำลังบันทึก...' : 'บันทึกการจอง' }}
        </button>
        <button type="button" @click="$emit('cancel')"
          class="px-6 py-2 ml-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
          ยกเลิก
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { createReservation } from '../services/fake-api';

// กำหนดอีเวนต์
const emit = defineEmits<{
  (e: 'submitted'): void;
  (e: 'cancel'): void;
}>();

// ข้อมูลฟอร์ม
const form = reactive({
  requesterName: '',
  destination: '',
  details: '',
  vehicleType: 'sedan' as 'sedan' | 'pickup' | 'van',
  passengerCount: 1,
  arrivalDateTime: ''
});

// สถานะการส่งฟอร์ม
const isSubmitting = ref(false);

// บันทึกการจอง
const submitForm = async () => {
  if (isSubmitting.value) return;

  try {
    isSubmitting.value = true;

    // แปลงข้อมูลวันเวลา
    const reservation = {
      ...form,
      arrivalDateTime: new Date(form.arrivalDateTime)
    };

    // ส่งข้อมูลการจอง
    await createReservation(reservation);

    // แจ้งว่าส่งสำเร็จ
    emit('submitted');
  } catch (error) {
    console.error('Error submitting reservation:', error);
    alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง');
  } finally {
    isSubmitting.value = false;
  }
};
</script>