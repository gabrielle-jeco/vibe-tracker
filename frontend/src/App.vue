<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { ref, watchEffect } from 'vue';

const router = useRouter();
const isAuthenticated = ref(false);

const checkAuth = () => {
  isAuthenticated.value = !!localStorage.getItem('token');
};

watchEffect(() => {
  // Simple check on route change or mount
  checkAuth();
});

// Listen to storage events? Or just provide a global state. 
// For vibe coding simplicity, we check localstorage content.

const logout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  isAuthenticated.value = false;
  router.push('/login');
};
</script>

<template>
  <div class="app-layout">
    <nav v-if="isAuthenticated" class="sidebar glass-panel">
      <div class="logo">
        <h2>💸 VibeTracker</h2>
      </div>
      <div class="links">
        <RouterLink to="/" class="nav-link">Dashboard</RouterLink>
        <RouterLink to="/transactions" class="nav-link">Transactions</RouterLink>
        <RouterLink to="/profile" class="nav-link">Profile</RouterLink>
        <RouterLink to="/about" class="nav-link">About</RouterLink>
      </div>
      <div class="footer">
        <button @click="logout" class="btn btn-secondary" style="width: 100%">Logout</button>
      </div>
    </nav>
    <main :class="{ 'with-sidebar': isAuthenticated }">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  position: fixed;
  left: 1rem;
  top: 1rem;
  bottom: 1rem;
  width: 250px;
  display: flex;
  flex-direction: column;
  z-index: 100;
  border-radius: 1rem;
}

.logo h2 {
  margin: 0 0 2rem 0;
  background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.links {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  flex: 1;
}

.nav-link {
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  color: #94a3b8;
}

.nav-link:hover, .nav-link.router-link-active {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

main {
  flex: 1;
  padding: 2rem;
  transition: margin-left 0.3s;
}

main.with-sidebar {
  margin-left: 270px; /* Sidebar width + margin */
}

@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-110%);
    transition: transform 0.3s;
  }
  main.with-sidebar {
    margin-left: 0;
  }
}
</style>
