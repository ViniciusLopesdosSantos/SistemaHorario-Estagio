<template>
  <!-- Telas públicas (ex.: /login) -->
  <div v-if="!showSidebar">
    <router-view />
  </div>

  <!-- Telas privadas com sidebar -->
  <div v-else class="app-shell">
    <Sidebar />
    <main class="app-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Sidebar from './components/Sidebar.vue'

const route = useRoute()

// Mostra a sidebar apenas nas rotas que pedem auth
const showSidebar = computed(() => route.matched.some(r => r.meta.requiresAuth))
</script>

<style>
.app-shell {
  display: flex;
  min-height: 100vh;
  background: #FF6528;    /* pinta o plano de fundo do shell também */
  overflow-x: hidden;     /* evita qualquer rolagem lateral */
}

/* conteúdo principal “começa” após a largura do sidebar fechado */
.app-content {
  flex: 1;
  min-height: 100vh;
  margin-left: 142px;      /* mesma largura do sidebar fechado */
  padding: 16px;
  background: transparent; /* as páginas já têm o laranja, não precisa branco aqui */
}
</style>

