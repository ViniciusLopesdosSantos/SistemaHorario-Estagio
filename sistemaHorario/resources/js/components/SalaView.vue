<template>
  <div class="pagina">
    <div class="titulo">
      <h1>Cadastrar Sala</h1>
    </div>

    <div class="caixa-cinza">
      <!-- Barra de pesquisa e botões -->
      <div class="barra-superior">
        <div class="relative w-60">
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
            <th>Salas</th>
            <th>Quantidade de lugares</th>
            <th colspan="2">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sala in salasFiltradas" :key="sala.id_sala">
            <td>{{ sala.nome }}</td>
            <td>{{ sala.capacidade }}</td>
            <td class="acoes" colspan="2">
              <div class="botao-coluna">
                <span class="botao-texto">Editar</span>
                <button @click="abrirModalEditar(sala)" class="icon-btn">
                  <span class="material-icons">edit</span>
                </button>
              </div>
              <div class="botao-coluna">
                <span class="botao-texto">Excluir</span>
                <button @click="deletarSala(sala.id_sala)" class="icon-btn delete">
                  <span class="material-icons">close</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="modalAberto" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editando ? 'Editar Sala' : 'Nova Sala' }}</h2>
          <button @click="fecharModal" class="fechar-btn">
            <span class="material-icons">close</span>
          </button>
        </div>
        <form @submit.prevent="editando ? atualizarSala() : criarSala()">
          <div class="form-group">
            <label>Sala</label>
            <input v-model="form.nome" type="text" required />
            <span v-if="!form.nome" class="error">*Obrigatório</span>
          </div>
          <div class="form-group">
            <label>Quantidade de Lugares</label>
            <input v-model="form.capacidade" type="number" required />
            <span v-if="!form.capacidade" class="error">*Obrigatório</span>
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
  data() {
    return {
      salas: [],
      termoPesquisa: '',
      salasFiltradas: [],
      modalAberto: false,
      editando: false,
      salaEditandoId: null,
      form: {
        nome: '',
        capacidade: ''
      }
    }
  },
  methods: {
    async buscarSalas() {
      try {
        const res = await axios.get('/api/salas')
        this.salas = res.data
        this.salasFiltradas = res.data
      } catch (error) {
        console.error('Erro ao buscar salas:', error)
      }
    },
    normalizar(texto) {
      return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
    },
    aplicarFiltro: _.debounce(function () {
      const termo = this.normalizar(this.termoPesquisa)
      this.salasFiltradas = this.salas.filter(sala =>
        this.normalizar(sala.nome).includes(termo) ||
        sala.capacidade.toString().includes(termo)
      )
    }, 300),
    limparFiltro() {
      this.termoPesquisa = ''
      this.aplicarFiltro()
    },
    async deletarSala(id) {
      const confirmacao = await Swal.fire({
        title: 'Tem certeza?',
        text: 'Você deseja excluir esta sala?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF6F00',
        cancelButtonColor: '#505050',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
      })

      if (confirmacao.isConfirmed) {
        try {
          await axios.delete(`/api/salas/${id}`)
          this.salas = this.salas.filter(sala => sala.id_sala !== id)
          this.aplicarFiltro()
          Swal.fire('Excluído!', 'A sala foi excluída.', 'success')
        } catch (error) {
          Swal.fire('Erro!', 'Não foi possível excluir a sala.', 'error')
        }
      }
    },
    abrirModalCriar() {
      this.form = { nome: '', capacidade: '' }
      this.editando = false
      this.modalAberto = true
    },
    abrirModalEditar(sala) {
      this.form = { nome: sala.nome, capacidade: sala.capacidade }
      this.salaEditandoId = sala.id_sala
      this.editando = true
      this.modalAberto = true
    },
    fecharModal() {
      this.modalAberto = false
    },
    async criarSala() {
      try {
        const res = await axios.post('/api/salas', this.form)
        this.salas.push(res.data)
        this.aplicarFiltro()
        this.fecharModal()
        Swal.fire('Sucesso!', 'Sala cadastrada com sucesso.', 'success')
      } catch (error) {
        const msg = error.response?.data?.message || 'Erro ao cadastrar sala.'
        Swal.fire('Erro!', msg, 'error')
      }
    },
    async atualizarSala() {
      try {
        const res = await axios.put(`/api/salas/${this.salaEditandoId}`, this.form)
        const index = this.salas.findIndex(sala => sala.id_sala === this.salaEditandoId)
        if (index !== -1) {
          this.salas[index] = { ...res.data }
          this.aplicarFiltro()
        }
        this.fecharModal()
        Swal.fire('Atualizado!', 'Sala atualizada com sucesso.', 'success')
      } catch (error) {
        const msg = error.response?.data?.message || 'Erro ao atualizar sala.'
        Swal.fire('Erro!', msg, 'error')
      }
    }
  },
  mounted() {
    this.buscarSalas()
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

.barra-superior .flex {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-bar {
  width: 240px;
  height: 34px;
  border-radius: 15px;
  background: white;
  padding: 0 30px 0 15px; /* espaço maior à direita */
  font-size: 0.95rem;
  border: none;
  color: black;
  position: relative; /* não obrigatório, mas pode manter */
}


.search-bar::placeholder {
  color: white;
  opacity: 1;
}

.filter-btn,
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

.filter-btn:hover,
.new-btn:hover {
  background: #FF8533;
}

table {
  width: 100%;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 10px;
  overflow: hidden;
}

th {
  text-align: center;
  padding: 15px;
  font-size: 1.2rem;
  color: black;
  font-weight: bold;
}

td {
  text-align: center;
  padding: 15px;
  border-bottom: 1px solid #505050;
  font-size: 1rem;
}

td.acoes {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
}

.botao-coluna {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.botao-texto {
  font-size: 0.8rem;
  color: black;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.3rem;
  color: black;
}

.icon-btn.delete {
  color: red;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  background: rgba(0,0,0,0.5);
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: #505050;
  padding: 30px;
  border-radius: 20px;
  width: 500px;
  color: white;
  position: relative;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.fechar-btn {
  background: none;
  border: none;
  color: white;
  font-size: 2rem;
  cursor: pointer;
}

.form-group {
  margin: 20px 0;
  display: flex;
  flex-direction: column;
}

input[type="text"],
input[type="number"] {
  padding: 10px;
  border-radius: 10px;
  border: none;
  background-color: #666;
  color: white;
}

.error {
  color: #FF6F00;
  font-size: 0.9rem;
  margin-top: 5px;
}

.botoes-modal {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.cancelar-btn {
  background-color: #333;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
}

.cancelar-btn:hover {
  background-color: #555;
}

.btn-cadastrar {
  padding: 10px 15px;
  background-color: #FF6F00;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: bold;
  cursor: pointer;
}

.btn-cadastrar:hover {
  background-color: #FF8533;
}
</style>
