import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/components/LoginView.vue'
import SalaView from '@/components/SalaView.vue'
import ProfessorView from '@/components/ProfessorView.vue'

const routes = [
  { path: '/login', name: 'login', component: LoginView }, // pública
  { path: '/salas', name: 'salas', component: SalaView, meta: { requiresAuth: true } },
  { path: '/professores', name: 'professores', component: ProfessorView, meta: { requiresAuth: true } },
  { path: '/', redirect: '/salas' },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('api_token')

  if (to.meta.requiresAuth && !token) return next({ name: 'login' })
  if (to.name === 'login' && token)   return next({ name: 'salas' })

  next()
})

export default router
