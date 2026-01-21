<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const user = ref({});
const router = useRouter();

onMounted(() => {
  const userData = localStorage.getItem('user');
  if (userData) {
    user.value = JSON.parse(userData);
  }
});

const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/login';
};
</script>

<template>
  <div class="container fade-in">
    <div class="glass-panel">
      <h1>My Profile</h1>
      <div class="profile-details">
        <div class="detail-item">
          <span class="label">Username</span>
          <span class="value">{{ user.username }}</span>
        </div>
        <div class="detail-item">
            <span class="label">ID</span>
            <span class="value">{{ user.id }}</span>
        </div>
      </div>
      <button @click="logout" class="btn btn-secondary" style="margin-top: 2rem;">Log Out</button>
    </div>
  </div>
</template>

<style scoped>
.profile-details {
  margin-top: 2rem;
}
.detail-item {
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  border-bottom: 1px solid var(--glass-border);
}
.label {
    color: #94a3b8;
}
.value {
    font-weight: 600;
}
</style>
