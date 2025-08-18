import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/components/LoginView.vue'
import SalaView from '@/components/SalaView.vue'
import ProfessorView from '@/components/ProfessorView.vue'
import TurmaView from '@/components/TurmaView.vue' 

const routes = [
  { path: '/login', name: 'login', component: LoginView },

  { path: '/salas', name: 'salas', component: SalaView, meta: { requiresAuth: true } },
  { path: '/professores', name: 'professores', component: ProfessorView, meta: { requiresAuth: true } },
  { path: '/turmas', name: 'turmas', component: TurmaView, meta: { requiresAuth: true } }, 

  { path: '/', redirect: '/salas' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('api_token')

  // bloqueia rotas privadas sem token
  if (to.meta.requiresAuth && !token) return next({ name: 'login' })
  // evita ir ao login se já autenticado
  if (to.name === 'login' && token)   return next({ name: 'salas' })

  next()
})

export default router
