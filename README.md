# Traffic Tracker

A modern, privacy-focused website analytics solution built with PHP. Track website visitors without compromising user privacy - no cookies, no personal data collection, just essential metrics.

## 🚀 Features

- **Privacy-First Analytics**: Track unique visitors without storing personal data
- **Multi-Website Management**: Add and manage multiple websites from one dashboard
- **Real-Time Tracking**: Monitor visits as they happen
- **Beautiful Dashboard**: Modern UI with interactive charts and data visualization
- **API-Based Tracking**: Simple JavaScript snippet integration
- **User Authentication**: Secure login with email or username support
- **Responsive Design**: Works perfectly on desktop and mobile devices

## 🛠️ Technology Stack

- **Backend**: PHP 8+ with Flight Framework (micro-framework)
- **Database**: MySQL with Doctrine ORM
- **Frontend**: BladeOne Templates + DaisyUI + Tailwind CSS
- **Charts**: Chart.js for data visualization
- **Authentication**: Session-based with password hashing
- **Architecture**: MVC pattern with Repository design

## 📊 Dashboard Preview

The dashboard provides comprehensive analytics including:
- Total visits and unique visitors
- Daily visit trends with dual-line charts
- Most popular pages ranking
- Tracking status monitoring
- Time period filtering (7, 30, 90 days)

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/traffic-tracker-v2.git
   cd traffic-tracker-v2
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Database setup**
   ```bash
   # Create a MySQL database
   mysql -u root -p -e "CREATE DATABASE traffic_tracker;"
   
   # Import the database schema
   mysql -u root -p traffic_tracker < database.sql
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

5. **Start the development server**
   ```bash
   php -S localhost:8080 -t public
   ```

6. **Access the application**
   Open http://localhost:8080 in your browser

### Default Admin Account
- **Email**: admin@example.com
- **Username**: admin
- **Password**: admin123

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
2. Select time period (7, 30, or 90 days)
3. View metrics:
   - Total visits vs unique visitors chart
   - Top pages table with visit counts
   - Traffic status indicators

## 🏗️ Architecture

### Directory Structure
```
app/
├── Controllers/        # Request handling logic
├── Entities/          # Doctrine ORM entities
├── Repositories/      # Data access layer
├── Views/            # Blade templates
│   ├── layouts/      # Base layouts
│   └── partials/     # Reusable components
└── Middleware/       # Authentication middleware

public/
└── index.php         # Application entry point

database.sql          # Database schema and seed data
```

### Key Components

- **User Management**: Registration, login, password hashing
- **Website Management**: CRUD operations for tracked websites
- **Traffic Logging**: Efficient visit recording with session tracking
- **Analytics Engine**: Statistical calculations for visitor insights
- **API Endpoints**: RESTful API for tracking script delivery

## 🔒 Privacy & Security

- **No Personal Data**: Only tracks anonymous visit patterns
- **Session-Based**: Uses temporary session identifiers
- **Secure Authentication**: BCrypt password hashing
- **CSRF Protection**: Form validation and user verification
- **API Key Security**: Unique keys per website for access control

## 🌟 Key Features Explained

### Unique Visitor Tracking
The system counts unique visitors per day using session-based identification, ensuring privacy while providing accurate metrics.

### Multi-Website Support
Each user can manage multiple websites with separate API keys and independent analytics.

### Real-Time Dashboard
Interactive charts update automatically, showing both total visits and unique visitor trends.

### Modern UI/UX
Built with DaisyUI and Tailwind CSS for a beautiful, responsive interface that works on all devices.
