<template>
  <div class="pagina">
    <div class="titulo">
      <h1>Cadastrar Turma</h1>
    </div>

    <div class="caixa-cinza">
      <!-- Barra de pesquisa e botão Novo -->
      <div class="barra-superior">
        <div class="relative w-full max-w-[240px]">
          <input
            v-model="termoPesquisa"
            @input="aplicarFiltro"
            type="text"
            placeholder="Pesquisar..."
            class="search-bar w-full"
          />
        </div>

        <button @click="abrirModalCriar" class="new-btn">
          <span class="material-icons">add</span> Novo
        </button>
      </div>

      <!-- Tabela -->
      <table>
        <thead>
          <tr>
            <th>Turmas</th>
            <th>Representantes</th>
            <th>Alunos</th>
            <th colspan="2">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in turmasFiltradas" :key="t.id">
            <td>{{ t.nome }}</td>
            <td>{{ t.representante }}</td>
            <td>{{ t.quantidade_alunos }}</td>
            <td class="acoes" colspan="2">
              <div class="botao-coluna">
                <span class="botao-texto">Editar</span>
                <button @click="abrirModalEditar(t)" class="icon-btn">
                  <span class="material-icons">edit</span>
                </button>
              </div>
              <div class="botao-coluna">
                <span class="botao-texto">Excluir</span>
                <button @click="deletarTurma(t.id)" class="icon-btn delete">
                  <span class="material-icons">close</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal (cadastro/edição) - opcional, já preparado -->
    <div v-if="modalAberto" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editando ? 'Editar Turma' : 'Nova Turma' }}</h2>
          <button @click="fecharModal" class="fechar-btn">
            <span class="material-icons">close</span>
          </button>
        </div>

        <form @submit.prevent="editando ? atualizarTurma() : criarTurma()">
          <div class="form-group">
            <label>Turma</label>
            <input v-model="form.nome" type="text" required />
          </div>

          <div class="form-group">
            <label>Representante</label>
            <input v-model="form.representante" type="text" required />
          </div>

          <div class="form-group">
            <label>Quantidade Alunos</label>
            <input v-model.number="form.quantidade_alunos" type="number" min="1" required />
          </div>

          <div class="botoes-modal">
            <button type="button" @click="fecharModal" class="cancelar-btn">Cancelar</button>
            <button type="submit" class="btn-cadastrar">
              {{ editando ? 'Atualizar' : 'Cadastrar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import _ from 'lodash'
import Swal from 'sweetalert2'

export default {
  name: 'TurmaView',
  data() {
    return {
      turmas: [],
      turmasFiltradas: [],
      termoPesquisa: '',

      modalAberto: false,
      editando: false,
      turmaEditandoId: null,
      form: {
        nome: '',
        representante: '',
        quantidade_alunos: ''
      }
    }
  },
  mounted() {
    this.buscarTurmas()
  },
  methods: {
    // === BUSCA E FILTRO (igual às outras telas) ===
    async buscarTurmas() {
      try {
        const { data } = await axios.get('/api/turmas')
        this.turmas = data
        this.turmasFiltradas = data
      } catch (e) {
        console.error('Erro ao buscar turmas:', e)
      }
    },
    normalizar(texto) {
      return (texto ?? '')
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
    },
    aplicarFiltro: _.debounce(function () {
      const termo = this.normalizar(this.termoPesquisa)
      this.turmasFiltradas = this.turmas.filter(t =>
        this.normalizar(t.nome).includes(termo) ||
        this.normalizar(t.representante).includes(termo) ||
        String(t.quantidade_alunos).includes(termo)
      )
    }, 300),

    // === CRUD básico (opcional) ===
    abrirModalCriar() {
      this.form = { nome: '', representante: '', quantidade_alunos: '' }
      this.editando = false
      this.modalAberto = true
    },
    abrirModalEditar(t) {
      this.form = {
        nome: t.nome,
        representante: t.representante,
        quantidade_alunos: t.quantidade_alunos
      }
      this.turmaEditandoId = t.id
      this.editando = true
      this.modalAberto = true
    },
    fecharModal() { this.modalAberto = false },

    async criarTurma() {
      try {
        const { data } = await axios.post('/api/turmas', this.form)
        this.turmas.push(data)
        this.aplicarFiltro()
        this.fecharModal()
        Swal.fire('Sucesso!', 'Turma cadastrada com sucesso.', 'success')
      } catch (error) {
        const errs = error.response?.data?.errors
        const msg = errs ? Object.values(errs).flat()[0]
                         : (error.response?.data?.message || 'Erro ao cadastrar turma.')
        Swal.fire('Erro!', msg, 'error')
      }
    },

    async atualizarTurma() {
      try {
        const { data } = await axios.put(`/api/turmas/${this.turmaEditandoId}`, this.form)
        const idx = this.turmas.findIndex(t => t.id === this.turmaEditandoId)
        if (idx !== -1) this.turmas[idx] = data
        this.aplicarFiltro()
        this.fecharModal()
        Swal.fire('Atualizado!', 'Turma atualizada com sucesso.', 'success')
      } catch (error) {
        const errs = error.response?.data?.errors
        const msg = errs ? Object.values(errs).flat()[0]
                         : (error.response?.data?.message || 'Erro ao atualizar turma.')
        Swal.fire('Erro!', msg, 'error')
      }
    },

    async deletarTurma(id) {
      const ok = await Swal.fire({
        title: 'Tem certeza?',
        text: 'Você deseja excluir esta turma?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF6F00',
        cancelButtonColor: '#505050',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
      })
      if (!ok.isConfirmed) return
      try {
        await axios.delete(`/api/turmas/${id}`)
        this.turmas = this.turmas.filter(t => t.id !== id)
        this.aplicarFiltro()
        Swal.fire('Excluída!', 'A turma foi excluída.', 'success')
      } catch {
        Swal.fire('Erro!', 'Não foi possível excluir a turma.', 'error')
      }
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');

.pagina {
  background-color: #FF6528;
  min-height: 100vh;
  width: 100vw;
  padding: 40px 0;
  margin: 0;
  font-family: sans-serif;
  overflow-x: hidden;
}

.titulo h1 {
  text-align: center;
  font-size: 4rem;
  color: black;
  font-weight: bold;
  margin-bottom: 20px;
}

.caixa-cinza {
  background-color: #505050;
  border-radius: 20px;
  padding: 30px;
  width: 90%;
  max-width: 1400px;
  margin: 0 auto;
}

.barra-superior {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.barra-superior .flex { display: flex; align-items: center; gap: 16px; }

/* === mesma search-bar das telas Salas/Professores === */
.search-bar {
  width: 240px;
  height: 34px;
  border-radius: 15px;
  background: white;
  padding: 0 30px 0 15px;
  font-size: 0.95rem;
  border: none;
  color: black;
  position: relative;
}
.search-bar::placeholder { color: white; opacity: 1; }

.new-btn {
  background: #FF6F00;
  border: none;
  color: white;
  border-radius: 12px;
  height: 34px;
  padding: 0 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: bold;
  cursor: pointer;
}
.new-btn:hover { background: #FF8533; }

table {
  width: 100%;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 10px;
  overflow: hidden;
}
th { text-align: center; padding: 15px; font-size: 1.2rem; color: black; font-weight: bold; }
td { text-align: center; padding: 15px; border-bottom: 1px solid #505050; font-size: 1rem; }

td.acoes {
  display: flex; justify-content: center; align-items: center; gap: 12px;
}
.botao-coluna { display: flex; flex-direction: column; align-items: center; gap: 2px; }
.botao-texto { font-size: 0.8rem; color: black; }
.icon-btn { background: none; border: none; cursor: pointer; font-size: 1.3rem; color: black; }
.icon-btn.delete { color: red; }

/* Modal */
.modal-overlay {
  position: fixed; top: 0; left: 0; background: rgba(0,0,0,0.5);
  width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;
}
.modal-content {
  background: #505050; padding: 30px; border-radius: 20px; width: 500px; color: white; position: relative;
}
.modal-header { display: flex; justify-content: space-between; align-items: center; }
.fechar-btn { background: none; border: none; color: white; font-size: 2rem; cursor: pointer; }
.form-group { margin: 20px 0; display: flex; flex-direction: column; }
input[type="text"], input[type="number"] {
  padding: 10px; border-radius: 10px; border: none; background-color: #666; color: white;
}
.botoes-modal { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
.cancelar-btn {
  background-color: #333; color: white; padding: 10px 15px; border: none; border-radius: 10px; cursor: pointer;
}
.cancelar-btn:hover { background-color: #555; }
.btn-cadastrar {
  padding: 10px 15px; background-color: #FF6F00; color: white; border: none; border-radius: 10px; font-weight: bold; cursor: pointer;
}
.btn-cadastrar:hover { background-color: #FF8533; }
</style>
