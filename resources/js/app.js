import axios from 'axios'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

const token = localStorage.getItem('api_token')
if (token) axios.defaults.headers.common['Authorization'] = 'Bearer ' + token

createApp(App).use(router).mount('#app')
