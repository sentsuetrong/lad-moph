import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useAccessibilityStore = defineStore('accessibility', () => {
  // ตรวจสอบ preference ของระบบก่อน
  const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches

  // อ่านค่าจาก cookie ถ้ามี
  const getCookieValue = (name: string): string | null => {
    const value = `; ${document.cookie}`
    const parts = value.split(`; ${name}=`)
    if (parts.length === 2) return parts.pop()?.split(';').shift() || null
    return null
  }

  // สร้างหรืออัพเดท cookie
  const setCookie = (name: string, value: string, days: number = 365) => {
    const date = new Date()
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000))
    document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`
  }

  // ตั้งค่าเริ่มต้นโดยอ่านจาก cookie หรือใช้ค่าเริ่มต้นตาม system preference
  const theme = ref(getCookieValue('theme') || (systemPrefersDark ? 'dark' : 'light'))
  const fontSize = ref(parseInt(getCookieValue('fontSize') || '100'))
  const highContrast = ref(getCookieValue('highContrast') === 'true')
  const reducedMotion = ref(getCookieValue('reducedMotion') === 'true' || window.matchMedia('(prefers-reduced-motion: reduce)').matches)

  // บันทึกการเปลี่ยนแปลงลงใน cookie
  watch(theme, (newValue) => {
    setCookie('theme', newValue)
    applyTheme(newValue)
  })

  watch(fontSize, (newValue) => {
    setCookie('fontSize', newValue.toString())
    applyFontSize(newValue)
  })

  watch(highContrast, (newValue) => {
    setCookie('highContrast', newValue.toString())
    applyHighContrast(newValue)
  })

  watch(reducedMotion, (newValue) => {
    setCookie('reducedMotion', newValue.toString())
    applyReducedMotion(newValue)
  })

  // ฟังก์ชันสำหรับนำการตั้งค่าไปใช้
  const applyTheme = (value: string) => {
    if (value === 'dark') {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }

  const applyFontSize = (percentage: number) => {
    document.documentElement.style.fontSize = `${percentage}%`
  }

  const applyHighContrast = (enabled: boolean) => {
    if (enabled) {
      document.documentElement.classList.add('high-contrast')
    } else {
      document.documentElement.classList.remove('high-contrast')
    }
  }

  const applyReducedMotion = (enabled: boolean) => {
    if (enabled) {
      document.documentElement.classList.add('reduced-motion')
    } else {
      document.documentElement.classList.remove('reduced-motion')
    }
  }

  // ฟังก์ชันสำหรับเปลี่ยนการตั้งค่า
  const toggleTheme = () => {
    theme.value = theme.value === 'dark' ? 'light' : 'dark'
  }

  const increaseFontSize = () => {
    if (fontSize.value < 200) {
      fontSize.value += 10
    }
  }

  const decreaseFontSize = () => {
    if (fontSize.value > 80) {
      fontSize.value -= 10
    }
  }

  const resetFontSize = () => {
    fontSize.value = 100
  }

  const toggleHighContrast = () => {
    highContrast.value = !highContrast.value
  }

  const toggleReducedMotion = () => {
    reducedMotion.value = !reducedMotion.value
  }

  // นำการตั้งค่าไปใช้ตั้งแต่เริ่มต้น
  const initAccessibilitySettings = () => {
    applyTheme(theme.value)
    applyFontSize(fontSize.value)
    applyHighContrast(highContrast.value)
    applyReducedMotion(reducedMotion.value)
  }

  return {
    theme,
    fontSize,
    highContrast,
    reducedMotion,
    toggleTheme,
    increaseFontSize,
    decreaseFontSize,
    resetFontSize,
    toggleHighContrast,
    toggleReducedMotion,
    initAccessibilitySettings
  }
})