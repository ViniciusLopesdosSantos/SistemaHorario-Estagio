<template>
  <div class="login-page">
    <div class="login-card">
      <img src="@/assets/computacao.jpg" alt="Logo" class="login-logo" />
      <h1>Login</h1>

      <form @submit.prevent="doLogin">
        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-icon">
            <span class="material-icons">email</span>
            <input id="email" v-model="form.email" type="email" placeholder="exemplo@unifil.br" required />
          </div>
        </div>

        <div class="form-group">
          <label for="senha">Senha</label>
          <div class="input-icon">
            <span class="material-icons">vpn_key</span>
            <input
              id="senha"
              v-model="form.senha"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
            />
            <span class="material-icons eye" @click="showPassword = !showPassword">
              {{ showPassword ? 'visibility_off' : 'visibility' }}
            </span>
          </div>
        </div>

        <div class="options">
          <label><input type="checkbox" v-model="form.remember" /> Lembre de mim</label>
        </div>

        <button type="submit" class="btn-login">Entrar</button>
      </form>

      <p v-if="error" class="error">{{ error }}</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      form: { email: '', senha: '', remember: false },
      showPassword: false,
      error: null,
    }
  },
  mounted() {
    const remembered = localStorage.getItem('remember_login') === '1'
    const rememberedEmail = localStorage.getItem('remember_email') || ''
    if (remembered) {
      this.form.remember = true
      this.form.email = rememberedEmail
    }
  },
  methods: {
 async doLogin() {
  this.error = null
  try {
    const res = await axios.post('/api/login', {
      email: this.form.email,
      senha: this.form.senha, 
      remember: this.form.remember,
    });

    // Armazena o token e o usuário no localStorage
    localStorage.setItem('api_token', res.data.token);
    localStorage.setItem('user', JSON.stringify(res.data.professor));
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + res.data.token;

    // Lógica para lembrar o login
    if (this.form.remember) {
      localStorage.setItem('remember_login', '1');
      localStorage.setItem('remember_email', this.form.email);
    } else {
      localStorage.removeItem('remember_login');
      localStorage.removeItem('remember_email');
    }

    this.$router.push('/salas');
  } catch (err) {
    this.error = err.response?.data?.message || 'Erro ao efetuar login.';
  }
}

  }
}
</script>

<style scoped>
/* Área laranja de fundo */
.login-page{
  background:#FF6528;
  min-height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
}

/* Card escuro grande, arredondado, como no protótipo */
.login-card{
  background:#000000;            
  color:#fff;
  width:100%;
  max-width:720px;                
  padding:3.5rem 3rem;
  border-radius:24px;
  text-align:left;                
  box-shadow:0 10px 30px rgba(0,0,0,.25);
  position:relative;
}

.login-logo{
  width:120px;
  display:block;
  margin:0 auto 8px;
}

h1{
  text-align:center;
  font-size:40px;
  line-height:1.1;
  margin:6px 0 28px;
  font-weight:700;
}

/* Blocos dos campos */
.form-group{
  margin:22px 0;
}

.form-group > label{
  display:block;
  font-size:15px;
  color:#ddd;
  margin:0 0 10px;
}

.input-icon{
  position:relative;
}

.input-icon input{
  box-sizing:border-box;
  width:100%;
  height:54px;
  background:#fff;
  color:#111;
  border:none;
  border-radius:12px;
  padding:0 56px;                 
  padding-left:52px;              
  font-size:15px;
  outline:none;
}
.input-icon input:focus{
  box-shadow:0 0 0 3px rgba(255,101,40,.35);
}

.input-icon .material-icons{
  position:absolute;
  left:16px;
  top:50%;
  transform:translateY(-50%);
  font-size:22px;
  color:#6f6f6f;
  pointer-events:none;
}

.input-icon .eye{
  position:absolute;
  top:50%;
  transform:translateY(-50%);
  font-size:6px;
  color:#8a8a8a;
  cursor:pointer;
  user-select:none;
  pointer-events:auto;
}

.options{
  display:flex;
  align-items:center;
  justify-content:space-between;
  margin:10px 0 22px;
}
.options label{
  color:#dcdcdc;
  font-size:15px;
}

.btn-login{
  width:100%;
  height:52px;
  border:none;
  border-radius:12px;
  background:#FF6F00;
  color:#fff;
  font-size:20px;
  font-weight:700;
  cursor:pointer;
  transition:filter .15s ease, transform .02s ease;
}
.btn-login:hover{ filter:brightness(1.05); }
.btn-login:active{ transform:translateY(1px); }

.error{
  color:#ff8b8b;
  margin-top:16px;
  text-align:center;
  font-size:14px;
}
</style>
