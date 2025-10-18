import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'

import LandingView from '@/components/LandingView.vue'
import AlunoHorariosView from '@/components/AlunoHorariosView.vue'
import LoginView from '@/components/LoginView.vue'
import SalaView from '@/components/SalaView.vue'
import ProfessorView from '@/components/ProfessorView.vue'
import TurmaView from '@/components/TurmaView.vue'
import UnidadesCurriculares from '@/components/UnidadesCurriculares.vue'
import HorarioView from '@/components/HorarioView.vue'
import HorariosFeitosView from '@/components/HorariosFeitosView.vue'

const routes = [
  { path: '/', name: 'home', component: LandingView },              // Página inicial
  { path: '/aluno', name: 'aluno', component: AlunoHorariosView },  // Página do aluno

  { path: '/login', name: 'login', component: LoginView },          // Página de login
  { path: '/horarios', name: 'horarios', component: HorarioView, meta: { requiresAuth: true } },
  { path: '/horarios-feitos', name: 'horarios-feitos', component: HorariosFeitosView, meta: { requiresAuth: true } },
  { path: '/salas', name: 'salas', component: SalaView, meta: { requiresAuth: true } },
  { path: '/professores', name: 'professores', component: ProfessorView, meta: { requiresAuth: true } },
  { path: '/turmas', name: 'turmas', component: TurmaView, meta: { requiresAuth: true } },
  { path: '/unidades-curriculares', name: 'UnidadesCurriculares', component: UnidadesCurriculares, meta: { requiresAuth: true } },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('api_token')
  
  // Define o cabeçalho para todas as requisições com o token de autorização
  if (token) axios.defaults.headers.common.Authorization = `Bearer ${token}`
  
  // Garante que todas as requisições aceitam JSON
  axios.defaults.headers.common.Accept = 'application/json'

  // Se a rota exige autenticação e o token não existe, redireciona para o login
  if (to.meta.requiresAuth && !token) return next({ name: 'login' })
  
  // Se o usuário já estiver logado e tentar acessar o login ou a home, redireciona para a página de horários
  if ((to.name === 'login' || to.name === 'home') && token) return next({ name: 'horarios' })

  next()
})

export default router
