<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
      <!-- หัวข้อ Modal -->
      <div class="p-4 border-b">
        <h2 class="text-xl font-bold">{{ title }}</h2>
      </div>

      <!-- เนื้อหา Modal -->
      <div class="p-4">
        <!-- รายละเอียดการจอง -->
        <div v-if="reservation" class="mb-4 text-sm border-b pb-4">
          <p><span class="font-semibold">ผู้จอง:</span> {{ reservation.requesterName }}</p>
          <p><span class="font-semibold">สถานที่:</span> {{ reservation.destination }}</p>
          <p><span class="font-semibold">รายละเอียด:</span> {{ reservation.details }}</p>
          <p><span class="font-semibold">ประเภทรถ:</span> {{ getVehicleTypeThai(reservation.vehicleType) }}</p>
          <p><span class="font-semibold">จำนวนผู้โดยสาร:</span> {{ reservation.passengerCount }} คน</p>
          <p><span class="font-semibold">วันเวลา:</span> {{ formatThaiDateTime(reservation.arrivalDateTime) }}</p>
        </div>

        <!-- ชื่อผู้อนุมัติ -->
        <div class="mb-4">
          <label for="approverName" class="block mb-1 font-medium">ชื่อผู้{{ actionLabel }} <span
              class="text-red-500">*</span></label>
          <input id="approverName" v-model="approverName" type="text"
            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        </div>

        <!-- เหตุผล (เฉพาะกรณีไม่อนุมัติหรือยกเลิก) -->
        <div v-if="action !== 'approved'" class="mb-4">
          <label for="reason" class="block mb-1 font-medium">เหตุผล <span class="text-red-500">*</span></label>
          <textarea id="reason" v-model="reason"
            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"
            required></textarea>
        </div>
      </div>

      <!-- ปุ่มใน Modal -->
      <div class="p-4 border-t flex justify-end space-x-2">
        <button @click="closeModal"
          class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
          ยกเลิก
        </button>
        <button @click="confirm" :disabled="isProcessing || !isValid" :class="[
          'px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-offset-2',
          isProcessing ? 'bg-gray-400 cursor-not-allowed' : buttonClass
        ]">
          {{ isProcessing ? 'กำลังดำเนินการ...' : confirmButtonText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { Reservation } from '../types';
import { formatThaiDate, formatThaiTime } from '../utils/date';
import { updateReservationStatus } from '../services/fake-api';

const props = defineProps<{
  show: boolean;
  reservation: Reservation | null;
  action: 'approved' | 'rejected' | 'cancelled';
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'updated', reservation: Reservation): void;
}>();

// สถานะการประมวลผล
const isProcessing = ref(false);

// ข้อมูลสำหรับการอนุมัติ
const approverName = ref('');
const reason = ref('');

// เคลียร์ข้อมูลเมื่อปิด Modal
watch(() => props.show, (newVal) => {
  if (newVal) {
    approverName.value = '';
    reason.value = '';
  }
});

// ปิด Modal
const closeModal = () => {
  emit('close');
};

// ชื่อหัวข้อตามสถานะ
const title = computed(() => {
  switch (props.action) {
    case 'approved':
      return 'อนุมัติการจองรถยนต์';
    case 'rejected':
      return 'ไม่อนุมัติการจองรถยนต์';
    case 'cancelled':
      return 'ยกเลิกการจองรถยนต์';
    default:
      return 'จัดการการจองรถยนต์';
  }
});

// คำอธิบายการกระทำ
const actionLabel = computed(() => {
  switch (props.action) {
    case 'approved':
      return 'อนุมัติ';
    case 'rejected':
      return 'ไม่อนุมัติ';
    case 'cancelled':
      return 'ยกเลิก';
    default:
      return 'จัดการ';
  }
});

// ข้อความปุ่มยืนยัน
const confirmButtonText = computed(() => {
  switch (props.action) {
    case 'approved':
      return 'อนุมัติ';
    case 'rejected':
      return 'ไม่อนุมัติ';
    case 'cancelled':
      return 'ยกเลิก';
    default:
      return 'ยืนยัน';
  }
});

// สีปุ่มตามสถานะ
const buttonClass = computed(() => {
  switch (props.action) {
    case 'approved':
      return 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500';
    case 'rejected':
      return 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500';
    case 'cancelled':
      return 'bg-yellow-600 hover:bg-yellow-700 text-white focus:ring-yellow-500';
    default:
      return 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500';
  }
});

// ตรวจสอบข้อมูลที่จำเป็น
const isValid = computed(() => {
  if (!approverName.value.trim()) return false;
  if (props.action !== 'approved' && !reason.value.trim()) return false;
  return true;
});

// แปลงชื่อประเภทรถเป็นภาษาไทย
const getVehicleTypeThai = (type: string) => {
  switch (type) {
    case 'sedan':
      return 'รถเก๋ง';
    case 'pickup':
      return 'รถกระบะ';
    case 'van':
      return 'รถตู้';
    default:
      return type;
  }
};

// จัดรูปแบบวันเวลาเป็นภาษาไทย
const formatThaiDateTime = (date: Date) => {
  const dateObj = new Date(date);
  return `${formatThaiDate(dateObj)} เวลา ${formatThaiTime(dateObj)} น.`;
};

// ยืนยันการดำเนินการ
const confirm = async () => {
  if (!props.reservation || isProcessing.value || !isValid.value) return;

  try {
    isProcessing.value = true;

    const updatedReservation = await updateReservationStatus(
      props.reservation.id,
      props.action,
      approverName.value,
      props.action !== 'approved' ? reason.value : undefined
    );

    if (updatedReservation) {
      emit('updated', updatedReservation);
    }

    closeModal();
  } catch (error) {
    console.error('Error updating reservation status:', error);
    alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ กรุณาลองใหม่อีกครั้ง');
  } finally {
    isProcessing.value = false;
  }
};
</script>