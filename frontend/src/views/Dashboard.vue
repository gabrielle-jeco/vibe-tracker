<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const summary = ref({ income: 0, expense: 0, balance: 0 });
const chartData = ref({
  labels: [],
  datasets: []
});
const loaded = ref(false);

onMounted(async () => {
  try {
    const [summaryRes, transactionsRes] = await Promise.all([
      api.get('/summary'),
      api.get('/transactions')
    ]);
    
    summary.value = summaryRes.data;
    
    // Process transactions for chart (group by date)
    const transactions = transactionsRes.data; // ASC or DESC? Controller returns DESC.
    // Let's take last 7 distinct dates
    const grouped = {};
    transactions.forEach(t => {
      const date = t.transaction_date;
      if (!grouped[date]) grouped[date] = { income: 0, expense: 0 };
      if (t.type === 'income') grouped[date].income += parseFloat(t.amount);
      else grouped[date].expense += parseFloat(t.amount);
    });
    
    const labels = Object.keys(grouped).sort().slice(-7);
    const incomeData = labels.map(date => grouped[date].income);
    const expenseData = labels.map(date => grouped[date].expense);
    
    chartData.value = {
      labels,
      datasets: [
        { label: 'Income', backgroundColor: '#10b981', data: incomeData },
        { label: 'Expense', backgroundColor: '#f43f5e', data: expenseData }
      ]
    };
    
    loaded.value = true;
  } catch (err) {
    console.error("Failed to load dashboard data", err);
  }
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { labels: { color: '#cbd5e1' } } // Light text for dark mode
  },
  scales: {
    x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.1)' } },
    y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.1)' } }
  }
};
</script>

<template>
  <div class="container fade-in">
    <h1>Dashboard</h1>
    
    <div class="summary-cards">
      <div class="card glass-panel">
        <h3>Balance</h3>
        <p class="amount" :class="{ 'positive': summary.balance >= 0, 'negative': summary.balance < 0 }">
          Rp {{ Number(summary.balance).toLocaleString() }}
        </p>
      </div>
      <div class="card glass-panel">
        <h3>Income</h3>
        <p class="amount positive">Rp {{ Number(summary.income).toLocaleString() }}</p>
      </div>
      <div class="card glass-panel">
        <h3>Expense</h3>
        <p class="amount negative">Rp {{ Number(summary.expense).toLocaleString() }}</p>
      </div>
    </div>
    
    <div class="chart-container glass-panel">
      <h3>Financial Overview (Last 7 Days)</h3>
      <div v-if="loaded" style="height: 300px">
        <Bar :data="chartData" :options="chartOptions" />
      </div>
      <div v-else>Loading Chart...</div>
    </div>
  </div>
</template>

<style scoped>
.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.card h3 {
  margin-top: 0;
  color: #94a3b8;
  font-size: 0.875rem;
  text-transform: uppercase;
}

.amount {
  font-size: 2rem;
  font-weight: 700;
  margin: 0.5rem 0 0 0;
}

.positive { color: var(--primary-color); }
.negative { color: var(--accent-color); }

.chart-container {
  margin-top: 2rem;
}
</style>
