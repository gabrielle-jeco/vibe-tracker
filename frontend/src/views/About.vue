<template>
  <div class="container fade-in">
    <div class="glass-panel">
      <h1>About VibeTracker</h1>
      <p>
        VibeTracker is a modern financial tracking application built with the "Vibe Coding" philosophy.
        It combines a lightweight Native PHP backend with a dynamic Vue.js frontend.
      </p>
      
      <h2>Tech Stack</h2>
      <ul class="stack-list">
        <li><strong>Frontend:</strong> Vue.js 3, Vite, Glassmorphism CSS</li>
        <li><strong>Backend:</strong> Native PHP 8.2 (REST API)</li>
        <li><strong>Database:</strong> MySQL 8.0</li>
        <li><strong>Monitoring:</strong> Prometheus & Grafana</li>
        <li><strong>Infrastructure:</strong> Docker & Docker Compose</li>
      </ul>
      
      <h2>Monitoring Targets</h2>
      <p>We monitor p95 latency, error rates, and resource saturation to ensure the vibe never dies.</p>
    </div>
  </div>
</template>

<style scoped>
.stack-list {
  list-style: none;
  padding: 0;
}
.stack-list li {
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--glass-border);
}
</style>
