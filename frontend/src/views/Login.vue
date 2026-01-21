<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const username = ref('');
const password = ref('');
const error = ref('');
const router = useRouter();

const handleLogin = async () => {
  try {
    const response = await api.post('/login', {
      username: username.value,
      password: password.value
    });
    
    // Store token
    localStorage.setItem('token', response.data.token);
    localStorage.setItem('user', JSON.stringify(response.data.user)); // Optional
    
    // Trigger auth update (naive way for now, usually use a store)
    // Or valid since App.vue watches localStorage effect if using a wrapper (wait, App.vue only watches on component lifecycle)
    // Let's force reload or use a simple event bus, or just trust the router push triggers app update? 
    // Actually App.vue code was `watchEffect(checkAuth)`. If checkAuth is triggered. 
    // Best to reload or specific state management. For vibe:
    router.push('/').then(() => {
      window.location.reload(); 
    });
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed';
  }
};
</script>

<template>
  <div class="auth-container">
    <div class="glass-panel auth-box fade-in">
      <h1 class="title">Welcome Back</h1>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>Username</label>
          <input v-model="username" type="text" class="input-field" placeholder="Enter username" required />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="password" type="password" class="input-field" placeholder="Enter password" required />
        </div>
        <div v-if="error" class="error">{{ error }}</div>
        <button type="submit" class="btn btn-primary" style="width: 100%">Login</button>
      </form>
      <p class="switch-auth">
        Don't have an account? <RouterLink to="/register">Register</RouterLink>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
}

.auth-box {
  width: 100%;
  max-width: 400px;
}

.title {
  tex-align: center;
  margin-bottom: 2rem;
  background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-size: 2rem;
  font-weight: 800;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  color: #cbd5e1;
}

.error {
  color: var(--accent-color);
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.switch-auth {
  margin-top: 1.5rem;
  text-align: center;
  font-size: 0.875rem;
  color: #94a3b8;
}

.switch-auth a {
  color: var(--primary-color);
  font-weight: 600;
}
</style>
