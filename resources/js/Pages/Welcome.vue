<template>
  <div class="welcome-page">
    <nav class="navbar">
      <div class="nav-container">
        <div class="logo">
          <span class="logo-icon">🏥</span>
          <span class="logo-text">VetCare Platform</span>
        </div>
        <div class="nav-links">
          <a href="/doctors" class="nav-link">Find Veterinarians</a>
          <a href="/login" class="nav-link">Login</a>
          <a href="/register" class="btn-register">Get Started</a>
        </div>
      </div>
    </nav>

    <section class="hero">
      <div class="hero-content">
        <h1>Professional Veterinary Care for All Your Animals</h1>
        <p class="hero-subtitle">
          From pets to livestock - Connect with experienced veterinarians for dogs, cats, cows, goats, chickens, and more
        </p>
        <div class="hero-actions">
          <a href="/register" class="btn-primary-large">Book a Consultation</a>
          <a href="/doctors" class="btn-secondary-large">Browse Veterinarians</a>
        </div>
      </div>
      <div class="hero-image">
        <div class="animal-icons">
          <span class="animal-icon">🐕</span>
          <span class="animal-icon">🐈</span>
          <span class="animal-icon">🐄</span>
          <span class="animal-icon">🐐</span>
          <span class="animal-icon">🐔</span>
          <span class="animal-icon">🐴</span>
          <span class="animal-icon">🐑</span>
          <span class="animal-icon">🐷</span>
        </div>
      </div>
    </section>

    <section class="features">
      <div class="features-container">
        <h2>Why Choose VetCare Platform?</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">🔍</div>
            <h3>Find Specialists</h3>
            <p>Search for veterinarians by specialization - from small animals to large livestock</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">📅</div>
            <h3>Easy Booking</h3>
            <p>Book appointments online and manage your animal's health records in one place</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">💊</div>
            <h3>Digital Prescriptions</h3>
            <p>Receive and download prescriptions digitally after consultations</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">💳</div>
            <h3>Secure Payments</h3>
            <p>Pay safely with bKash or Nagad - trusted local payment methods</p>
          </div>
        </div>
      </div>
    </section>

    <section class="animals-supported">
      <div class="animals-container">
        <h2>We Care for All Animals</h2>
        <div class="animals-grid">
          <div class="animal-category">
            <h3>Companion Animals</h3>
            <div class="animal-list">
              <span>🐕 Dogs</span>
              <span>🐈 Cats</span>
              <span>🐦 Birds</span>
              <span>🐰 Rabbits</span>
              <span>🐹 Small Pets</span>
            </div>
          </div>
          <div class="animal-category">
            <h3>Livestock & Farm Animals</h3>
            <div class="animal-list">
              <span>🐄 Cattle</span>
              <span>🐐 Goats</span>
              <span>🐑 Sheep</span>
              <span>🐔 Poultry</span>
              <span>🐴 Horses</span>
              <span>🐷 Pigs</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="cta">
      <div class="cta-container">
        <h2>Ready to Get Started?</h2>
        <p>Join thousands of animal owners who trust VetCare Platform</p>
        <a href="/register" class="btn-cta">Create Free Account</a>
      </div>
    </section>

    <footer class="footer">
      <div class="footer-container">
        <p>&copy; 2026 VetCare Platform. Professional veterinary care for all animals.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
// No props needed for static welcome page
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.welcome-page {
  min-height: 100vh;
  background: linear-gradient(to bottom, #f9fafb, #ffffff);
}

/* Navbar */
.navbar {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 1rem 0;
  position: sticky;
  top: 0;
  z-index: 100;
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
  gap: 2rem;
  align-items: center;
}

.nav-link {
  color: #4b5563;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.nav-link:hover {
  color: #3b82f6;
}

.btn-register {
  background-color: #3b82f6;
  color: white;
  padding: 0.5rem 1.5rem;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.2s;
}

.btn-register:hover {
  background-color: #2563eb;
}

/* Hero Section */
.hero {
  max-width: 1200px;
  margin: 0 auto;
  padding: 4rem 2rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: center;
}

.hero-content h1 {
  font-size: 3rem;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 1.25rem;
  color: #6b7280;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.hero-actions {
  display: flex;
  gap: 1rem;
}

.btn-primary-large, .btn-secondary-large {
  padding: 1rem 2rem;
  border-radius: 0.5rem;
  font-size: 1.125rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-primary-large {
  background-color: #3b82f6;
  color: white;
}

.btn-primary-large:hover {
  background-color: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

.btn-secondary-large {
  background-color: white;
  color: #3b82f6;
  border: 2px solid #3b82f6;
}

.btn-secondary-large:hover {
  background-color: #eff6ff;
}

.hero-image {
  display: flex;
  justify-content: center;
  align-items: center;
}

.animal-icons {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.animal-icon {
  font-size: 4rem;
  animation: float 3s ease-in-out infinite;
}

.animal-icon:nth-child(2) { animation-delay: 0.2s; }
.animal-icon:nth-child(3) { animation-delay: 0.4s; }
.animal-icon:nth-child(4) { animation-delay: 0.6s; }
.animal-icon:nth-child(5) { animation-delay: 0.8s; }
.animal-icon:nth-child(6) { animation-delay: 1s; }
.animal-icon:nth-child(7) { animation-delay: 1.2s; }
.animal-icon:nth-child(8) { animation-delay: 1.4s; }

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

/* Features Section */
.features {
  background: white;
  padding: 4rem 2rem;
}

.features-container {
  max-width: 1200px;
  margin: 0 auto;
}

.features-container h2 {
  font-size: 2.5rem;
  font-weight: bold;
  text-align: center;
  color: #1f2937;
  margin-bottom: 3rem;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.feature-card {
  text-align: center;
  padding: 2rem;
  border-radius: 0.75rem;
  background: #f9fafb;
  transition: transform 0.2s;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.75rem;
}

.feature-card p {
  color: #6b7280;
  line-height: 1.6;
}

/* Animals Supported Section */
.animals-supported {
  padding: 4rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.animals-container {
  max-width: 1200px;
  margin: 0 auto;
}

.animals-container h2 {
  font-size: 2.5rem;
  font-weight: bold;
  text-align: center;
  margin-bottom: 3rem;
}

.animals-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.animal-category {
  background: rgba(255, 255, 255, 0.1);
  padding: 2rem;
  border-radius: 0.75rem;
  backdrop-filter: blur(10px);
}

.animal-category h3 {
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  font-weight: 600;
}

.animal-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.animal-list span {
  font-size: 1.125rem;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 0.375rem;
}

/* CTA Section */
.cta {
  padding: 4rem 2rem;
  background: white;
}

.cta-container {
  max-width: 800px;
  margin: 0 auto;
  text-align: center;
}

.cta-container h2 {
  font-size: 2.5rem;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 1rem;
}

.cta-container p {
  font-size: 1.25rem;
  color: #6b7280;
  margin-bottom: 2rem;
}

.btn-cta {
  display: inline-block;
  background-color: #3b82f6;
  color: white;
  padding: 1rem 3rem;
  border-radius: 0.5rem;
  font-size: 1.25rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-cta:hover {
  background-color: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

/* Footer */
.footer {
  background: #1f2937;
  color: white;
  padding: 2rem;
  text-align: center;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
}

@media (max-width: 768px) {
  .hero {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .hero-content h1 {
    font-size: 2rem;
  }

  .hero-actions {
    flex-direction: column;
  }

  .animal-icons {
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
  }

  .animal-icon {
    font-size: 2.5rem;
  }
}
</style>
