<template>
  <div class="pagina">
    <h1 class="titulo">Horários Feitos</h1>

    <div class="caixa-cinza">
      <button class="btn-fechar" @click="$router.back()">
        <span class="material-icons">close</span>
      </button>

      <div class="barra-superior">
        <input v-model="termoPesquisa" type="text" placeholder="Pesquisa" class="search-bar" />
      </div>

      <table>
        <thead>
          <tr>
            <th>Horários</th>
            <th>Representantes</th>
            <th>Editar</th>
            <th>Excluir</th>
            <th>Publicar</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in turmasFiltradas" :key="t.id">
            <td>{{ t.nome }}</td>
            <td>{{ t.representante || '-' }}</td>
            <td class="centro">
              <button class="icon-btn" @click="editar(t)">
                <span class="material-icons">edit</span>
              </button>
            </td>
            <td class="centro">
              <button class="icon-btn delete" @click="excluir(t)">
                <span class="material-icons">close</span>
              </button>
            </td>
            <td class="centro">
              <input
                type="checkbox"
                :checked="t.publicado"
                @change="togglePublicar(t, $event)"
              />
            </td>
          </tr>
          <tr v-if="turmasFiltradas.length === 0">
            <td colspan="5" class="vazio">Nenhum horário encontrado.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script>
import axios from 'axios'
import Swal from 'sweetalert2'

export default {
  name: 'HorariosFeitosView',
  data() {
    return { turmas: [], termoPesquisa: '' }
  },
  computed: {
    turmasFiltradas() {
      const n = s => (s ?? '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
      const termo = n(this.termoPesquisa)
      return this.turmas.filter(t => n(t.nome).includes(termo) || n(t.representante).includes(termo))
    }
  },
  mounted() {
    this.buscar()
  },
  methods: {
    // Função para buscar os horários feitos
    async buscar() {
      try {
        const { data } = await axios.get('/api/horarios-feitos')
        this.turmas = data
      } catch (e) {
        Swal.fire('Erro', 'Falha ao carregar horários feitos.', 'error')
      }
    },

    // Função para editar o horário
    async editar(t) {
      try {
        await axios.post(`/api/turmas/${t.id}/horario/reabrir`)
        this.$router.push({ name: 'horarios', query: { turma: t.id } })
      } catch (e) {
        Swal.fire('Erro', e.response?.data?.message || 'Não foi possível reabrir para edição.', 'error')
      }
    },

    // Função para excluir o horário
    async excluir(t) {
      const ok = await Swal.fire({
        title: 'Confirmar exclusão?',
        text: 'As aulas da turma serão apagadas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF6F00',
        cancelButtonColor: '#505050',
        confirmButtonText: 'Excluir',
        cancelButtonText: 'Cancelar'
      })
      if (!ok.isConfirmed) return
      try {
        await axios.delete(`/api/turmas/${t.id}/horario`)
        this.turmas = this.turmas.filter(x => x.id !== t.id)
        Swal.fire('Excluído', 'Horário removido.', 'success')
      } catch {
        Swal.fire('Erro', 'Não foi possível excluir.', 'error')
      }
    },

    // Função para alternar o status de "publicar"
    async togglePublicar(t, ev) {
      const novo = ev.target.checked
      try {
        const { data } = await axios.patch(`/api/turmas/${t.id}/publicar`, { publicado: novo })
        t.publicado = data.publicado
      } catch (e) {
        ev.target.checked = !novo // Reverte o checkbox
        Swal.fire('Erro', e.response?.data?.message || 'Falha ao atualizar publicação.', 'error')
      }
    }
  }
}
</script>
<style scoped>
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');

.pagina {
  background: #ff6528;
  min-height: 100vh;
  padding: 40px 0;
}

.titulo {
  text-align: center;
  font-size: 3rem;
  color: black;
  margin-bottom: 20px;
  font-weight: 700;
}

.caixa-cinza {
  position: relative;
  background: #808080;
  border-radius: 18px;
  width: 90%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px;
}

.btn-fechar {
  position: absolute;
  right: 16px;
  top: 12px;
  background: transparent;
  border: 0;
  cursor: pointer;
}

.barra-superior {
  margin: 12px 0 20px 0;
}

.search-bar {
  width: 280px;
  height: 36px;
  border-radius: 14px;
  background: #fff;
  border: 0;
  padding: 0 14px;
  font-size: .95rem;
}

table {
  width: 100%;
  background: rgba(255, 255, 255, .7);
  border-radius: 10px;
  overflow: hidden;
  border-collapse: collapse;
}
th,
td {
  padding: 14px;
  border-bottom: 1px solid #505050;
  text-align: center;
  color: #000;
}
th {
  font-weight: 700;
  font-size: 1.05rem;
}
tr:last-child td {
  border-bottom: 0;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.3rem;
  color: black;
}
.icon-btn.delete {
  color: #e53935;
}

.centro {
  text-align: center;
}

.vazio {
  padding: 26px;
  color: #222;
}
</style>
