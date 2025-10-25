# Traffic Tracker

A modern, privacy-focused website analytics solution built with PHP. Track website visitors without compromising user privacy - no cookies, no personal data collection, just essential metrics.

Hosted on Render: https://traffic-tracker-t18u.onrender.com/

## 🚀 Features

- **Privacy-First Analytics**: Track unique visitors without storing personal data
- **Multi-Website Management**: Add and manage multiple websites from one dashboard
- **Real-Time Tracking**: Monitor visits as they happen
- **Beautiful Dashboard**: Modern UI with interactive charts and data visualization
- **API-Based Tracking**: Simple JavaScript snippet integration
- **User Authentication**: Secure login with email or username support
- **Responsive Design**: Works perfectly on desktop and mobile devices

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+ with Flight Framework (micro-framework)
- **Database**: PostgreSQL with Doctrine ORM
- **Frontend**: BladeOne Templates + DaisyUI + Tailwind CSS + HTMX
- **Charts**: Chart.js for data visualization
- **Authentication**: Session-based with bcrypt password hashing
- **Architecture**: MVC pattern with Repository design
- **Deployment**: Docker containerized with Apache
- **Caching**: File-based and array adapters for Doctrine

## 📊 Dashboard Preview

The dashboard provides comprehensive analytics including:
- **Total visits and unique visitors** with real-time counts
- **Hourly tracking** for Today and Yesterday periods  
- **Daily visit trends** with dual-line charts for longer periods
- **Most popular pages** ranking with visit percentages
- **Website filtering** for multi-site management
- **Responsive design** optimized for mobile and desktop
- **Time period filtering**: Today, Yesterday, 7, 30, 90 days
- **Interactive charts** with Chart.js visualization

<img width="1918" height="811" alt="image" src="https://github.com/user-attachments/assets/e5e11f34-4de7-4949-84a0-4ed0f6b10af6" />

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- PostgreSQL 17
- Composer
- Docker (optional, for containerized deployment)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/traffic-tracker.git
   cd traffic-tracker
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Database setup**
   
   **For PostgreSQL (Production):**
   ```bash
   # Create PostgreSQL database
   createdb traffic_tracker
   
   # Run schema creation
   php create-schema.php
   ```
   
   **For MySQL (Development):**
   ```bash
   # Create MySQL database
   mysql -u root -p -e "CREATE DATABASE traffic_tracker;"
   
   # Import schema if using MySQL
   mysql -u root -p traffic_tracker < database.sql
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   # For PostgreSQL (default):
   # DB_HOST=127.0.0.1
   # DB_PORT=5432
   # DB_NAME=tracker
   # DB_USER=tracker
   # DB_PASS=trackerpassword
   ```

5. **Start the development server**
   ```bash
   php -S localhost:8080 -t public
   ```

**Alternative: Docker Deployment**
```bash
# Build and run with Docker
docker build -t traffic-tracker .
docker run -p 8080:8080 traffic-tracker
```

6. **Access the application**
   Open http://localhost:8080 in your browser

### Initial Setup
The application will guide you through creating your first admin account on first visit, or you can register normally through the interface.

## 📝 Usage

### Adding a Website

1. Log in to your dashboard
2. Navigate to "Websites" section
3. Click "Add Website" button
4. Enter website name and domain
5. Copy the generated tracking script
6. Add the script to your website's HTML

### Tracking Script Integration

Add this script tag to your website's HTML (replace with your actual API key):

```html
<script src="//your-domain.com/api/tracking-script?key=YOUR_API_KEY"></script>
```

The script automatically tracks:
- Page views
- Unique visitors (based on session)
- Referrer information
- Visit timestamps

### Viewing Analytics

1. Go to the Dashboard
2. Select website (if multiple websites configured)
3. Choose time period:
   - **Today/Yesterday**: Shows hourly breakdown (24-hour view)
   - **7/30/90 days**: Shows daily breakdown with trends
4. View comprehensive metrics:
   - **Real-time charts**: Total visits vs unique visitors
   - **Top pages table**: Visit counts with percentages
   - **Traffic insights**: Peak hours and visitor patterns
   - **Website filtering**: Individual or combined analytics

## 🏗️ Architecture

### Directory Structure
```
app/
├── Controllers/      # Request handling logic
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── TrackingController.php
│   └── WebsiteController.php
├── Entities/         # Doctrine ORM entities  
│   ├── User.php
│   ├── Website.php
│   └── TrafficLog.php
├── Repositories/     # Data access layer
├── Services/         # Business logic services
├── Middleware/       # Authentication & CORS middleware
└── Views/            # Blade templates
    ├── layouts/      # Base layouts
    ├── auth/         # Login/Register pages
    ├── dashboard/    # Analytics components  
    └── partials/     # Reusable components

config/
└── doctrine.php      # Database configuration

public/
└── index.php         # Application entry point

storage/
├── cache/            # Template and app cache
└── doctrine/         # Doctrine proxy classes

bootstrap.php         # Application bootstrap
create-schema.php     # Database schema creation
database.sql          # MySQL schema (fallback)
Dockerfile            # Container configuration
```

### Key Components

- **User Management**: Registration, login, bcrypt password hashing
- **Website Management**: CRUD operations for tracked websites with API keys
- **Traffic Logging**: Efficient visit recording with IP-based unique visitor detection
- **Analytics Engine**: Real-time statistical calculations with hourly/daily aggregation
- **API Endpoints**: RESTful API for tracking script delivery and data collection
- **Responsive UI**: Mobile-first design with hamburger navigation and touch-friendly controls
- **Chart Visualization**: Interactive Chart.js integration with dual-metric tracking

## 🔒 Privacy & Security

- **No Personal Data**: Only tracks anonymous visit patterns using IP addresses
- **Privacy-First**: No cookies, no localStorage tracking of personal information
- **Session-Based**: Uses temporary session identifiers for visit aggregation
- **Secure Authentication**: BCrypt password hashing with session management
- **API Key Security**: Unique keys per website for access control
- **CORS Protection**: Properly configured cross-origin resource sharing
- **Input Validation**: Comprehensive server-side validation and sanitization
- **Docker Security**: Containerized deployment with proper permissions

## 🌟 Key Features Explained

### Intelligent Visitor Tracking
The system counts unique visitors using IP address-based identification, providing accurate metrics while maintaining privacy. Supports both total visits and unique visitor differentiation.

### Advanced Time Period Analysis  
- **Hourly Granularity**: Today and Yesterday show 24-hour breakdowns
- **Daily Trends**: Longer periods display day-by-day analytics
- **Flexible Filtering**: Easy switching between time ranges

### Multi-Website Dashboard
Each user can manage multiple websites with separate API keys and independent analytics, all viewable from a unified dashboard with filtering capabilities.

### Modern Responsive Interface
Built with DaisyUI and Tailwind CSS featuring:
- **Mobile-first design** with hamburger navigation
- **Touch-friendly controls** with proper sizing
- **Dark/light theme toggle** for user preference
- **Interactive charts** with real-time data visualization
- **Responsive tables** with horizontal scrolling on mobile

### Production-Ready Deployment
- **Docker containerization** for easy deployment
- **PostgreSQL support** for production scalability  
- **Environment-based configuration** for different deployment stages
- **Optimized caching** with Doctrine and template systems

## 🚀 Recent Improvements

### Mobile Optimization
- **Responsive Navigation**: Hamburger menu with touch-friendly controls
- **Mobile Dashboard**: Optimized layouts with proper spacing and sizing
- **Better UX**: Improved selectors, buttons, and form controls for mobile devices

### Enhanced Analytics  
- **Hourly Tracking**: Detailed hourly breakdowns for Today/Yesterday analysis
- **Improved Charts**: Dual-metric visualization (total visits + unique visitors)
- **Better Data Accuracy**: IP-based unique visitor detection for more reliable metrics

### Modern PHP Implementation
- **PHP 8.2+ Features**: Modern syntax with match expressions, str_contains(), and improved type safety
- **Performance Optimizations**: Better array handling and string operations
- **Code Quality**: Modernized codebase following current PHP best practices

### Production Features
- **Docker Support**: Full containerization with Apache and PHP 8.2
- **PostgreSQL Integration**: Production-ready database configuration
- **Environment Flexibility**: Support for both development (MySQL) and production (PostgreSQL) setups
