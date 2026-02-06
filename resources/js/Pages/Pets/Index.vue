<template>
  <div class="pets-index">
    <div class="header">
      <h1>My Animals</h1>
      <a :href="route('pets.create')" class="btn-primary">
        + Add New Animal
      </a>
    </div>

    <div v-if="pets.length > 0" class="pets-grid">
      <div v-for="pet in pets" :key="pet.id" class="pet-card">
        <div class="pet-icon">
          {{ getAnimalIcon(pet.species) }}
        </div>
        <div class="pet-info">
          <h3>{{ pet.name }}</h3>
          <p class="species">{{ formatSpecies(pet.species) }} - {{ pet.breed }}</p>
          <div class="details">
            <span>Age: {{ pet.age }} years</span>
            <span>Weight: {{ pet.weight }} kg</span>
            <span>Gender: {{ pet.gender }}</span>
          </div>
          <div class="actions">
            <a :href="route('pets.show', pet.id)" class="btn-view">View</a>
            <a :href="route('pets.edit', pet.id)" class="btn-edit">Edit</a>
            <button @click="confirmDelete(pet)" class="btn-delete">Remove</button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="empty-state">
      <p>You haven't registered any animals yet.</p>
      <a :href="route('pets.create')" class="btn-primary">Register Your First Animal</a>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
  pets: Array,
  title: String,
});

const getAnimalIcon = (species) => {
  const icons = {
    dog: '🐕',
    cat: '🐈',
    bird: '🐦',
    rabbit: '🐰',
    hamster: '🐹',
    cow: '🐄',
    goat: '🐐',
    sheep: '🐑',
    chicken: '🐔',
    duck: '🦆',
    horse: '🐴',
    pig: '🐷',
    other: '🐾',
  };
  return icons[species] || '🐾';
};

const formatSpecies = (species) => {
  return species.charAt(0).toUpperCase() + species.slice(1);
};

const confirmDelete = (pet) => {
  if (confirm(`Are you sure you want to remove ${pet.name}?`)) {
    router.delete(route('pets.destroy', pet.id));
  }
};

const route = (name, params) => {
  const routes = {
    'pets.create': '/pets/create',
    'pets.show': (id) => `/pets/${id}`,
    'pets.edit': (id) => `/pets/${id}/edit`,
    'pets.destroy': (id) => `/pets/${id}`,
  };
  return typeof routes[name] === 'function' ? routes[name](params) : routes[name];
};
</script>

<style scoped>
.pets-index {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #1f2937;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.pets-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.pet-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: box-shadow 0.2s;
}

.pet-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.pet-icon {
  font-size: 3rem;
  line-height: 1;
}

.pet-info {
  flex: 1;
}

.pet-info h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.species {
  color: #6b7280;
  margin-bottom: 0.75rem;
}

.details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: #4b5563;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-view, .btn-edit, .btn-delete {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-view {
  background-color: #10b981;
  color: white;
}

.btn-view:hover {
  background-color: #059669;
}

.btn-edit {
  background-color: #f59e0b;
  color: white;
}

.btn-edit:hover {
  background-color: #d97706;
}

.btn-delete {
  background-color: #ef4444;
  color: white;
}

.btn-delete:hover {
  background-color: #dc2626;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border: 2px dashed #d1d5db;
  border-radius: 0.75rem;
}

.empty-state p {
  font-size: 1.125rem;
  color: #6b7280;
  margin-bottom: 1.5rem;
}
</style>
