<template>
    <div class="min-h-screen bg-gray-100 text-gray-800 p-6">
      <h1 class="text-3xl font-bold mb-6 text-center">Gerenciador de Salas</h1>
      <SalaView />
    </div>
  </template>
  
  <script setup>
  import SalaView from './components/SalaView.vue'
  </script>
  
<style>
html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  overflow-x: hidden;
}

</style>

  