# Veterinary Hospital Platform - Setup Documentation

## Technology Stack

- **Backend**: Laravel 10
- **Frontend**: Vue.js 3 with Composition API
- **SSR/Routing**: Inertia.js
- **Database**: MySQL 8.0+
- **Cache/Session**: Redis
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **Real-time**: Laravel Broadcasting with Pusher
- **Testing**: PHPUnit + Eris (Property-Based Testing)

## Installation Complete

The following components have been installed and configured:

### Backend Dependencies
- Laravel 10 framework
- Inertia.js Laravel adapter
- Predis (Redis client)
- Pusher PHP Server (Broadcasting)
- Eris (Property-Based Testing library)

### Frontend Dependencies
- Vue.js 3
- Inertia.js Vue 3 adapter
- Tailwind CSS 3
- Vite plugin for Vue
- PostCSS & Autoprefixer

## Configuration

### Database Configuration
The `.env` file has been configured with:
- Database: `veterinary_hospital`
- Connection: MySQL
- Host: 127.0.0.1
- Port: 3306

**Note**: You need to create the database manually:
```sql
CREATE DATABASE veterinary_hospital;
```

### Cache & Session Configuration
- Cache Driver: Redis
- Session Driver: Redis
- Queue Connection: Redis

**Note**: Ensure Redis server is running on localhost:6379

### Broadcasting Configuration
- Broadcast Driver: Pusher
- Configure Pusher credentials in `.env` file when ready

## Next Steps

1. **Create the database**:
   ```bash
   mysql -u root -p
   CREATE DATABASE veterinary_hospital;
   ```

2. **Start Redis server** (if not already running):
   ```bash
   redis-server
   ```

3. **Run migrations** (when created):
   ```bash
   php artisan migrate
   ```

4. **Build frontend assets**:
   ```bash
   npm run dev
   ```

5. **Start the development server**:
   ```bash
   php artisan serve
   ```

6. **Run tests**:
   ```bash
   php artisan test
   ```

## Testing

### Unit Tests
Located in `tests/Unit/`

### Feature Tests
Located in `tests/Feature/`

### Property-Based Tests
Using Eris library for property-based testing. Example test included in `tests/Unit/PropertyBasedTestExample.php`

## Project Structure

```
veterinary-platform/
├── app/
│   └── Http/
│       └── Middleware/
│           └── HandleInertiaRequests.php
├── resources/
│   ├── css/
│   │   └── app.css (Tailwind directives)
│   ├── js/
│   │   ├── app.js (Inertia + Vue setup)
│   │   └── Pages/
│   │       └── Welcome.vue (Sample page)
│   └── views/
│       └── app.blade.php (Root template)
├── routes/
│   └── web.php (Inertia routes)
├── tests/
│   ├── Feature/
│   │   └── SetupTest.php
│   └── Unit/
│       └── PropertyBasedTestExample.php
├── .env (Configured)
├── vite.config.js (Vue + Inertia)
├── tailwind.config.js (Configured)
└── phpunit.xml (Test configuration)
```

## Verification

Visit `http://localhost:8000` after starting the server to see the welcome page with Tailwind styling.
