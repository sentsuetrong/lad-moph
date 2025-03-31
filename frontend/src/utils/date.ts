import { format } from 'date-fns';
import { th } from 'date-fns/locale';

// แปลงวันที่ให้เป็นรูปแบบไทย (พ.ศ.)
export const formatThaiDate = (date: Date): string => {
  // เพิ่ม 543 ปีเพื่อแปลงเป็น พ.ศ.
  const buddhistYear = date.getFullYear() + 543;
  const thaiMonth = format(date, 'MMMM', { locale: th });
  const day = format(date, 'd');

  return `${day} ${thaiMonth} ${buddhistYear}`;
};

// แปลงเดือนปีเป็นรูปแบบไทย (พ.ศ.)
export const formatThaiMonthYear = (date: Date): string => {
  const buddhistYear = date.getFullYear() + 543;
  const thaiMonth = format(date, 'MMMM', { locale: th });

  return `${thaiMonth} ${buddhistYear}`;
};

// แปลงเวลาให้เป็นรูปแบบไทย
export const formatThaiTime = (date: Date): string => {
  return format(date, 'HH:mm');
};

// สร้างข้อมูลปฏิทินของเดือน
export const getCalendarDays = (year: number, month: number): Date[] => {
  const result: Date[] = [];

  // วันแรกของเดือน
  const firstDay = new Date(year, month - 1, 1);
  // วันสุดท้ายของเดือน
  const lastDay = new Date(year, month, 0);

  // จำนวนวันในเดือน
  const daysInMonth = lastDay.getDate();

  // วันที่เริ่มต้นของสัปดาห์ (อาทิตย์ = 0)
  const startingDayOfWeek = firstDay.getDay();

  // เพิ่มวันว่างของเดือนก่อนหน้า
  for (let i = 0; i < startingDayOfWeek; i++) {
    const prevMonthDay = new Date(year, month - 1, i - startingDayOfWeek + 1);
    result.push(prevMonthDay);
  }

  // เพิ่มวันในเดือนปัจจุบัน
  for (let i = 1; i <= daysInMonth; i++) {
    const currentMonthDay = new Date(year, month - 1, i);
    result.push(currentMonthDay);
  }

  // เพิ่มวันของเดือนถัดไป
  const remainingDays = 7 - (result.length % 7);
  if (remainingDays < 7) {
    for (let i = 1; i <= remainingDays; i++) {
      const nextMonthDay = new Date(year, month, i);
      result.push(nextMonthDay);
    }
  }

  return result;
};

// ตรวจสอบว่าเป็นวันในเดือนปัจจุบันหรือไม่
export const isSameYearMonth = (date: Date, year: number, month: number): boolean => {
  return date.getFullYear() === year && date.getMonth() === month - 1;
};

// แปลงจากชื่อวันเป็นภาษาไทย
export const getThaiDayName = (day: number): string => {
  const thaiDays = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
  return thaiDays[day];
};