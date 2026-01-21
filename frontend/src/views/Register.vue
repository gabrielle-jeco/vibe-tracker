<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const username = ref('');
const password = ref('');
const error = ref('');
const router = useRouter();

const handleRegister = async () => {
  try {
    await api.post('/register', {
      username: username.value,
      password: password.value
    });
    router.push('/login');
  } catch (err) {
    error.value = err.response?.data?.message || 'Registration failed';
  }
};
</script>

<template>
  <div class="auth-container">
    <div class="glass-panel auth-box fade-in">
      <h1 class="title">Join VibeTracker</h1>
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label>Username</label>
          <input v-model="username" type="text" class="input-field" placeholder="Choose a username" required />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="password" type="password" class="input-field" placeholder="Choose a password" required />
        </div>
        <div v-if="error" class="error">{{ error }}</div>
        <button type="submit" class="btn btn-primary" style="width: 100%">Sign Up</button>
      </form>
      <p class="switch-auth">
        Already have an account? <RouterLink to="/login">Login</RouterLink>
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
  text-align: center;
  margin-bottom: 2rem;
  background: linear-gradient(to right, var(--secondary-color), var(--accent-color));
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
