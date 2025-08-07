import { createRouter, createWebHistory } from 'vue-router'
import SalaPage from '../views/SalaPage.vue'
import ProfessorPage from '../views/ProfessorPage.vue'

const routes = [
  { path: '/salas', name: 'Salas', component: SalaPage },
  { path: '/professores', name: 'Professores', component: ProfessorPage },
  { path: '/', redirect: '/salas' },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})


