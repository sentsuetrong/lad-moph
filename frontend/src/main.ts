import { createApp } from 'vue'
import axios from 'axios'
import App from './App.vue'
import router from './router'
import './style.css'

// กำหนดค่าเริ่มต้น Axios สำหรับการเรียกใช้งาน API
axios.defaults.baseURL = import.meta.env.VITE_API_URL || '/api'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.withCredentials = true

// อินเตอร์เซปเตอร์สำหรับจัดการ JWT token
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// สร้างแอปพลิเคชัน Vue
const app = createApp(App);

app.use(router);

// สร้างตัวแปรสำหรับเรียกใช้ Axios ทั่วทั้งแอปพลิเคชัน
app.config.globalProperties.$axios = axios;

app.mount('#app');