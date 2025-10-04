<template>
  <aside class="sidebar">
    <div class="top">
      <img :src="computacaoLogo" alt="Logo Computação UNIFIL" class="logo" />
      <div class="title">Sistema de Horário</div>
    </div>

    <nav class="nav">
      <router-link to="/horarios" class="nav-link" active-class="active">
        <span class="material-icons">calendar_month</span><span>Horários</span>
      </router-link>
      <router-link to="/salas" class="nav-link" active-class="active">
        <span class="material-icons">meeting_room</span><span>Salas</span>
      </router-link>
      <router-link to="/professores" class="nav-link" active-class="active">
        <span class="material-icons">school</span><span>Professores</span>
      </router-link>
      <router-link to="/turmas" class="nav-link" active-class="active">
        <span class="material-icons">groups</span><span>Turmas</span>
      </router-link>
      <router-link to="/unidades-curriculares" class="nav-link" active-class="active">
        <span class="material-icons">book</span><span>UCs</span>
      </router-link>
    </nav>

    <div class="bottom">
      <button @click="logout" class="logout-btn">
        <span class="material-icons">exit_to_app</span><span>Sair</span>
      </button>
    </div>
  </aside>
</template>

<script>
import axios from 'axios'
import computacaoLogo from '../assets/computacao.jpg'

export default {
  name: 'Sidebar',
  data: () => ({ computacaoLogo }),
  methods: {
    async logout() {
      try { await axios.post('/api/logout') } catch (_) {}
      localStorage.removeItem('api_token')
      localStorage.removeItem('user')
      delete axios.defaults.headers.common.Authorization
      this.$router.push('/login')
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');
.sidebar{width:142px;height:100vh;position:fixed;top:0;left:0;background:#000;color:#fff;transition:width .5s}
.sidebar:hover{width:285px}
.top{padding:1rem 1rem .5rem;text-align:center}
.logo{width:6rem;margin:0 auto .5rem}
.title{font-size:1.85rem;font-weight:700;white-space:nowrap}
.sidebar:not(:hover) .title{opacity:0}
.nav{padding:0 .5rem;display:flex;flex-direction:column;gap:.5rem}
.nav-link{display:flex;align-items:center;gap:1.25rem;padding:.75rem 1rem;font-size:1.75rem;color:#fff;text-decoration:none;border-radius:.375rem}
.nav-link:hover{background:#1f1f1f}
.nav-link.active{background:#79736e}
.nav-link .material-icons{font-size:3rem}
.sidebar:not(:hover) .nav-link span:not(.material-icons){opacity:0;width:0;margin:0}
.bottom{padding:1rem}
.logout-btn{display:flex;align-items:center;gap:1.25rem;width:100%;padding:.75rem 1rem;font-size:1.75rem;background:none;border:none;color:#fff;border-radius:.375rem;cursor:pointer}
.logout-btn:hover{background:#1f1f1f}
</style>
