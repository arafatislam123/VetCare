<template>
  <div class="doctor-profile">
    <div class="doctor-header">
      <img v-if="veterinarian.profile_image" :src="veterinarian.profile_image" :alt="veterinarian.user.name" class="profile-image" />
      <div class="doctor-info">
        <h1>{{ veterinarian.user.name }}</h1>
        <p class="license">License: {{ veterinarian.license_number }}</p>
        <p class="experience">{{ veterinarian.experience_years }} years of experience</p>
        <p class="rating">Rating: {{ averageRating.toFixed(1) }} / 5.0</p>
      </div>
    </div>

    <div class="specializations">
      <h2>Specializations</h2>
      <div class="specialization-list">
        <span v-for="spec in veterinarian.specializations" :key="spec.id" class="badge">
          {{ spec.name }}
        </span>
      </div>
    </div>

    <div class="bio">
      <h2>About</h2>
      <p>{{ veterinarian.bio }}</p>
    </div>

    <div class="consultation-fee">
      <h2>Consultation Fee</h2>
      <p class="fee">{{ veterinarian.consultation_fee }} TK</p>
    </div>

    <div class="available-slots">
      <h2>Available Time Slots (Next 30 Days)</h2>
      <div v-if="availableSlots.length > 0" class="slots-grid">
        <div v-for="slot in availableSlots" :key="slot.id" class="slot-card">
          <p class="slot-date">{{ formatDate(slot.start_time) }}</p>
          <p class="slot-time">{{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}</p>
        </div>
      </div>
      <p v-else class="no-slots">No available slots at this time.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  veterinarian: Object,
  averageRating: Number,
  availableSlots: Array,
});

const availableSlots = ref(props.availableSlots);
let echoChannel = null;

onMounted(() => {
  // Listen for real-time availability updates
  if (window.Echo) {
    echoChannel = window.Echo.channel(`doctor-availability.${props.veterinarian.id}`)
      .listen('.availability.updated', (event) => {
        console.log('Availability updated:', event);
        availableSlots.value = event.available_slots;
      });
  }
});

onUnmounted(() => {
  // Clean up the channel subscription
  if (echoChannel) {
    echoChannel.stopListening('.availability.updated');
    window.Echo.leave(`doctor-availability.${props.veterinarian.id}`);
  }
});

const formatDate = (datetime) => {
  return new Date(datetime).toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatTime = (datetime) => {
  return new Date(datetime).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<style scoped>
.doctor-profile {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.doctor-header {
  display: flex;
  gap: 2rem;
  margin-bottom: 2rem;
}

.profile-image {
  width: 200px;
  height: 200px;
  border-radius: 50%;
  object-fit: cover;
}

.doctor-info h1 {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.specializations, .bio, .consultation-fee, .available-slots {
  margin-bottom: 2rem;
}

.specialization-list {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.badge {
  background-color: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.fee {
  font-size: 1.5rem;
  font-weight: bold;
  color: #10b981;
}

.slots-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.slot-card {
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  background-color: #f9fafb;
}

.slot-date {
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.slot-time {
  color: #6b7280;
}

.no-slots {
  color: #6b7280;
  font-style: italic;
}
</style>
