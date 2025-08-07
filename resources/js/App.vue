<template>
  <div class="flex">
    <Sidebar />
    <main class="ml-56 flex-1 bg-orange-500 min-h-screen p-8 overflow-auto">
      <router-view/>
    </main>
  </div>
</template>

<script>
import Sidebar from './components/Sidebar.vue'
export default { components: { Sidebar } }
</script>
