# VetCare Platform - Getting Started Guide

## Overview

VetCare Platform is a comprehensive veterinary telemedicine system that connects animal owners with veterinarians. The platform supports all types of animals - from household pets (dogs, cats, birds) to livestock (cows, goats, chickens, sheep, horses, pigs).

## Features Implemented

### ✅ Core Features (MVP)

1. **User Authentication**
   - Registration with role selection (Pet Owner, Veterinarian, Admin)
   - Secure login/logout
   - Role-based access control
   - Rate limiting (3 login attempts per 5 minutes)

2. **Animal Management**
   - Register multiple animals (pets and livestock)
   - Support for: Dogs, Cats, Birds, Rabbits, Hamsters, Cows, Goats, Sheep, Chickens, Ducks, Horses, Pigs
   - Track medical history and notes
   - Soft delete (archive animals)
   - View animal profiles and appointment history

3. **Veterinarian Discovery**
   - Browse all veterinarians
   - Search by name or keywords
   - Filter by specialization
   - View detailed profiles with experience and fees
   - See available time slots (next 30 days)
   - Real-time availability updates via WebSocket

4. **Specializations**
   - Small Animal Medicine
   - Large Animal Medicine
   - Avian Medicine
   - Surgery
   - Dentistry
   - Emergency Care

## Quick Start

### 1. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed sample data (veterinarians and specializations)
php artisan db:seed
```

### 2. Start the Development Server

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server
npm run dev
```

### 3. Access the Application

Open your browser and navigate to: `http://localhost:8000`

## User Accounts

### Sample Veterinarians (Password: `password`)

1. **Dr. Sarah Johnson** - sarah.johnson@vetcare.com
   - Specializations: Small Animal Medicine, Emergency Care
   - Fee: 500 TK

2. **Dr. Michael Chen** - michael.chen@vetcare.com
   - Specializations: Large Animal Medicine, Surgery
   - Fee: 600 TK

3. **Dr. Emily Rodriguez** - emily.rodriguez@vetcare.com
   - Specializations: Avian Medicine, Small Animal Medicine
   - Fee: 550 TK

4. **Dr. Ahmed Hassan** - ahmed.hassan@vetcare.com
   - Specializations: Surgery, Large Animal Medicine
   - Fee: 700 TK

5. **Dr. Lisa Thompson** - lisa.thompson@vetcare.com
   - Specializations: Dentistry, Small Animal Medicine
   - Fee: 450 TK

### Create Your Own Account

1. Go to `/register`
2. Choose your role:
   - **Pet Owner**: Register and manage your animals
   - **Veterinarian**: Provide veterinary services
   - **Admin**: Manage the platform
3. Fill in your details and submit

## User Workflows

### For Animal Owners

1. **Register an Account**
   - Visit the homepage
   - Click "Get Started" or "Register"
   - Select "Pet Owner" role
   - Complete registration

2. **Add Your Animals**
   - Login and go to Dashboard
   - Click "My Animals"
   - Click "+ Add New Animal"
   - Fill in details (name, species, breed, age, weight, gender, medical notes)
   - Submit

3. **Find a Veterinarian**
   - Click "Find Veterinarians" from dashboard or homepage
   - Browse or search for veterinarians
   - Filter by specialization
   - View profiles and available time slots

4. **Manage Your Animals**
   - View all your registered animals
   - Edit animal information
   - View appointment history
   - Remove animals (soft delete)

### For Veterinarians

1. **Login**
   - Use your veterinarian credentials
   - Access veterinarian dashboard

2. **Manage Schedule** (Coming Soon)
   - Set availability
   - Block time slots
   - View appointments

3. **View Profile** (Coming Soon)
   - Update bio and specializations
   - Set consultation fees

### For Administrators

1. **Login**
   - Use admin credentials
   - Access admin dashboard

2. **Manage Platform** (Coming Soon)
   - View analytics
   - Manage users
   - Update homepage content

## API Routes

### Public Routes
- `GET /` - Homepage
- `GET /doctors` - Browse veterinarians
- `GET /doctors/search` - Search veterinarians
- `GET /doctors/{id}` - View veterinarian profile
- `GET /register` - Registration page
- `GET /login` - Login page

### Authenticated Routes
- `POST /logout` - Logout
- `GET /dashboard` - User dashboard

### Pet Owner Routes (Authenticated)
- `GET /pets` - List all animals
- `GET /pets/create` - Register new animal
- `POST /pets` - Store new animal
- `GET /pets/{id}` - View animal details
- `GET /pets/{id}/edit` - Edit animal
- `PUT /pets/{id}` - Update animal
- `DELETE /pets/{id}` - Remove animal (soft delete)

## Technology Stack

- **Backend**: Laravel 10
- **Frontend**: Vue.js 3 with Composition API
- **SSR/Routing**: Inertia.js
- **Database**: MySQL
- **Cache/Session**: Redis
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **Real-time**: Laravel Broadcasting (WebSocket)
- **Testing**: PHPUnit + Eris (Property-Based Testing)

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=VeterinarianTest
php artisan test --filter=DoctorControllerTest
php artisan test --filter=AuthenticationTest

# Run with coverage
php artisan test --coverage
```

## Next Steps (Roadmap)

### Phase 1: Appointment Booking
- [ ] Book appointments with veterinarians
- [ ] Select time slots
- [ ] Choose which animal for appointment
- [ ] View appointment history

### Phase 2: Payment Integration
- [ ] bKash payment gateway
- [ ] Nagad payment gateway
- [ ] Payment confirmation
- [ ] Transaction history

### Phase 3: Video Consultations
- [ ] Google Meet integration
- [ ] Start/end consultations
- [ ] Meeting link generation

### Phase 4: Prescriptions
- [ ] Create digital prescriptions
- [ ] PDF generation
- [ ] Download prescriptions
- [ ] Prescription history

### Phase 5: Additional Features
- [ ] Patient portal
- [ ] Doctor schedule management
- [ ] Admin dashboard with analytics
- [ ] Homepage content management
- [ ] Service directory
- [ ] Real-time notifications
- [ ] Email notifications

## Support

For issues or questions, please refer to the documentation or contact the development team.

## License

This project is proprietary software developed for VetCare Platform.
