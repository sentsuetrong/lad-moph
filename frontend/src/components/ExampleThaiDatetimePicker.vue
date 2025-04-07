// App.vue หรือ Component ที่ต้องการใช้งาน
<script setup lang="ts">
import { ref } from 'vue';
import ThaiDatetimePicker from './ThaiDatetimePicker.vue';
import CustomSelect from './CustomSelect.vue';

// สำหรับตัวอย่างที่ 1 (แบบพื้นฐาน)
const date1 = ref<Date | undefined>();

// สำหรับตัวอย่างที่ 2 (เลือกเฉพาะวันที่)
const date2 = ref<Date | [Date | null, Date | null] | undefined>();

// สำหรับตัวอย่างที่ 3 (เลือกเฉพาะเวลา)
const time = ref<Date | undefined>(new Date());

// สำหรับตัวอย่างที่ 4 (ปีคริสต์ศักราช)
const dateChristian = ref<Date | undefined>();

// สำหรับตัวอย่างที่ 5 (เลือกช่วงวันที่)
const dateRange = ref<[Date | null, Date | null]>([null, null]);

// สำหรับตัวอย่างที่ 6 (กำหนดช่วงวันที่ที่เลือกได้)
const dateWithLimits = ref<Date | undefined>();
const minDate = new Date();
const maxDate = new Date();
maxDate.setMonth(maxDate.getMonth() + 3); // สามารถเลือกได้สูงสุด 3 เดือนจากวันปัจจุบัน

const selectedMinutes = ref(0);
const minutesOptions = Array.from({ length: 60 }, (_, i) => ({ value: i, label: String(i).padStart(2, '0') }));
</script>

<template>
  <div class="card">
    <h1 class="text-xl font-bold mb-4">ตัวอย่างการใช้งาน ThaiDatetimePicker</h1>

    <div class="p-4 border rounded-lg mb-4">
      <h2 class="text-lg font-semibold mb-2">ตัวอย่าง Custom Select</h2>
      <CustomSelect id="select-hours" v-model.number="selectedMinutes" :options="minutesOptions" placeholder="นาที" />
      <div class="mt-2 text-sm text-gray-600">
        ค่าที่เลือก: {{ selectedMinutes }}
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- ตัวอย่างที่ 1: การใช้งานพื้นฐาน (ค่าเริ่มต้น) -->
      <div class="p-4 border rounded-lg">
        <h2 class="text-lg font-semibold mb-2">แบบพื้นฐาน (ปีพุทธศักราช + วันที่และเวลา)</h2>
        <ThaiDatetimePicker v-model="date1" placeholder="เลือกวันที่และเวลา" />
        <div class="mt-2 text-sm text-gray-600">
          ค่าที่เลือก: {{ date1 ? date1.toLocaleString('th-TH') : 'ยังไม่ได้เลือก' }}
        </div>
      </div>

      <!-- ตัวอย่างที่ 2: เลือกเฉพาะวันที่ -->
      <div class="p-4 border rounded-lg">
        <h2 class="text-lg font-semibold mb-2">เลือกเฉพาะวันที่</h2>
        <ThaiDatetimePicker v-model="date2" mode="date" placeholder="เลือกวันที่" />
        <div class="mt-2 text-sm text-gray-600">
          ค่าที่เลือก: {{ date2 ? date2.toLocaleString('th-TH') : 'ยังไม่ได้เลือก' }}
        </div>
      </div>

      <!-- ตัวอย่างที่ 3: เลือกเฉพาะเวลา -->
      <div class="p-4 border rounded-lg">
        <h2 class="text-lg font-semibold mb-2">เลือกเฉพาะเวลา</h2>
        <ThaiDatetimePicker v-model="time" mode="time" placeholder="เลือกเวลา" />
        <div class="mt-2 text-sm text-gray-600">
          ค่าที่เลือก: {{ time ? time.toLocaleTimeString('th-TH') : 'ยังไม่ได้เลือก' }}
        </div>
      </div>

      <!-- ตัวอย่างที่ 4: ปีคริสต์ศักราช -->
      <div class="p-4 border rounded-lg">
        <h2 class="text-lg font-semibold mb-2">แสดงปีคริสต์ศักราช</h2>
        <ThaiDatetimePicker v-model="dateChristian" yearType="christian" placeholder="choose date and time" />
        <div class="mt-2 text-sm text-gray-600">
          ค่าที่เลือก: {{ dateChristian ? dateChristian.toLocaleString('en-US') : 'ยังไม่ได้เลือก' }}
        </div>
      </div>

      <!-- ตัวอย่างที่ 5: เลือกช่วงวันที่ -->
      <div class="p-4 border rounded-lg">
        <h2 class="text-lg font-semibold mb-2">เลือกช่วงวันที่</h2>
        <ThaiDatetimePicker v-model="dateRange" rangeMode="range" mode="date" placeholder="เลือกช่วงวันที่" />
        <div class="mt-2 text-sm text-gray-600">
          ค่าเริ่มต้น: {{ dateRange[0] ? dateRange[0].toLocaleDateString('th-TH') : 'ยังไม่ได้เลือก' }}<br>
          ค่าสิ้นสุด: {{ dateRange[1] ? dateRange[1].toLocaleDateString('th-TH') : 'ยังไม่ได้เลือก' }}
        </div>
      </div>

      <!-- ตัวอย่างที่ 6: กำหนดช่วงวันที่ที่เลือกได้ -->
      <div class="p-4 border rounded-lg">
        <h2 class="text-lg font-semibold mb-2">กำหนดช่วงวันที่ที่เลือกได้</h2>
        <ThaiDatetimePicker v-model="dateWithLimits" :minDate="minDate" :maxDate="maxDate"
          placeholder="เลือกวันที่ (ภายใน 3 เดือน)" />
        <div class="mt-2 text-sm text-gray-600">
          ค่าที่เลือก: {{ dateWithLimits ? dateWithLimits.toLocaleString('th-TH') : 'ยังไม่ได้เลือก' }}
        </div>
      </div>
    </div>
  </div>
</template>