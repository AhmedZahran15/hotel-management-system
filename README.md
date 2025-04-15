# Hotel Management System

A modern and comprehensive hotel management solution built with Laravel and Vue.js. This system enables hotels to efficiently manage rooms, reservations, clients, staff, and financial operations.

![Hotel Management System](public/logo.jpg)

## Features

### Client Management
- Client registration and authentication
- Client profiles with detailed information
- Client approval workflow by staff
- Client notification system

### Room Management
- Comprehensive room inventory system
- Room categorization and pricing
- Room status tracking (available, occupied, maintenance, etc.)
- Room images and detailed descriptions

### Reservation System
- Seamless online reservation process
- Stripe payment integration
- Reservation status tracking
- Room availability checking

### Staff Management
- Role-based access control (Admin, Manager, Receptionist)
- Staff profile management
- Performance tracking
- Account suspension/banning functionality

### Floor Management
- Floor creation and management
- Floor-based room organization
- Occupancy statistics

### Additional Features
- Dashboard with statistics and analytics
- Mobile responsive design
- Export client data to Excel
- Comprehensive search and filter capabilities
- Multi-currency support

## Tech Stack

### Backend
- **PHP 8.2** with **Laravel 12** framework
- **MySQL/PostgreSQL** database
- **Spatie Media Library** for media management
- **Spatie Permissions** for role-based access
- **Stripe** for payment processing
- **Laravel Query Builder** for advanced filtering

### Frontend
- **Vue.js 3** with **Composition API**
- **Inertia.js** for SPA-like experience
- **TypeScript** for type safety
- **Tailwind CSS** with **Shadcn UI** components
- **Chart.js** for data visualization
- **Zod** for form validation

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js and npm/pnpm
- MySQL or PostgreSQL
- Stripe account for payment processing

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/hotel-management-system.git
   cd hotel-management-system
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   # or if you use pnpm
   pnpm install
   ```

4. **Set up environment file**
   ```bash
   cp .env.example .env
   # Configure your database and Stripe API keys in the .env file
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Link storage**
   ```bash
   php artisan storage:link
   ```

8. **Compile assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

9. **Start the development server**
   ```bash
   php artisan serve
   ```

10. **Access the application**
    Visit `http://localhost:8000` in your web browser

## Development

For development with hot-reloading:

```bash
# Start everything with concurrently (as defined in composer.json)
composer dev

# Or start services individually
php artisan serve
npm run dev
php artisan queue:listen
```

## User Roles and Access

- **Admin**: Full access to all system features
- **Manager**: Manage rooms, floors, and staff in their assigned areas
- **Receptionist**: Handle check-ins, check-outs, and client approvals
- **Client**: Book rooms, manage reservations, and update profile

## Testing

```bash
php artisan test
# or
./vendor/bin/pest
```

## Deployment

The system can be deployed to any standard PHP hosting environment that meets the prerequisites. For optimal performance, consider using Laravel Forge, Laravel Vapor, or similar deployment services.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- [Laravel Team](https://laravel.com)
- [Vue.js Team](https://vuejs.org)
- [Spatie](https://spatie.be) for their excellent Laravel packages
- [Shadcn UI](https://ui.shadcn.com) for the beautiful UI components
- [Stripe](https://stripe.com) for payment processing