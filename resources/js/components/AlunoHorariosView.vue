<template>
  <div class="wrap">
    <header class="topbar">
      <div class="brand">
        <img :src="logo" alt="Computação UNIFIL" />
        <div class="titulo-site">
          <span>Sistema de</span><strong>Horário</strong>
        </div>
      </div>

      <h1 class="titulo">Horários</h1>

      <button class="sair" @click="$router.push({ name: 'home' })">
        <span class="material-icons">logout</span> Sair
      </button>
    </header>

    <div class="conteudo">
      <div class="busca">
        <input
          v-model="termo"
          @input="onType"
          list="lista-turmas"
          class="search"
          placeholder="Pesquisa"
        />
        <datalist id="lista-turmas">
          <option v-for="t in turmas" :key="t.id" :value="t.nome" />
        </datalist>
      </div>

      <!-- VISUALIZAÇÃO PARA TURMA FLEX -->
      <section v-if="turma && isFlex && gradePronta" ref="gridArea" class="quadro quadro-flex">
        <div class="quadro-top">
          <div class="curso">{{ turma.nome }}</div>
          <button class="btn-export" @click="exportar">Exportar</button>
        </div>

        <div class="lista-flex">
          <div class="lista-flex-header">
            <div class="col-dia">Dia</div>
            <div class="col-horario">Horário</div>
            <div class="col-uc">UC</div>
            <div class="col-codigo">Código</div>
            <div class="col-grupo">Grupo</div>
            <div class="col-sala">Sala</div>
            <div class="col-professor">Professor</div>
            <div class="col-classroom">Classroom</div>
          </div>

          <div v-for="(h, index) in horarios" :key="index" class="lista-flex-item">
            <div class="col-dia">{{ h.dia_semana }}</div>
            <div class="col-horario">{{ h.hora_inicio }} - {{ h.hora_fim }}</div>
            <div class="col-uc">{{ h.uc }}</div>
            <div class="col-codigo">{{ h.codigo_uc }}</div>
            <div class="col-grupo">{{ h.grupo }}</div>
            <div class="col-sala">{{ h.sala }}</div>
            <div class="col-professor">{{ h.professor }}</div>
            <div class="col-classroom">
              <a v-if="h.classroom" :href="h.classroom" target="_blank" class="classroom-link">
                Acessar
              </a>
              <span v-else>-</span>
            </div>
          </div>
        </div>
      </section>

      <!-- VISUALIZAÇÃO PARA TURMAS NORMAIS (GRID) -->
      <section v-else-if="turma && !isFlex && gradePronta" ref="gridArea" class="quadro">
        <div class="quadro-top">
          <div class="curso">{{ turma.nome }}</div>
          <button class="btn-export" @click="exportar">Exportar</button>
        </div>

        <div class="grid">
          <div class="col-horas"></div>
          <div v-for="d in dias" :key="d" class="th-dia">{{ d.toUpperCase() }}</div>

          <template v-for="b in blocos" :key="'blk-' + b.id">
            <div class="th-hora">{{ b.label }}</div>

            <div v-if="b.isIntervalo" class="intervalo" :style="{ gridColumn: '2 / 7' }">
              INTERVALO
            </div>

            <template v-else>
              <div v-for="d in dias" :key="d + b.id" class="celula">
                <div v-if="aula(d, b.inicio)" class="card">
                  <div class="uc">{{ aula(d, b.inicio).uc }}</div>
                  <div class="linha">Grupo: {{ aula(d, b.inicio).grupo }}</div>
                  <div class="linha">Código: {{ aula(d, b.inicio).codigo_uc }}</div>
                  <div class="linha">{{ aula(d, b.inicio).professor }}</div>
                  <div class="linha">Sala: {{ aula(d, b.inicio).sala }}</div>
                  <div v-if="aula(d, b.inicio).classroom" class="linha">
                    <a :href="aula(d, b.inicio).classroom" target="_blank" class="classroom-link-grid">
                      Classroom
                    </a>
                  </div>
                </div>
              </div>
            </template>
          </template>
        </div>
      </section>

      <!-- Seção de Atividades Digitais -->
      <section v-if="atividadesDigitais.length > 0" class="atividades-digitais-container">
        <div class="atividades-digitais-titulo">
          ATIVIDADES DIGITAIS*
        </div>
        <div class="atividades-digitais-lista">
          <div v-for="ativ in atividadesDigitais" :key="ativ.codigo_uc" class="atividade-item">
            <span class="atividade-codigo">{{ ativ.codigo_uc }}</span>
            -
            <span class="atividade-nome">{{ ativ.nome_uc }}</span>
          </div>
        </div>
      </section>

      <!-- NOVO: Seção de Horários FLEX (aparece para TODAS as turmas, exceto a própria FLEX) -->
      <section v-if="horariosFlex.length > 0 && !isFlex" class="horarios-flex-container">
        <div class="horarios-flex-titulo">
          📚 HORÁRIOS FLEX
        </div>
        
        <div class="lista-flex-compacta">
          <div class="lista-flex-header-compacta">
            <div class="col-dia-flex">Dia</div>
            <div class="col-horario-flex">Horário</div>
            <div class="col-uc-flex">UC</div>
            <div class="col-codigo-flex">Código</div>
            <div class="col-grupo-flex">Grupo</div>
            <div class="col-sala-flex">Sala</div>
            <div class="col-professor-flex">Professor</div>
            <div class="col-classroom-flex">Classroom</div>
          </div>

          <div v-for="(h, index) in horariosFlex" :key="'flex-' + index" class="lista-flex-item-compacta">
            <div class="col-dia-flex">{{ h.dia_semana }}</div>
            <div class="col-horario-flex">{{ h.hora_inicio }} - {{ h.hora_fim }}</div>
            <div class="col-uc-flex">{{ h.uc }}</div>
            <div class="col-codigo-flex">{{ h.codigo_uc }}</div>
            <div class="col-grupo-flex">{{ h.grupo }}</div>
            <div class="col-sala-flex">{{ h.sala }}</div>
            <div class="col-professor-flex">{{ h.professor }}</div>
            <div class="col-classroom-flex">
              <a v-if="h.classroom" :href="h.classroom" target="_blank" class="classroom-link">
                Acessar
              </a>
              <span v-else>-</span>
            </div>
          </div>
        </div>
      </section>

      <div v-else-if="!turma" class="placeholder">Pesquise e selecione uma turma publicada.</div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import logo from "@/assets/computacao.jpg";

export default {
  name: "AlunoHorariosView",
  data() {
    return {
      logo,
      turmas: [],
      termo: "",
      turma: null,
      horarios: [],
      atividadesDigitais: [],
      horariosFlex: [],
      isFlex: false,
      dias: ["Segunda", "Terça", "Quarta", "Quinta", "Sexta"],
      blocos: [
        { id: 1, label: "19h00 - 20h30", inicio: "19:00", fim: "20:30" },
        { id: 2, label: "20h30 - 20h45", isIntervalo: true },
        { id: 3, label: "20h45 - 22h15", inicio: "20:45", fim: "22:15" },
      ],
    };
  },
  computed: {
    gradePronta() {
      return this.turma && (this.horarios.length > 0 || this.atividadesDigitais.length > 0);
    },
  },
  mounted() {
    this.carregarTurmas();
  },
  methods: {
    async carregarTurmas() {
      try {
        const { data } = await axios.get("/api/public/turmas-publicadas");
        this.turmas = data;
      } catch {
        Swal.fire("Erro", "Falha ao carregar turmas publicadas.", "error");
      }
    },

    onType() {
      const nome = (this.termo || "").trim().toLowerCase();
      const t = this.turmas.find((x) => x.nome.toLowerCase() === nome);
      if (t && (!this.turma || t.id !== this.turma.id)) this.selecionarTurma(t);
    },

    async selecionarTurma(t) {
      try {
        const { data } = await axios.get(`/api/public/horarios/${t.id}`);
        this.turma = data.turma;
        this.horarios = data.horarios;
        this.atividadesDigitais = data.atividades_digitais || [];
        this.horariosFlex = data.horarios_flex || [];
        this.isFlex = data.is_flex || false;
      } catch (e) {
        Swal.fire(
          "Erro",
          e.response?.data?.message || "Não foi possível carregar a grade.",
          "error"
        );
      }
    },

    aula(dia, inicio) {
      const normalizarHora = (hora) => {
        if (!hora) return null;
        return hora.substring(0, 5);
      };

      return this.horarios.find(
        (h) =>
          h.dia_semana.toLowerCase() === dia.toLowerCase() &&
          normalizarHora(h.hora_inicio) === inicio
      ) || null;
    },

      async exportar() {
    const el = this.$refs.gridArea;
    if (!el) return;

    if (!window.html2canvas) {
      await new Promise((ok, err) => {
        const s = document.createElement("script");
        s.src = "https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js";
        s.onload = ok;
        s.onerror = err;
        document.head.appendChild(s);
      }).catch(() => {});
    }

    if (!window.html2canvas) {
      window.print();
      return;
    }

    // Criar container temporário para incluir tudo na exportação
    const container = document.createElement("div");
    container.style.cssText = "position: absolute; left: -9999px; top: 0; background: white; padding: 20px;";
    
    // Clone o horário principal
    const cloneGrid = el.cloneNode(true);
    container.appendChild(cloneGrid);
    
    // Adicionar atividades digitais se existir
    if (this.atividadesDigitais.length > 0) {
      const atividadesEl = document.querySelector(".atividades-digitais-container");
      if (atividadesEl) {
        const cloneAtividades = atividadesEl.cloneNode(true);
        container.appendChild(cloneAtividades);
      }
    }
    
    // Adicionar horários FLEX se existir
    if (this.horariosFlex.length > 0 && !this.isFlex) {
      const flexEl = document.querySelector(".horarios-flex-container");
      if (flexEl) {
        const cloneFlex = flexEl.cloneNode(true);
        container.appendChild(cloneFlex);
      }
    }
    
    document.body.appendChild(container);

    const canvas = await window.html2canvas(container, {
      scale: 2,
      useCORS: true,
      backgroundColor: "#ffffff",
    });
    
    document.body.removeChild(container);

    const link = document.createElement("a");
    link.download = `horario-${(this.turma?.nome || "turma")
      .replace(/\s+/g, "_")
      .toLowerCase()}.png`;
    link.href = canvas.toDataURL("image/png");
    link.click();
  },
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/icon?family=Material+Icons");

.wrap {
  min-height: 100vh;
  background: #ff6528;
  font-family: sans-serif;
}

.topbar {
  height: 76px;
  background: #0f0f0f;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 22px 0 18px;
  position: sticky;
  top: 0;
  z-index: 5;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand img {
  width: 52px;
  height: 52px;
  border-radius: 10px;
}

.titulo-site {
  display: flex;
  flex-direction: column;
  line-height: 1;
}

.titulo-site span {
  font-size: 1rem;
  color: #ddd;
}

.titulo-site strong {
  font-size: 1.25rem;
}

.titulo {
  font-size: 2.8rem;
  font-weight: 800;
  margin: 0;
}

.sair {
  display: flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  border: none;
  color: #fff;
  cursor: pointer;
  font-size: 1.05rem;
}

.sair .material-icons {
  font-size: 22px;
}

.conteudo {
  max-width: 1400px;
  margin: 22px auto;
  padding: 0 14px;
}

.busca {
  margin-bottom: 14px;
}

.search {
  width: 320px;
  height: 40px;
  border-radius: 20px;
  border: 1px solid #bbb;
  background: #fff;
  padding: 0 14px;
  font-size: 1rem;
}

.quadro {
  background: #3f3f3f;
  border-radius: 8px;
  padding: 0 0 14px 0;
  color: #fff;
}

.quadro-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #616161;
  padding: 12px 16px;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}

.curso {
  font-size: 2rem;
  font-weight: 700;
}

.btn-export {
  background: #000;
  border: none;
  color: #fff;
  font-weight: 700;
  padding: 8px 18px;
  border-radius: 10px;
  cursor: pointer;
}

/* ESTILOS PARA VISUALIZAÇÃO FLEX (LISTA PRINCIPAL) */
.quadro-flex .lista-flex {
  padding: 16px;
}

.lista-flex-header,
.lista-flex-item {
  display: grid;
  grid-template-columns: 100px 140px 2fr 100px 80px 100px 1.5fr 100px;
  gap: 12px;
  padding: 12px;
  border-bottom: 1px solid #555;
  align-items: center;
}

.lista-flex-header {
  background: #a3a3a3;
  color: #000;
  font-weight: 700;
  border-radius: 6px;
  border-bottom: none;
  margin-bottom: 8px;
}

.lista-flex-item {
  background: #fff;
  color: #000;
  border-radius: 4px;
  margin-bottom: 4px;
  border-bottom: none;
}

.lista-flex-item:hover {
  background: #f5f5f5;
}

.col-dia,
.col-horario,
.col-uc,
.col-codigo,
.col-grupo,
.col-sala,
.col-professor,
.col-classroom {
  font-size: 0.95rem;
}

.col-uc {
  font-weight: 600;
}

/* ESTILOS PARA GRID NORMAL */
.grid {
  display: grid;
  grid-template-columns: 120px repeat(5, 1fr);
  gap: 0;
  background: #f06a2a20;
}

.col-horas {
  grid-row: 1;
}

.th-dia {
  background: #a3a3a3;
  color: #000;
  text-align: center;
  font-weight: 800;
  padding: 10px 0;
  border-left: 1px solid #777;
  border-right: 1px solid #777;
}

.th-dia:first-of-type {
  border-left: none;
}

.th-hora {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #cfcfcf;
  color: #000;
  font-weight: 700;
  border-top: 1px solid #777;
  border-bottom: 1px solid #777;
}

.celula {
  min-height: 190px;
  background: #fff;
  border-left: 1px solid #777;
  border-bottom: 1px solid #777;
  display: flex;
  align-items: stretch;
  justify-content: stretch;
}

.card {
  width: 100%;
  padding: 16px;
  text-align: center;
  color: #000;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.uc {
  font-weight: 800;
}

.linha {
  font-size: 0.98rem;
}

.classroom-link-grid {
  color: #ff6528;
  text-decoration: underline;
}

.intervalo {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #bdbdbd;
  color: #000;
  font-weight: 800;
  border-bottom: 1px solid #777;
  border-left: 1px solid #777;
  border-right: 1px solid #777;
  height: 50px;
}

.placeholder {
  background: #505050;
  color: #fff;
  padding: 18px;
  border-radius: 12px;
  text-align: center;
}

/* ATIVIDADES DIGITAIS */
.atividades-digitais-container {
  margin-top: 20px;
  padding: 16px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.atividades-digitais-titulo {
  font-size: 1.1rem;
  font-weight: 700;
  color: #ff6528;
  border-left: 4px solid #ff6528;
  padding-left: 8px;
  margin-bottom: 10px;
}

.atividades-digitais-lista {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding-left: 12px;
}

.atividade-item {
  font-size: 0.95rem;
  color: #333;
}

.atividade-codigo {
  font-weight: 700;
  color: #000;
}

/* NOVO: HORÁRIOS FLEX (SEÇÃO PARA TODAS AS TURMAS) */
.horarios-flex-container {
  margin-top: 20px;
  padding: 16px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.horarios-flex-titulo {
  font-size: 1.1rem;
  font-weight: 700;
  color: #4CAF50;
  border-left: 4px solid #4CAF50;
  padding-left: 8px;
  margin-bottom: 12px;
}

.lista-flex-compacta {
  width: 100%;
}

.lista-flex-header-compacta,
.lista-flex-item-compacta {
  display: grid;
  grid-template-columns: 90px 130px 1.8fr 90px 70px 90px 1.3fr 90px;
  gap: 10px;
  padding: 10px;
  align-items: center;
  font-size: 0.88rem;
}

.lista-flex-header-compacta {
  background: #e8f5e9;
  color: #2e7d32;
  font-weight: 700;
  border-radius: 6px;
  margin-bottom: 6px;
}

.lista-flex-item-compacta {
  background: #f9f9f9;
  color: #333;
  border-radius: 4px;
  margin-bottom: 3px;
  border-bottom: 1px solid #e0e0e0;
}

.lista-flex-item-compacta:hover {
  background: #f0f0f0;
}

.col-uc-flex {
  font-weight: 600;
}

.classroom-link {
  color: #ff6528;
  text-decoration: none;
  font-weight: 600;
}

.classroom-link:hover {
  text-decoration: underline;
}

@media (max-width: 1200px) {
  .lista-flex-header,
  .lista-flex-item {
    grid-template-columns: 90px 120px 1.5fr 90px 70px 90px 1.2fr 90px;
    font-size: 0.85rem;
  }
  
  .lista-flex-header-compacta,
  .lista-flex-item-compacta {
    grid-template-columns: 80px 120px 1.5fr 80px 60px 80px 1.2fr 80px;
    font-size: 0.82rem;
  }
}

@media (max-width: 900px) {
  .curso {
    font-size: 1.6rem;
  }
  
  .grid {
    grid-template-columns: 90px repeat(5, 1fr);
  }
  
  .lista-flex-header,
  .lista-flex-item,
  .lista-flex-header-compacta,
  .lista-flex-item-compacta {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .col-dia::before,
  .col-dia-flex::before { content: "Dia: "; font-weight: 700; }
  .col-horario::before,
  .col-horario-flex::before { content: "Horário: "; font-weight: 700; }
  .col-uc::before,
  .col-uc-flex::before { content: "UC: "; font-weight: 700; }
  .col-codigo::before,
  .col-codigo-flex::before { content: "Código: "; font-weight: 700; }
  .col-grupo::before,
  .col-grupo-flex::before { content: "Grupo: "; font-weight: 700; }
  .col-sala::before,
  .col-sala-flex::before { content: "Sala: "; font-weight: 700; }
  .col-professor::before,
  .col-professor-flex::before { content: "Professor: "; font-weight: 700; }
  .col-classroom::before,
  .col-classroom-flex::before { content: "Classroom: "; font-weight: 700; }
}
</style>