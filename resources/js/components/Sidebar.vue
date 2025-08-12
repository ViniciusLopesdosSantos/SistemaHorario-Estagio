<template>
  <aside class="sidebar">
    <div class="top">
      <img :src="computacaoLogo" alt="Logo Computação UNIFIL" class="logo" />
      <div class="title">Sistema de Horário</div>
    </div>

    <nav class="nav">
      <router-link to="/salas" class="nav-link" active-class="active">
        <span class="material-icons">meeting_room</span>
        <span>Salas</span>
      </router-link>

      <router-link to="/professores" class="nav-link" active-class="active">
        <span class="material-icons">school</span>
        <span>Professores</span>
      </router-link>
    </nav>

    <div class="bottom">
      <button @click="logout" class="logout-btn">
        <span class="material-icons">exit_to_app</span>
        <span>Sair</span>
      </button>
    </div>
  </aside>
</template>

<script>
import axios from 'axios'
import computacaoLogo from '../assets/computacao.jpg'

export default {
  name: 'Sidebar',
  data() { return { computacaoLogo } },
  methods: {
    async logout() {
      try { await axios.post('/api/logout') } catch (e) {}
      localStorage.removeItem('api_token')
      localStorage.removeItem('user')
      delete axios.defaults.headers.common['Authorization']
      this.$router.push('/login')
    }
  }
}
</script>

<style scoped>
.sidebar {
  width: 142px;
  height: 100vh;
  position: fixed;
  top: 0; left: 0;
  background-color: #000;
  color: #fff;
  transition: width 0.5s ease;
}
.sidebar:hover { width: 285px; }

.top { padding: 1rem 1rem 0.5rem; text-align: center; }
.logo { width: 6rem; height: auto; margin: 0 auto 0.5rem; transition: width 0.5s ease; }
.title { font-size: 1.85rem; font-weight: bold; white-space: nowrap; transition: opacity 0.3s ease; }
.sidebar:not(:hover) .title { opacity: 0; }

.nav { padding: 0 0.5rem; display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0; }
.nav-link { display: flex; align-items: center; gap: 1.25rem; padding: 0.75rem 1rem; font-size: 1.75rem; color: #fff; text-decoration: none; border-radius: 0.375rem; transition: background-color 0.2s ease; }
.nav-link:hover { background-color: #1f1f1f; }
.nav-link.active { background-color: #79736e; }
.nav-link .material-icons { font-size: 3rem; flex-shrink: 0; transition: font-size 0.5s ease; }
.nav-link span:not(.material-icons) { transition: opacity 0.3s ease, width 0.3s ease, margin 0.3s ease; }
.sidebar:not(:hover) .nav-link span:not(.material-icons) { opacity: 0; width: 0; margin: 0; }

.bottom { padding: 1rem; }
.logout-btn { display: flex; align-items: center; gap: 1.25rem; width: 100%; padding: 0.75rem 1rem; font-size: 1.75rem; background: none; border: none; color: #fff; text-align: left; border-radius: 0.375rem; cursor: pointer; transition: background-color 0.2s ease; }
.logout-btn:hover { background-color: #1f1f1f; }
.logout-btn .material-icons { font-size: 3rem; flex-shrink: 0; }
.logout-btn span:not(.material-icons) { transition: opacity 0.3s ease, width 0.3s ease, margin 0.3s ease; }
.sidebar:not(:hover) .logout-btn span:not(.material-icons) { opacity: 0; width: 0; margin: 0; }
</style>
