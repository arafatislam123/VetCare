<template>
  <div class="doctors-search">
    <h1>Search Veterinarians</h1>

    <div class="search-form">
      <input 
        v-model="searchQuery" 
        type="text" 
        placeholder="Search by name or keywords..."
        class="search-input"
        @keyup.enter="performSearch"
      />
      
      <select v-model="selectedSpecialization" class="filter-select">
        <option value="">All Specializations</option>
        <option v-for="spec in specializations" :key="spec.id" :value="spec.id">
          {{ spec.name }}
        </option>
      </select>

      <button @click="performSearch" class="search-btn">Search</button>
    </div>

    <div v-if="veterinarians.data.length > 0" class="doctors-grid">
      <div v-for="vet in veterinarians.data" :key="vet.id" class="doctor-card">
        <img v-if="vet.profile_image" :src="vet.profile_image" :alt="vet.user.name" class="card-image" />
        <div class="card-content">
          <h2>{{ vet.user.name }}</h2>
          <p class="experience">{{ vet.experience_years }} years experience</p>
          <div class="specializations">
            <span v-for="spec in vet.specializations" :key="spec.id" class="badge">
              {{ spec.name }}
            </span>
          </div>
          <p class="fee">{{ vet.consultation_fee }} TK</p>
          <a :href="`/doctors/${vet.id}`" class="view-profile-btn">View Profile</a>
        </div>
      </div>
    </div>
    <p v-else class="no-results">No veterinarians found matching your search.</p>

    <div v-if="veterinarians.links" class="pagination">
      <a v-for="link in veterinarians.links" :key="link.label" 
         :href="link.url" 
         :class="{ active: link.active }"
         v-html="link.label">
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  veterinarians: Object,
  specializations: Array,
  filters: Object,
});

const searchQuery = ref(props.filters.query || '');
const selectedSpecialization = ref(props.filters.specialization || '');

const performSearch = () => {
  router.get('/doctors/search', {
    query: searchQuery.value || undefined,
    specialization: selectedSpecialization.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

<style scoped>
.doctors-search {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

h1 {
  font-size: 2rem;
  margin-bottom: 2rem;
}

.search-form {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.search-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
}

.search-btn {
  padding: 0.75rem 2rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.search-btn:hover {
  background-color: #2563eb;
}

.doctors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.doctor-card {
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  overflow: hidden;
  background-color: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.card-content {
  padding: 1.5rem;
}

.card-content h2 {
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
}

.experience {
  color: #6b7280;
  margin-bottom: 1rem;
}

.specializations {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.badge {
  background-color: #3b82f6;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
}

.fee {
  font-size: 1.25rem;
  font-weight: bold;
  color: #10b981;
  margin-bottom: 1rem;
}

.view-profile-btn {
  display: inline-block;
  background-color: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  text-decoration: none;
  transition: background-color 0.2s;
}

.view-profile-btn:hover {
  background-color: #2563eb;
}

.no-results {
  text-align: center;
  color: #6b7280;
  font-size: 1.125rem;
  padding: 2rem;
}

.pagination {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.pagination a {
  padding: 0.5rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
  text-decoration: none;
  color: #374151;
}

.pagination a.active {
  background-color: #3b82f6;
  color: white;
  border-color: #3b82f6;
}
</style>
