<template>
  <div class="pagina">
    <div class="titulo">
      <h1>Cadastrar Professor</h1>
    </div>

    <div class="caixa-cinza">
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

      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th colspan="2">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="professor in professoresFiltrados" :key="professor.id">
            <td>{{ professor.nome }}</td>
            <td>{{ professor.email }}</td>
            <td class="acoes" colspan="2">
              <div class="botao-coluna">
                <span class="botao-texto">Editar</span>
                <button @click="abrirModalEditar(professor)" class="icon-btn">
                  <span class="material-icons">edit</span>
                </button>
              </div>
              <div class="botao-coluna">
                <span class="botao-texto">Excluir</span>
                <button @click="deletarProfessor(professor.id)" class="icon-btn delete">
                  <span class="material-icons">close</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="modalAberta" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editando ? 'Editar Professor' : 'Novo Professor' }}</h2>
          <button @click="fecharModal" class="fechar-btn">
            <span class="material-icons">close</span>
          </button>
        </div>
        <form @submit.prevent="salvarProfessor">
          <div class="form-group">
            <label>Nome</label>
            <input v-model="form.nome" type="text" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="form.email" type="email" required />
          </div>
          <div class="form-group">
            <label>Senha</label>
            <input v-model="form.senha" type="password" :required="!editando" />
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
  name: 'ProfessorView',
  data() {
    return {
      professores: [],
      professoresFiltrados: [],
      termoPesquisa: '',
      modalAberta: false,
      editando: false,
      professorEditandoId: null,
      form: {
        nome: '',
        email: '',
        senha: ''
      }
    };
  },
  mounted() {
    this.buscarProfessores();
  },
  methods: {
    async buscarProfessores() {
      try {
        const res = await axios.get('/api/professores');
        this.professores = res.data;
        this.professoresFiltrados = res.data;
      } catch (error) {
        console.error('Erro ao buscar professores:', error);
      }
    },
    normalizar(texto) {
      return texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    },
    aplicarFiltro: _.debounce(function () {
      const termo = this.normalizar(this.termoPesquisa);
      this.professoresFiltrados = this.professores.filter(professor =>
        this.normalizar(professor.nome).includes(termo) ||
        this.normalizar(professor.email).includes(termo)
      );
    }, 300),
    abrirModalCriar() {
      this.form = { nome: '', email: '', senha: '' };
      this.editando = false;
      this.modalAberta = true;
    },
    abrirModalEditar(professor) {
      this.form = { nome: professor.nome, email: professor.email, senha: '' };
      this.professorEditandoId = professor.id;
      this.editando = true;
      this.modalAberta = true;
    },
    fecharModal() {
      this.modalAberta = false;
    },
    async salvarProfessor() {
      try {
        const payload = {
          nome: this.form.nome,
          email: this.form.email
        };

        if (!this.editando || this.form.senha) {
          payload.senha = this.form.senha;
        }

        if (this.editando) {
          await axios.put(`/api/professores/${this.professorEditandoId}`, payload);
          Swal.fire('Atualizado!', 'Professor atualizado com sucesso.', 'success');
        } else {
          await axios.post('/api/professores', payload);
          Swal.fire('Cadastrado!', 'Professor cadastrado com sucesso.', 'success');
        }

        this.fecharModal();
        this.buscarProfessores();
      } catch (error) {
  const errs = error.response?.data?.errors;
  const msg = errs
    ? Object.values(errs).flat()[0]
    : (error.response?.data?.message || 'Erro ao salvar professor.');
  Swal.fire('Erro!', msg, 'error');
  console.error(error);
}

    },
    async deletarProfessor(id) {
      const confirmacao = await Swal.fire({
        title: 'Tem certeza?',
        text: 'Você deseja excluir este professor?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF6F00',
        cancelButtonColor: '#505050',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
      });

      if (confirmacao.isConfirmed) {
        try {
          await axios.delete(`/api/professores/${id}`);
          this.buscarProfessores();
          Swal.fire('Excluído!', 'O professor foi excluído.', 'success');
        } catch (error) {
          Swal.fire('Erro!', 'Não foi possível excluir o professor.', 'error');
        }
      }
    }
  }
};
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

.new-btn:hover {
  background: #FF8533;
}

/* Tabela */
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

/* Modal */
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
input[type="email"],
input[type="password"] {
  padding: 10px;
  border-radius: 10px;
  border: none;
  background-color: #666;
  color: white;
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
