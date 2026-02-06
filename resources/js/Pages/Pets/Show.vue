<template>
  <div class="pet-show">
    <div class="header">
      <a href="/pets" class="back-link">← Back to Animals</a>
      <h1>{{ pet.name }}</h1>
    </div>

    <div class="pet-details-card">
      <div class="pet-header">
        <div class="pet-icon-large">
          {{ getAnimalIcon(pet.species) }}
        </div>
        <div class="pet-basic-info">
          <h2>{{ pet.name }}</h2>
          <p class="species">{{ formatSpecies(pet.species) }} - {{ pet.breed }}</p>
          <div class="badges">
            <span class="badge">{{ pet.gender }}</span>
            <span class="badge">{{ pet.age }} years old</span>
            <span class="badge">{{ pet.weight }} kg</span>
          </div>
        </div>
        <div class="actions">
          <a :href="`/pets/${pet.id}/edit`" class="btn-edit">Edit Details</a>
        </div>
      </div>

      <div class="pet-section">
        <h3>Medical Notes</h3>
        <p v-if="pet.medical_notes" class="medical-notes">{{ pet.medical_notes }}</p>
        <p v-else class="no-data">No medical notes recorded</p>
      </div>

      <div class="pet-section">
        <h3>Appointment History</h3>
        <div v-if="pet.appointments && pet.appointments.length > 0" class="appointments-list">
          <div v-for="appointment in pet.appointments" :key="appointment.id" class="appointment-card">
            <div class="appointment-info">
              <p class="appointment-date">{{ formatDate(appointment.scheduled_at) }}</p>
              <p class="appointment-vet">Dr. {{ appointment.veterinarian.user.name }}</p>
              <span :class="['status-badge', appointment.status]">{{ appointment.status }}</span>
            </div>
          </div>
        </div>
        <p v-else class="no-data">No appointments yet</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  pet: Object,
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

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};
</script>

<style scoped>
.pet-show {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem;
}

.header {
  margin-bottom: 2rem;
}

.back-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
  margin-bottom: 1rem;
  display: inline-block;
}

.back-link:hover {
  text-decoration: underline;
}

h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #1f2937;
}

.pet-details-card {
  background: white;
  border-radius: 0.75rem;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.pet-header {
  display: flex;
  gap: 2rem;
  align-items: flex-start;
  padding-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 2rem;
}

.pet-icon-large {
  font-size: 5rem;
  line-height: 1;
}

.pet-basic-info {
  flex: 1;
}

.pet-basic-info h2 {
  font-size: 1.875rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.species {
  font-size: 1.125rem;
  color: #6b7280;
  margin-bottom: 1rem;
}

.badges {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.badge {
  background-color: #eff6ff;
  color: #3b82f6;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-edit {
  background-color: #f59e0b;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.2s;
}

.btn-edit:hover {
  background-color: #d97706;
}

.pet-section {
  margin-bottom: 2rem;
}

.pet-section:last-child {
  margin-bottom: 0;
}

.pet-section h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.medical-notes {
  color: #4b5563;
  line-height: 1.6;
  white-space: pre-wrap;
}

.no-data {
  color: #9ca3af;
  font-style: italic;
}

.appointments-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.appointment-card {
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  background: #f9fafb;
}

.appointment-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.appointment-date {
  font-weight: 600;
  color: #1f2937;
}

.appointment-vet {
  color: #6b7280;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge.pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.confirmed {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-badge.completed {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.cancelled {
  background-color: #fee2e2;
  color: #991b1b;
}
</style>
