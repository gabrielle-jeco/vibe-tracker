<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../api';

const transactions = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const form = ref({
  id: null,
  transaction_date: new Date().toISOString().split('T')[0],
  type: 'expense',
  category: '',
  amount: 0,
  description: ''
});

const loadTransactions = async () => {
  try {
    const res = await api.get('/transactions');
    transactions.value = res.data;
  } catch (err) {
    console.error(err);
  }
};

onMounted(loadTransactions);

const openAddModal = () => {
  isEditing.value = false;
  form.value = {
    id: null,
    transaction_date: new Date().toISOString().split('T')[0],
    type: 'expense',
    category: '',
    amount: 0,
    description: ''
  };
  showModal.value = true;
};

const openEditModal = (transaction) => {
  isEditing.value = true;
  form.value = { ...transaction };
  showModal.value = true;
};

const deleteTransaction = async (id) => {
  if (!confirm('Are you sure?')) return;
  try {
    await api.delete(`/transactions/${id}`);
    loadTransactions();
  } catch (err) {
      alert('Failed to delete');
  }
};

const submitForm = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/transactions/${form.value.id}`, form.value);
    } else {
      await api.post('/transactions', form.value);
    }
    showModal.value = false;
    loadTransactions();
  } catch (err) {
    alert('Operation failed');
  }
};
</script>

<template>
  <div class="container fade-in">
    <div class="header-action">
      <h1>Transactions</h1>
      <button @click="openAddModal" class="btn btn-primary">+ Add New</button>
    </div>
    
    <div class="glass-panel">
      <table v-if="transactions.length > 0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Category</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in transactions" :key="t.id">
            <td>{{ t.transaction_date }}</td>
            <td>
              <span :class="['badge', t.type]">{{ t.type }}</span>
            </td>
            <td>{{ t.category }}</td>
            <td>{{ t.description }}</td>
            <td :class="t.type === 'income' ? 'positive' : 'negative'">
              Rp {{ Number(t.amount).toLocaleString() }}
            </td>
            <td>
              <button @click="openEditModal(t)" class="btn-icon">✏️</button>
              <button @click="deleteTransaction(t.id)" class="btn-icon">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else style="text-align:center; color: #94a3b8; padding: 2rem;">No transactions found.</p>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="glass-panel modal-content fade-in">
        <h2>{{ isEditing ? 'Edit Transaction' : 'New Transaction' }}</h2>
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label>Date</label>
            <input v-model="form.transaction_date" type="date" class="input-field" required />
          </div>
          <div class="form-group">
            <label>Type</label>
            <select v-model="form.type" class="input-field">
              <option value="expense">Expense</option>
              <option value="income">Income</option>
            </select>
          </div>
          <div class="form-group">
            <label>Category</label>
            <input v-model="form.category" type="text" class="input-field" placeholder="e.g. Food, Salary" required />
          </div>
          <div class="form-group">
            <label>Amount</label>
            <input v-model="form.amount" type="number" class="input-field" required />
          </div>
          <div class="form-group">
            <label>Description</label>
            <input v-model="form.description" type="text" class="input-field" placeholder="Optional details" />
          </div>
          <div class="modal-actions">
            <button type="button" @click="showModal = false" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.header-action {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}
.badge.income { background: rgba(16, 185, 129, 0.2); color: #10b981; }
.badge.expense { background: rgba(244, 63, 94, 0.2); color: #f43f5e; }

.positive { color: #10b981; }
.negative { color: #f43f5e; }

.btn-icon {
  background: none;
  font-size: 1.2rem;
  margin-right: 0.5rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(5px);
}

.modal-content {
  width: 100%;
  max-width: 500px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
}
select.input-field {
    background: rgba(15, 23, 42, 0.6); /* Fix browser style for select */
}
option {
    background: #0f172a;
}
</style>
