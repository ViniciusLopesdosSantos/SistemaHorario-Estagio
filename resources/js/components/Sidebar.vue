<template>
  <aside class="sidebar">
    <!-- Topo: logo + título -->
    <div class="top">
      <img :src="computacaoLogo" alt="Logo Computação UNIFIL" class="logo" />
      <div class="title">Sistema de Horário</div>
    </div>

    <!-- Navegação -->
    <nav class="nav">
      <router-link to="/salas" class="nav-link" active-class="active">
        <span class="material-icons">meeting_room</span>
        <span>Salas</span>
      </router-link>

      <router-link to="/professores" class="nav-link" active-class="active">
        <span class="material-icons">school</span>
        <span>Professores</span>
      </router-link>

      <!-- futuramente:
      <router-link to="/turmas" class="nav-link" active-class="active">…</router-link>
      <router-link to="/horarios" class="nav-link" active-class="active">…</router-link>
      -->
    </nav>

    <!-- Rodapé: logout -->
    <div class="bottom">
      <button @click="logout" class="logout-btn">
        <span class="material-icons">exit_to_app</span>
        <span>Sair</span>
      </button>
    </div>
  </aside>
</template>

<script>
import computacaoLogo from '../assets/computacao.jpg'

export default {
  name: 'Sidebar',
  data() {
    return { computacaoLogo }
  },
  methods: {
    logout() {
      this.$router.push('/login')
    }
  }
}
</script>

<style scoped>
/* ========== CONTAINER PRINCIPAL ========== */
.sidebar {
  width: 142px;                     /* largura quando fechada */
  height: 100vh;
  position: fixed;
  top: 0; left: 0;
  background-color: #000;
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
  transition: width 0.5s ease;     /* animação suave */
}
.sidebar:hover {
  width: 285px;                     /* largura completa ao passar o mouse */
}

/* ========== TOPO (LOGO + TÍTULO) ========== */
.top {
  padding: 1rem 1rem 0.5rem;
  text-align: center;
}
.logo {
  width: 6rem;                      /* aumenta um pouco o logo */
  height: auto;
  margin: 0 auto 0.5rem;
  transition: width 0.5s ease;
}
.title {
  font-size: 1.85rem;
  font-weight: bold;
  white-space: nowrap;
  transition: opacity 0.3s ease;
}
/* esconde o texto do título quando fechada */
.sidebar:not(:hover) .title {
  opacity: 0;
}

/* ========== NAVEGAÇÃO ========== */
.nav {
  padding: 0 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0;                    /* garante que fique logo abaixo do topo */
}

/* estilização dos links */
.nav-link {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 0.75rem 1rem;
  font-size: 1.75rem;
  color: #fff;
  text-decoration: none;
  border-radius: 0.375rem;
  transition: background-color 0.2s ease;
}
.nav-link:hover {
  background-color: #1f1f1f;
}
.nav-link.active {
  background-color: #79736e;
}

/* ícones dentro dos links */
.nav-link .material-icons {
  font-size: 3rem;                  /* ícones maiores */
  flex-shrink: 0;
  transition: font-size 0.5s ease;
}

/* esconde o texto dos links quando fechada */
.nav-link span:not(.material-icons) {
  transition: opacity 0.3s ease, width 0.3s ease, margin 0.3s ease;
}
.sidebar:not(:hover) .nav-link span:not(.material-icons) {
  opacity: 0;
  width: 0;
  margin: 0;
}

/* ========== RODAPÉ (LOGOUT) ========== */
.bottom {
  padding: 1rem;
}
.logout-btn {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 1.75rem;
  background: none;
  border: none;
  color: #fff;
  text-align: left;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
}
.logout-btn:hover {
  background-color: #1f1f1f;
}
.logout-btn .material-icons {
  font-size: 3rem;                  /* ícone maior */
  flex-shrink: 0;
}

/* esconde o texto "Sair" quando fechada */
.logout-btn span:not(.material-icons) {
  transition: opacity 0.3s ease, width 0.3s ease, margin 0.3s ease;
}
.sidebar:not(:hover) .logout-btn span:not(.material-icons) {
  opacity: 0;
  width: 0;
  margin: 0;
}
</style>
