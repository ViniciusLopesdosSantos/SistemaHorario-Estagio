import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/components/LoginView.vue'
import SalaView from '@/components/SalaView.vue'
import ProfessorView from '@/components/ProfessorView.vue'
import TurmaView from '@/components/TurmaView.vue'
import UnidadesCurriculares from '@/components/UnidadesCurriculares.vue'
import HorarioView from '@/components/HorarioView.vue'
import axios from 'axios'

const routes = [
  { path: '/login', name: 'login', component: LoginView },
  { path: '/horarios', name: 'horarios', component: HorarioView, meta: { requiresAuth: true } },
  { path: '/salas', name: 'salas', component: SalaView, meta: { requiresAuth: true } },
  { path: '/professores', name: 'professores', component: ProfessorView, meta: { requiresAuth: true } },
  { path: '/turmas', name: 'turmas', component: TurmaView, meta: { requiresAuth: true } },
  { path: '/unidades-curriculares', name: 'UnidadesCurriculares', component: UnidadesCurriculares, meta: { requiresAuth: true } },
  { path: '/', redirect: '/horarios' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// guarda simples por token no localStorage
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('api_token')
  if (token) axios.defaults.headers.common.Authorization = `Bearer ${token}`
  axios.defaults.headers.common.Accept = 'application/json'

  if (to.meta.requiresAuth && !token) return next({ name: 'login' })
  if (to.name === 'login' && token)   return next({ name: 'horarios' })
  next()
})

export default router
