<template>
  <div class="dashboard">
    <nav class="navbar">
      <div class="nav-container">
        <div class="logo">
          <span class="logo-icon">🏥</span>
          <span class="logo-text">VetCare Platform</span>
        </div>
        <div class="nav-links">
          <span class="user-name">{{ user.name }}</span>
          <form @submit.prevent="logout" style="display: inline;">
            <button type="submit" class="btn-logout">Logout</button>
          </form>
        </div>
      </div>
    </nav>

    <div class="dashboard-content">
      <h1>Welcome, {{ user.name }}!</h1>
      <p class="role-badge">{{ formatRole(user.role) }}</p>

      <!-- Pet Owner Dashboard -->
      <div v-if="user.role === 'pet_owner'" class="dashboard-section">
        <div class="quick-actions">
          <a href="/pets" class="action-card">
            <div class="action-icon">🐾</div>
            <h3>My Animals</h3>
            <p>Manage your animals' profiles</p>
          </a>
          <a href="/doctors" class="action-card">
            <div class="action-icon">👨‍⚕️</div>
            <h3>Find Veterinarians</h3>
            <p>Search for specialists</p>
          </a>
          <a href="/appointments" class="action-card">
            <div class="action-icon">📅</div>
            <h3>Appointments</h3>
            <p>View and manage bookings</p>
          </a>
        </div>
      </div>

      <!-- Veterinarian Dashboard -->
      <div v-if="user.role === 'veterinarian'" class="dashboard-section">
        <div class="quick-actions">
          <a href="/veterinarian/schedule" class="action-card">
            <div class="action-icon">📅</div>
            <h3>My Schedule</h3>
            <p>Manage availability</p>
          </a>
          <a href="/veterinarian/appointments" class="action-card">
            <div class="action-icon">🩺</div>
            <h3>Appointments</h3>
            <p>View patient appointments</p>
          </a>
          <a href="/veterinarian/profile" class="action-card">
            <div class="action-icon">👤</div>
            <h3>My Profile</h3>
            <p>Update your information</p>
          </a>
        </div>
      </div>

      <!-- Admin Dashboard -->
      <div v-if="user.role === 'admin'" class="dashboard-section">
        <div class="quick-actions">
          <a href="/admin/dashboard" class="action-card">
            <div class="action-icon">📊</div>
            <h3>Analytics</h3>
            <p>View platform metrics</p>
          </a>
          <a href="/admin/users" class="action-card">
            <div class="action-icon">👥</div>
            <h3>Users</h3>
            <p>Manage users</p>
          </a>
          <a href="/admin/content" class="action-card">
            <div class="action-icon">📝</div>
            <h3>Content</h3>
            <p>Manage homepage content</p>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
  user: Object,
});

const formatRole = (role) => {
  const roles = {
    pet_owner: 'Animal Owner',
    veterinarian: 'Veterinarian',
    admin: 'Administrator',
  };
  return roles[role] || role;
};

const logout = () => {
  router.post('/logout');
};
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: #f9fafb;
}

.navbar {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 1rem 0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.5rem;
  font-weight: bold;
  color: #1f2937;
}

.logo-icon {
  font-size: 2rem;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.user-name {
  color: #4b5563;
  font-weight: 500;
}

.btn-logout {
  background-color: #ef4444;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  border: none;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-logout:hover {
  background-color: #dc2626;
}

.dashboard-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

h1 {
  font-size: 2.5rem;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.role-badge {
  display: inline-block;
  background-color: #eff6ff;
  color: #3b82f6;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  margin-bottom: 3rem;
}

.dashboard-section {
  margin-top: 2rem;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.action-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 2rem;
  text-decoration: none;
  transition: all 0.2s;
  display: block;
}

.action-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border-color: #3b82f6;
}

.action-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.action-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.action-card p {
  color: #6b7280;
  font-size: 0.875rem;
}
</style>
