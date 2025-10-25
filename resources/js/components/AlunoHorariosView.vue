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

      <section ref="gridArea" class="quadro" v-if="turma && gradePronta">
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
                    <a :href="aula(d, b.inicio).classroom" target="_blank" style="color: #FF6528; text-decoration: underline;">Classroom</a>
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
      <!-- FIM NOVO -->

      <div v-else class="placeholder">Pesquise e selecione uma turma publicada.</div>
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
      atividadesDigitais: [], // Adicionado para armazenar as atividades digitais
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
      // Modificado para considerar grade pronta mesmo sem horários, se houver atividades digitais
      return this.turma && (this.horarios.length > 0 || this.atividadesDigitais.length > 0);
    },
  },
  mounted() {
    this.carregarTurmas();
  },
  methods: {
    // Carrega as turmas publicadas
    async carregarTurmas() {
      try {
        const { data } = await axios.get("/api/public/turmas-publicadas");
        this.turmas = data;
      } catch {
        Swal.fire("Erro", "Falha ao carregar turmas publicadas.", "error");
      }
    },

    // Realiza a busca pela turma selecionada
    onType() {
      const nome = (this.termo || "").trim().toLowerCase();
      const t = this.turmas.find((x) => x.nome.toLowerCase() === nome);
      if (t && (!this.turma || t.id !== this.turma.id)) this.selecionarTurma(t);
    },

    // Seleciona a turma e carrega seus horários
    async selecionarTurma(t) {
      try {
        const { data } = await axios.get(`/api/public/horarios/${t.id}`);
        this.turma = data.turma;
        this.horarios = data.horarios;
        this.atividadesDigitais = data.atividades_digitais || []; // Captura as atividades digitais
      } catch (e) {
        Swal.fire(
          "Erro",
          e.response?.data?.message || "Não foi possível carregar a grade.",
          "error"
        );
      }
    },

    // Função para encontrar a aula na célula
    aula(dia, inicio) {
      // Normaliza o horário: se vier como "19:00:00", converte para "19:00"
      const normalizarHora = (hora) => {
        if (!hora) return null;
        return hora.substring(0, 5); // Pega apenas "HH:mm"
      };

      return this.horarios.find(
        (h) =>
          h.dia_semana.toLowerCase() === dia.toLowerCase() &&
          normalizarHora(h.hora_inicio) === inicio
      ) || null;
    },

    // Função para exportar o horário em formato PNG
    async exportar() {
      const el = this.$refs.gridArea;
      if (!el) return;
      // carrega html2canvas por CDN quando precisar
      if (!window.html2canvas) {
        await new Promise((ok, err) => {
          const s = document.createElement("script");
          s.src =
            "https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js";
          s.onload = ok;
          s.onerror = err;
          document.head.appendChild(s );
        }).catch(() => {});
      }
      if (!window.html2canvas) {
        window.print();
        return;
      }
      const canvas = await window.html2canvas(el, {
        scale: 2,
        useCORS: true,
        backgroundColor: null,
      });
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
@import url("https://fonts.googleapis.com/icon?family=Material+Icons" );

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
  max-width: 1050px;
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
@media (max-width: 900px) {
  .curso {
    font-size: 1.6rem;
  }
  .grid {
    grid-template-columns: 90px repeat(5, 1fr);
  }
}

/* NOVOS ESTILOS PARA ATIVIDADES DIGITAIS */
.atividades-digitais-container {
  margin-top: 20px;
  padding: 16px;
  background: #fff; /* Fundo branco para contrastar com o laranja/cinza */
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.atividades-digitais-titulo {
  font-size: 1.1rem;
  font-weight: 700;
  color: #ff6528; /* Cor laranja para destaque */
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
/* FIM NOVOS ESTILOS */
</style>
