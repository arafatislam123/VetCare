<template>
  <div class="pet-create">
    <div class="form-container">
      <h1>Register New Animal</h1>
      
      <form @submit.prevent="submit">
        <div class="form-group">
          <label for="name">Animal Name *</label>
          <input 
            id="name"
            v-model="form.name" 
            type="text" 
            required
            placeholder="e.g., Bella, Daisy, Max"
          />
          <span v-if="errors.name" class="error">{{ errors.name }}</span>
        </div>

        <div class="form-group">
          <label for="species">Species *</label>
          <select id="species" v-model="form.species" required>
            <option value="">Select species</option>
            <optgroup label="Pets">
              <option value="dog">Dog 🐕</option>
              <option value="cat">Cat 🐈</option>
              <option value="bird">Bird 🐦</option>
              <option value="rabbit">Rabbit 🐰</option>
              <option value="hamster">Hamster 🐹</option>
            </optgroup>
            <optgroup label="Livestock">
              <option value="cow">Cow 🐄</option>
              <option value="goat">Goat 🐐</option>
              <option value="sheep">Sheep 🐑</option>
              <option value="chicken">Chicken 🐔</option>
              <option value="duck">Duck 🦆</option>
              <option value="horse">Horse 🐴</option>
              <option value="pig">Pig 🐷</option>
            </optgroup>
            <option value="other">Other 🐾</option>
          </select>
          <span v-if="errors.species" class="error">{{ errors.species }}</span>
        </div>

        <div class="form-group">
          <label for="breed">Breed *</label>
          <input 
            id="breed"
            v-model="form.breed" 
            type="text" 
            required
            placeholder="e.g., Holstein, Labrador, Persian"
          />
          <span v-if="errors.breed" class="error">{{ errors.breed }}</span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="age">Age (years) *</label>
            <input 
              id="age"
              v-model="form.age" 
              type="number" 
              min="0"
              required
              placeholder="0"
            />
            <span v-if="errors.age" class="error">{{ errors.age }}</span>
          </div>

          <div class="form-group">
            <label for="weight">Weight (kg) *</label>
            <input 
              id="weight"
              v-model="form.weight" 
              type="number" 
              step="0.1"
              min="0"
              required
              placeholder="0.0"
            />
            <span v-if="errors.weight" class="error">{{ errors.weight }}</span>
          </div>

          <div class="form-group">
            <label for="gender">Gender *</label>
            <select id="gender" v-model="form.gender" required>
              <option value="">Select</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
            </select>
            <span v-if="errors.gender" class="error">{{ errors.gender }}</span>
          </div>
        </div>

        <div class="form-group">
          <label for="medical_notes">Medical Notes</label>
          <textarea 
            id="medical_notes"
            v-model="form.medical_notes" 
            rows="4"
            placeholder="Any medical history, allergies, or special conditions..."
          ></textarea>
          <span v-if="errors.medical_notes" class="error">{{ errors.medical_notes }}</span>
        </div>

        <div class="form-actions">
          <a href="/pets" class="btn-cancel">Cancel</a>
          <button type="submit" class="btn-submit" :disabled="processing">
            {{ processing ? 'Registering...' : 'Register Animal' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  errors: {
    type: Object,
    default: () => ({}),
  },
});

const form = ref({
  name: '',
  species: '',
  breed: '',
  age: '',
  weight: '',
  gender: '',
  medical_notes: '',
});

const processing = ref(false);

const submit = () => {
  processing.value = true;
  router.post('/pets', form.value, {
    onFinish: () => {
      processing.value = false;
    },
  });
};
</script>

<style scoped>
.pet-create {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
}

.form-container {
  background: white;
  border-radius: 0.75rem;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

h1 {
  font-size: 1.875rem;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

label {
  display: block;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

input, select, textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: border-color 0.2s;
}

input:focus, select:focus, textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.error {
  display: block;
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn-cancel, .btn-submit {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel {
  background-color: #f3f4f6;
  color: #374151;
}

.btn-cancel:hover {
  background-color: #e5e7eb;
}

.btn-submit {
  background-color: #3b82f6;
  color: white;
}

.btn-submit:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
