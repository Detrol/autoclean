# AutoClean

> Modern task management and maintenance scheduling system for cleaning operations

[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-fb70a9.svg)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777bb4.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

AutoClean is a comprehensive task management and maintenance scheduling application built with Laravel 12 and Livewire 3. It provides powerful station-based task management, advanced recurring schedules, time tracking, and inventory management—perfect for cleaning services, facility management, and maintenance operations.

## ✨ Key Features

- **🏢 Multi-Station Management** - Organize tasks across multiple locations or departments
- **📅 Advanced Recurring Tasks** - Support for daily, weekly (even/odd weeks), monthly, and yearly patterns
- **⏰ Time Tracking** - Clock in/out functionality with detailed time logs and PDF reports
- **📋 Task Templates** - Create reusable task templates for common operations
- **📦 Inventory Management** - Track supplies and materials per station with transaction history
- **👥 Role-Based Access** - Separate admin and employee interfaces with appropriate permissions
- **🔄 Real-Time Updates** - Livewire 3 provides seamless reactive components without page reloads
- **📊 Time Reports** - Generate comprehensive time reports with PDF export
- **🎯 Task Assignment** - Assign users to specific stations and manage responsibilities
- **🔔 Overdue Task Management** - Automatic rollover system for incomplete tasks
- **💼 Employee Invitations** - Streamlined onboarding process for new team members
- **⚙️ Customizable Settings** - Configure application behavior through an admin interface

## 🚀 Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL/MariaDB database server

### Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/autoclean.git
cd autoclean

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file and configure
cp .env.example .env
php artisan key:generate

# Configure your database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=autoclean
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations and seed database
php artisan migrate --seed

# Build assets
npm run build

# Start the development server
composer dev
```

The application will be available at `http://localhost:8000`

**Default Admin Credentials** (if using seeders):
- Email: admin@example.com
- Password: password

## 🛠️ Technology Stack

| Category | Technology |
|----------|-----------|
| **Framework** | Laravel 12 |
| **Frontend** | Livewire 3, Tailwind CSS v4, Flux UI |
| **Database** | MySQL/MariaDB |
| **Build Tool** | Vite |
| **Testing** | Pest PHP |
| **Code Style** | Laravel Pint |
| **Queue** | Database driver |
| **Cache** | Database driver |

## 📚 Documentation

Comprehensive documentation is available in the `/docs` directory:

- **[Installation Guide](docs/installation.md)** - Detailed setup instructions
- **[Architecture Overview](docs/architecture.md)** - Application structure and design patterns
- **[Database Schema](docs/database-schema.md)** - Complete database documentation
- **[Features Guide](docs/features.md)** - Detailed feature documentation
- **[API Reference](docs/api-reference.md)** - Livewire components and services
- **[Development Guide](docs/development.md)** - Contributing and development workflow
- **[Deployment Guide](docs/deployment.md)** - Production deployment instructions
- **[User Guide](docs/user-guide.md)** - End-user documentation
- **[Configuration](docs/configuration.md)** - Environment and settings reference
- **[Testing](docs/testing.md)** - Testing guidelines and practices

## 🎯 Core Concepts

### Stations
Stations represent different work locations, departments, or areas. Each station can have:
- Assigned users (employees)
- Scheduled tasks
- Inventory items
- Time logs

### Tasks
Tasks can be:
- One-time or recurring (with complex patterns)
- Assigned to specific stations
- Created from templates
- Tracked for completion status
- Automatically scheduled by the system

### Recurrence Patterns
The RecurrenceCalculator service supports:
- **Daily** - Every day or weekdays only
- **Weekly** - Specific days, even/odd weeks
- **Monthly** - Specific day number or weekday (e.g., "second Tuesday")
- **Yearly** - Annual tasks on specific dates

### Time Tracking
Employees can:
- Clock in/out at stations
- Log time for task completion
- View personal time reports
- Export reports to PDF

## 🧪 Testing

```bash
# Run all tests
composer test

# Run with coverage
./vendor/bin/pest --coverage

# Run specific test suite
./vendor/bin/pest tests/Feature
```

## 🎨 Code Style

```bash
# Format code automatically
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](docs/contributing.md) for details.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure:
- All tests pass (`composer test`)
- Code follows Laravel Pint standards (`./vendor/bin/pint`)
- New features include tests
- Documentation is updated

## 📝 Development Commands

```bash
# Start full development stack (recommended)
composer dev

# Individual services
php artisan serve        # Laravel dev server (port 8000)
npm run dev             # Vite with hot reload
php artisan queue:listen # Queue worker
php artisan pail        # Log viewer

# Database
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Reset and seed

# Scheduled tasks (normally run by cron)
php artisan tasks:generate       # Generate scheduled tasks
php artisan tasks:rollover-overdue # Rollover overdue tasks
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com)
- UI powered by [Livewire](https://livewire.laravel.com) and [Flux UI](https://flux.laravel.com)
- Styled with [Tailwind CSS](https://tailwindcss.com)
- Tested with [Pest PHP](https://pestphp.com)

## 📞 Support

For issues, questions, or contributions:
- Open an [issue](https://github.com/yourusername/autoclean/issues)
- Submit a [pull request](https://github.com/yourusername/autoclean/pulls)
- Check the [documentation](docs/README.md)

---

Made with ❤️ for efficient facility management
