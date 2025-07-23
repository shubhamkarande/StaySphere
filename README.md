# StaySphere - Airbnb Clone

A full-stack vacation rental platform built with Astro (frontend) and Laravel (backend).

## 🏗️ Tech Stack

### Frontend
- **Astro** - Static site generator with dynamic capabilities
- **Tailwind CSS** - Utility-first CSS framework
- **TypeScript** - Type-safe JavaScript
- **Leaflet** - Interactive maps

### Backend
- **Laravel 10** - PHP web framework
- **PostgreSQL** - Primary database
- **Laravel Sanctum** - API authentication
- **Stripe** - Payment processing
- **Cloudinary** - Image storage and optimization

### Infrastructure
- **Google Cloud Platform** - Hosting (App Engine)
- **PostgreSQL** - Database hosting

## 🚀 Features

### For Guests
- Browse and search listings with advanced filters
- View detailed property information with image galleries
- Book properties with secure Stripe payments
- Manage bookings and view booking history
- Leave reviews after stays
- Message hosts through integrated chat system

### For Hosts
- Create and manage property listings
- Upload and manage property photos via Cloudinary
- Set pricing, availability, and house rules
- Manage bookings and communicate with guests
- View earnings and analytics
- Calendar integration for availability management

### Platform Features
- User authentication and profiles
- Real-time messaging system
- Review and rating system
- Mobile-responsive design
- SEO-optimized pages
- Image optimization and CDN delivery

## 📁 Project Structure

```
staysphere/
├── frontend/                 # Astro frontend application
│   ├── src/
│   │   ├── components/      # Reusable UI components
│   │   ├── layouts/         # Page layouts
│   │   ├── pages/           # Route pages
│   │   └── styles/          # Global styles
│   ├── public/              # Static assets
│   └── package.json
├── backend/                 # Laravel backend API
│   ├── app/
│   │   ├── Http/Controllers/Api/  # API controllers
│   │   ├── Models/          # Eloquent models
│   │   └── ...
│   ├── database/
│   │   ├── migrations/      # Database migrations
│   │   └── seeders/         # Database seeders
│   ├── routes/api.php       # API routes
│   └── composer.json
└── README.md
```

## 🛠️ Setup Instructions

### Prerequisites
- Node.js 18+ and npm
- PHP 8.1+ and Composer
- PostgreSQL 13+
- Stripe account (for payments)
- Cloudinary account (for image storage)

### Backend Setup

1. **Navigate to backend directory**
   ```bash
   cd backend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   ```
   
   Update `.env` with your configuration:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=staysphere
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   STRIPE_KEY=pk_test_your_stripe_key
   STRIPE_SECRET=sk_test_your_stripe_secret
   STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
   
   CLOUDINARY_CLOUD_NAME=your_cloud_name
   CLOUDINARY_API_KEY=your_api_key
   CLOUDINARY_API_SECRET=your_api_secret
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Run database migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```
   
   Backend will be available at `http://localhost:8000`

### Frontend Setup

1. **Navigate to frontend directory**
   ```bash
   cd frontend
   ```

2. **Install Node.js dependencies**
   ```bash
   npm install
   ```

3. **Start the development server**
   ```bash
   npm run dev
   ```
   
   Frontend will be available at `http://localhost:4321`

## 🔧 Configuration

### Environment Variables

#### Backend (.env)
```env
# Application
APP_NAME=StaySphere
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:4321

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=staysphere
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Stripe
STRIPE_KEY=pk_test_your_publishable_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# Cloudinary
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

#### Frontend (astro.config.mjs)
```javascript
export default defineConfig({
  env: {
    schema: {
      PUBLIC_API_URL: {
        context: 'client',
        access: 'public',
        default: 'http://localhost:8000/api'
      }
    }
  }
});
```

## 📚 API Documentation

### Authentication Endpoints
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user

### Listings Endpoints
- `GET /api/listings` - Get all listings (with filters)
- `GET /api/listings/{id}` - Get single listing
- `POST /api/listings` - Create listing (auth required)
- `PUT /api/listings/{id}` - Update listing (auth required)
- `DELETE /api/listings/{id}` - Delete listing (auth required)

### Bookings Endpoints
- `POST /api/bookings` - Create booking (auth required)
- `GET /api/bookings` - Get user bookings (auth required)
- `PUT /api/bookings/{id}/cancel` - Cancel booking (auth required)

### Reviews Endpoints
- `POST /api/reviews` - Create review (auth required)
- `GET /api/listings/{id}/reviews` - Get listing reviews

### Upload Endpoints
- `POST /api/upload/image` - Upload single image (auth required)
- `POST /api/upload/images` - Upload multiple images (auth required)

## 🎨 UI Components

### Key Components
- `SearchBar.astro` - Property search functionality
- `ListingCard.astro` - Property listing display
- `BookingForm.astro` - Booking form with Stripe integration
- `ImageGallery.astro` - Property image gallery with modal
- `FilterSidebar.astro` - Advanced search filters
- `ReviewBox.astro` - Review display component

### Pages
- `/` - Homepage with featured listings
- `/listings` - Search and browse listings
- `/listings/[id]` - Property detail page
- `/booking/[id]` - Booking page
- `/dashboard` - User dashboard
- `/dashboard/bookings` - Booking management
- `/dashboard/listings` - Listing management (hosts)
- `/auth/login` - User login
- `/auth/register` - User registration

## 🚀 Deployment

### Backend (Google Cloud App Engine)

1. **Install Google Cloud SDK**
2. **Create app.yaml**
   ```yaml
   runtime: php81
   
   env_variables:
     APP_KEY: your_app_key
     DB_CONNECTION: pgsql
     DB_HOST: your_db_host
     DB_DATABASE: your_db_name
     DB_USERNAME: your_db_user
     DB_PASSWORD: your_db_password
   ```

3. **Deploy**
   ```bash
   gcloud app deploy
   ```

### Frontend (Vercel/Netlify)

1. **Build the project**
   ```bash
   npm run build
   ```

2. **Deploy to your preferred platform**
   - Update `PUBLIC_API_URL` to point to your production API

## 🔐 Security Features

- CSRF protection
- SQL injection prevention via Eloquent ORM
- XSS protection
- Rate limiting on API endpoints
- Secure file upload validation
- Environment variable protection
- HTTPS enforcement in production

## 🧪 Testing

### Backend Testing
```bash
cd backend
php artisan test
```

### Frontend Testing
```bash
cd frontend
npm run test
```

## 📈 Performance Optimizations

- Image optimization via Cloudinary
- Database query optimization with eager loading
- Frontend asset optimization
- CDN integration for static assets
- Caching strategies for API responses
- Lazy loading for images and components

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support, email support@staysphere.com or create an issue in the GitHub repository.

## 🙏 Acknowledgments

- Inspired by Airbnb's user experience
- Built with modern web technologies
- Community contributions and feedback

---

**StaySphere** - Book Unique Stays Around the Globe 🌍