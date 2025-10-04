<template>
  <div class="pagina">
    <div class="titulo"><h1>Horários</h1></div>

    <div class="caixa-cinza">
      <div class="seletor-turma">
        <span class="material-icons">groups</span>
        <select v-model="turmaSelecionadaId" class="select-turma">
          <option :value="null" disabled>Selecione uma Turma</option>
          <option v-for="turma in turmas" :key="turma.id" :value="turma.id">
            {{ turma.nome }}
          </option>
        </select>
      </div>

      <div class="grade-container" v-if="turmaSelecionadaId">
        <div v-for="dia in diasDaSemana" :key="dia" class="dia-coluna" :style="{ 'grid-column': diasDaSemana.indexOf(dia) + 2 }">
          {{ dia }}
        </div>

        <template v-for="bloco in blocosDeTempo">
          <div v-if="!bloco.isIntervalo" :key="bloco.id + '-label'" class="bloco-tempo" :style="{ 'grid-row': bloco.gridRow }">
            {{ bloco.label }}
          </div>

          <template v-if="bloco.isIntervalo">
            <div :key="bloco.id + '-intervalo'" class="celula-intervalo" :style="{ 'grid-row': bloco.gridRow, 'grid-column': '2 / 7' }">
              INTERVALO
            </div>
          </template>

          <template v-else>
            <div
              v-for="dia in diasDaSemana"
              :key="dia + bloco.id"
              class="celula-horario"
              :style="{ 'grid-column': diasDaSemana.indexOf(dia) + 2, 'grid-row': bloco.gridRow }"
            >
              <div v-if="getHorarioNaCelula(dia, bloco.inicio)" class="uc-card">
                <p class="uc-nome">{{ getHorarioNaCelula(dia, bloco.inicio).uc_nome }}</p>
                <p class="uc-info"><strong>Grupo:</strong> {{ getHorarioNaCelula(dia, bloco.inicio).uc_grupo }}</p>
                <p class="uc-info"><strong>Código:</strong> {{ getHorarioNaCelula(dia, bloco.inicio).uc_codigo }}</p>
                <p class="uc-info">{{ getHorarioNaCelula(dia, bloco.inicio).professor?.nome }}</p>
                <p class="uc-info"><strong>Sala:</strong> {{ getHorarioNaCelula(dia, bloco.inicio).sala?.nome }}</p>
                <p v-if="getHorarioNaCelula(dia, bloco.inicio).classroom_link" class="uc-info">
                  <strong>Classroom:</strong>
                  <a :href="getHorarioNaCelula(dia, bloco.inicio).classroom_link" target="_blank" class="classroom-link">Acessar</a>
                </p>
                <div class="uc-actions">
                  <button @click="abrirModalEdicao(getHorarioNaCelula(dia, bloco.inicio))" class="icon-btn-uc" title="Editar">✏️</button>
                  <button @click="deletarHorario(getHorarioNaCelula(dia, bloco.inicio).id)" class="icon-btn-uc" title="Excluir">🗑️</button>
                </div>
              </div>

              <div v-else class="incluir-uc" @click="abrirModalCriacao(dia, bloco.inicio, bloco.fim)">
                <span class="material-icons">add_circle</span>
                <span>Incluir UC</span>
              </div>
            </div>
          </template>
        </template>
      </div>

      <div v-else class="selecione-turma-aviso">
        <p>Selecione uma turma para visualizar/editar os horários.</p>
      </div>
    </div>

    <div v-if="modalAberto" class="modal-overlay" @click.self="fecharModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editando ? 'Editar Aula' : 'Adicionar Aula' }}</h2>
          <button @click="fecharModal" class="fechar-btn">
            <span class="material-icons">close</span>
          </button>
        </div>

        <div v-if="sugestaoDeJuncao" class="sugestao-juncao">
          <p>
            Já existe uma aula de <strong>{{ sugestaoDeJuncao.uc_nome }}</strong> com
            <strong>{{ sugestaoDeJuncao.professor?.nome }}</strong> neste horário.
          </p>
          <p class="info-capacidade">
            Capacidade da sala: <strong>{{ sugestaoDeJuncao.sala?.capacidade }}</strong> |
            Total de alunos após junção: <strong>{{ totalAlunosAposJuncao }}</strong>
          </p>
          <button type="button" @click="preencherComSugestao" class="btn-juntar">
            <span class="material-icons">add_link</span> Juntar-se a esta aula
          </button>
        </div>

        <form @submit.prevent="salvarHorario">
          <div class="form-grid">
            <div class="form-group">
              <label>UC</label>
              <select v-model="form.uc_id" required>
                <option :value="null" disabled>Selecione...</option>
                <option v-for="u in ucs" :key="u.id" :value="u.id">
                  {{ u.uc }} ({{ u.codigo_uc }})
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Código UC</label>
              <input :value="ucSelecionada?.codigo_uc || ''" type="text" disabled />
            </div>
            <div class="form-group">
              <label>Grupo</label>
              <input :value="ucSelecionada?.grupo || ''" type="text" disabled />
            </div>

            <div class="form-group">
              <label>Professor</label>
              <select v-model="form.professor_id" required>
                <option :value="null" disabled>Selecione...</option>
                <option v-for="p in professores" :key="p.id" :value="p.id">{{ p.nome }}</option>
              </select>
            </div>

            <div class="form-group">
              <label>Sala</label>
              <select v-model="form.sala_id" required>
                <option :value="null" disabled>Selecione...</option>
                <option v-for="s in salasFiltradas" :key="s.id_sala" :value="s.id_sala">
                  {{ s.nome }} (Cap: {{ s.capacidade }})
                </option>
              </select>
              <small v-if="salas.length > 0 && salasFiltradas.length === 0" class="aviso-filtro">
                Nenhuma sala comporta esta turma.
              </small>
            </div>

            <div class="form-group">
              <label>Link do Classroom</label>
              <input v-model="form.classroom_link" type="text" placeholder="Opcional" />
            </div>
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
import Swal from 'sweetalert2'

export default {
  name: 'HorarioView',
  data() {
    return {
      turmas: [], professores: [], salas: [], ucs: [], horarios: [],
      turmaSelecionadaId: null,
      modalAberto: false, editando: false, form: {},
      sugestaoDeJuncao: null, totalAlunosAposJuncao: 0,
      diasDaSemana: ['Segunda','Terça','Quarta','Quinta','Sexta'],
      blocosDeTempo: [
        { id: 1, label: '19h00 - 20h30', inicio: '19:00', fim: '20:30', gridRow: 2 },
        { id: 2, label: 'Intervalo', isIntervalo: true, gridRow: 3 },
        { id: 3, label: '20h45 - 22h15', inicio: '20:45', fim: '22:15', gridRow: 4 },
      ],
    }
  },
  computed: {
    horariosDaTurma() { return this.horarios.filter(h => h.turma_id === this.turmaSelecionadaId) },
    turmaAtual() { return this.turmas.find(t => t.id === this.turmaSelecionadaId) || null },
    salasFiltradas() {
      if (!this.turmaAtual) return []
      const n = this.turmaAtual.quantidade_alunos || 0
      return this.salas.filter(s => (s.capacidade || 0) >= n) // mantém s.id_sala
    },
    ucSelecionada() { return this.ucs.find(u => u.id === this.form.uc_id) || null },
  },
  async mounted() { await this.buscarDadosIniciais() },
  methods: {
    async buscarDadosIniciais() {
      try {
        const [t,p,s,h,u] = await Promise.all([
          axios.get('/api/turmas'),
          axios.get('/api/professores'),
          axios.get('/api/salas'),
          axios.get('/api/horarios'),
          axios.get('/api/unidades-curriculares'),
        ])
        this.turmas=t.data; this.professores=p.data; this.salas=s.data; this.horarios=h.data; this.ucs=u.data
      } catch { Swal.fire('Erro','Falha ao carregar dados iniciais.','error') }
    },
    getHorarioNaCelula(dia, horaInicio) {
      return this.horariosDaTurma.find(h => h.dia_semana===dia && h.hora_inicio===horaInicio) || null
    },
    abrirModalCriacao(dia, horaInicio, horaFim) {
      this.editando=false; this.sugestaoDeJuncao=null; this.totalAlunosAposJuncao=0
      const existente = this.horarios.find(h => h.dia_semana===dia && h.hora_inicio===horaInicio && h.turma_id!==this.turmaSelecionadaId)
      if (existente && this.turmaAtual) {
        const naMesmaSalaESlot = this.horarios.filter(h => h.sala_id===existente.sala_id && h.dia_semana===dia && h.hora_inicio===horaInicio)
        const turmasIds = naMesmaSalaESlot.map(h=>h.turma_id)
        const alunosJaNaSala = this.turmas.filter(t=>turmasIds.includes(t.id)).reduce((a,t)=>a+(t.quantidade_alunos||0),0)
        const totalApos = alunosJaNaSala + (this.turmaAtual.quantidade_alunos||0)
        if ((existente.sala?.capacidade||0) >= totalApos) { this.sugestaoDeJuncao=existente; this.totalAlunosAposJuncao=totalApos }
      }
      this.form = {
        turma_id:this.turmaSelecionadaId, dia_semana:dia, hora_inicio:horaInicio, hora_fim:horaFim,
        uc_id:null, professor_id:null, sala_id:null, classroom_link:''
      }
      this.modalAberto=true
    },
    preencherComSugestao() {
      if (!this.sugestaoDeJuncao) return
      const s=this.sugestaoDeJuncao
      this.form.uc_id=s.uc_id||null; this.form.professor_id=s.professor_id; this.form.sala_id=s.sala_id; this.form.classroom_link=s.classroom_link
      this.sugestaoDeJuncao=null
    },
    abrirModalEdicao(h) {
      this.editando=true; this.sugestaoDeJuncao=null
      this.form={ id:h.id, turma_id:h.turma_id, dia_semana:h.dia_semana, hora_inicio:h.hora_inicio, hora_fim:h.hora_fim,
                  uc_id:h.uc_id||null, professor_id:h.professor_id, sala_id:h.sala_id, classroom_link:h.classroom_link||'' }
      this.modalAberto=true
    },
    fecharModal(){ this.modalAberto=false; this.form={}; this.sugestaoDeJuncao=null },
    async salvarHorario() {
      try {
        const payload={...this.form}
        const resp = this.editando ? await axios.put(`/api/horarios/${payload.id}`, payload)
                                   : await axios.post('/api/horarios', payload)
        const novo = resp.data
        const i = this.horarios.findIndex(h=>h.id===novo.id)
        if (i>=0) this.horarios.splice(i,1,novo); else this.horarios.push(novo)
        Swal.fire('Sucesso', `Horário ${this.editando?'atualizado':'cadastrado'}.`, 'success')
        this.fecharModal()
      } catch (e) {
        if (e.response?.status===422) {
          const errors=e.response.data.errors||{}; const first=Object.values(errors).flat()[0]||'Erro de validação.'
          Swal.fire('Erro', first, 'error')
        } else {
          Swal.fire('Erro', e.response?.data?.message||'Falha ao salvar.', 'error')
        }
      }
    },
    async deletarHorario(id){
      const ok=await Swal.fire({title:'Excluir?',text:'Esta ação não pode ser desfeita.',icon:'warning',showCancelButton:true,
        confirmButtonColor:'#FF6F00',cancelButtonColor:'#505050',confirmButtonText:'Sim, excluir',cancelButtonText:'Cancelar'})
      if(!ok.isConfirmed) return
      try{ await axios.delete(`/api/horarios/${id}`); this.horarios=this.horarios.filter(h=>h.id!==id); Swal.fire('Excluído','Horário removido.','success')}
      catch{ Swal.fire('Erro','Não foi possível excluir.','error')}
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');
.pagina{background:#FF6528;min-height:100vh;padding:40px;font-family:sans-serif}
.titulo h1{text-align:center;font-size:4rem;color:black;font-weight:bold;margin-bottom:20px}
.caixa-cinza{background:#505050;border-radius:20px;padding:30px;width:90%;max-width:1400px;margin:0 auto;color:white}
.seletor-turma{display:flex;align-items:center;gap:1rem;margin-bottom:2rem;background:#444;padding:.5rem 1rem;border-radius:12px;max-width:400px;border:1px solid #666}
.seletor-turma .material-icons{font-size:2rem}
.select-turma{width:100%;background:none;border:none;color:white;font-size:1.2rem;font-weight:bold;outline:none;cursor:pointer}
.select-turma option{background:#333}
.grade-container{display:grid;grid-template-columns:120px repeat(5,1fr);grid-template-rows:auto 1fr 50px 1fr;gap:8px}
.dia-coluna{grid-row:1;background:#3a3a3a;padding:1rem;text-align:center;font-weight:bold;border-radius:8px;display:flex;align-items:center;justify-content:center}
.bloco-tempo{grid-column:1;display:flex;align-items:center;justify-content:center;padding:1rem;font-weight:bold;text-align:center;color:#ddd}
.celula-horario{background:#e0e0e0;border-radius:8px;min-height:160px;padding:1rem;display:flex;flex-direction:column;justify-content:center;align-items:center;color:#333}
.celula-intervalo{background:transparent;color:#aaa;display:flex;align-items:center;justify-content:center;font-weight:bold;letter-spacing:.1em;border-radius:8px;border:1px dashed #666}
.uc-card{width:100%;height:100%;display:flex;flex-direction:column;text-align:left;font-size:.9rem}
.uc-nome{font-weight:bold;font-size:1rem;margin-bottom:.5rem;color:#000}
.uc-info{margin:3px 0;color:#222}.uc-info strong{color:#000}
.classroom-link{color:#FF6F00;text-decoration:none;font-weight:bold}.classroom-link:hover{text-decoration:underline}
.uc-actions{margin-top:auto;align-self:flex-end}
.icon-btn-uc{background:none;border:none;cursor:pointer;font-size:1.1rem;margin-left:8px;padding:4px;transition:transform .2s}
.icon-btn-uc:hover{transform:scale(1.2)}
.incluir-uc{cursor:pointer;text-align:center;color:#888;display:flex;flex-direction:column;align-items:center;gap:.5rem;transition:color .2s}
.incluir-uc:hover{color:#FF6F00}.incluir-uc .material-icons{font-size:2.5rem}
.selecione-turma-aviso{text-align:center;padding:4rem;font-size:1.2rem;color:#ccc}
.modal-overlay{position:fixed;top:0;left:0;background:rgba(0,0,0,.7);width:100%;height:100%;display:flex;justify-content:center;align-items:center;z-index:1000}
.modal-content{background:#505050;padding:30px;border-radius:20px;width:90%;max-width:800px;color:white;position:relative}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}
.modal-header h2{font-size:2rem;font-weight:bold}.fechar-btn{background:none;border:none;color:white;font-size:2rem;cursor:pointer}
.form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5rem}
.form-group{display:flex;flex-direction:column;gap:.5rem}
.form-group label{font-weight:bold}
.form-group input,.form-group select{padding:12px;border-radius:10px;border:none;background:#666;color:white;font-size:1rem}
.form-group input:disabled,.form-group select:disabled{background:#444;color:#999;cursor:not-allowed}
.botoes-modal{display:flex;justify-content:flex-end;gap:10px;margin-top:2rem}
.cancelar-btn{background:#333;color:white;padding:10px 20px;border:none;border-radius:10px;cursor:pointer;font-weight:bold}
.btn-cadastrar{padding:10px 20px;background:#FF6F00;color:white;border:none;border-radius:10px;font-weight:bold;cursor:pointer}
.sugestao-juncao{background:#404040;border-left:4px solid #FF6F00;padding:1rem;margin-bottom:1.5rem;border-radius:8px;display:flex;flex-direction:column;gap:.75rem}
.sugestao-juncao p{margin:0;color:#eee}.info-capacidade{font-size:.9rem;color:#ccc}
.btn-juntar{background:#FF6F00;color:white;border:none;padding:8px 12px;border-radius:8px;cursor:pointer;font-weight:bold;display:flex;align-items:center;gap:8px;align-self:flex-start}
.btn-juntar:hover{filter:brightness(1.1)}
</style>
