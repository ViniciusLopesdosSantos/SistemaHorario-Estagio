<template>
  <div class="landing">
    <div class="card" role="group" aria-label="Escolha de perfil">
      <h1 class="titulo">Sistema de Horário</h1>

      <img class="logo" :src="logo" alt="Computação UNIFIL" />

      <button class="btn" @click="goProfessor" @keyup.enter="goProfessor">
        Professor
      </button>

      <button class="btn" @click="goAluno" @keyup.enter="goAluno">
        Aluno
      </button>
    </div>
  </div>
</template>

<script>
import logo from '@/assets/computacao.jpg'

export default {
  name: 'LandingView',
  data: () => ({ logo }),
  methods: {
    goProfessor() { this.$router.push({ name: 'login' }) },
    goAluno() { this.$router.push({ name: 'aluno' }) },
  },
}
</script>

<style scoped>
.landing{
  min-height:100vh; display:flex; align-items:center; justify-content:center;
  background:#FF6528; font-family:sans-serif;
}
.card{
  width:min(860px,92vw);
  background:#000000; color:#fff;
  border-radius:36px; padding:60px 64px;
  text-align:center; box-shadow:0 18px 40px rgba(0,0,0,.35);
}
.titulo{font-size:3.4rem; font-weight:800; margin:0 0 16px}
.logo{width:84px; height:auto; display:block; margin:8px auto 34px}
.btn{
  width:min(560px,92%); height:74px; margin:14px auto;
  background:#B3B3B3; color:#fff; font-weight:800; font-size:2rem;
  border:none; border-radius:18px; cursor:pointer;
  transition:filter .15s, transform .02s;
}
.btn:hover{ filter:brightness(1.05) }
.btn:active{ transform:scale(.995) }
@media (max-width:520px){
  .titulo{font-size:2.6rem}
  .btn{height:66px; font-size:1.6rem}
}
</style>
