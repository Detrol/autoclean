# AutoClean Documentation

Welcome to the AutoClean documentation hub. This guide will help you understand, install, configure, and use AutoClean effectively.

## 📖 Documentation Overview

### Getting Started

- **[Installation Guide](installation.md)** - Complete setup instructions for local and production environments
- **[Configuration](configuration.md)** - Environment variables, settings, and customization options
- **[Quick Start](#quick-start)** - Get up and running in 5 minutes

### Understanding AutoClean

- **[Architecture Overview](architecture.md)** - System design, patterns, and structure
- **[Database Schema](database-schema.md)** - Complete database documentation with relationships
- **[Features Guide](features.md)** - Detailed documentation of all features

### Development

- **[Development Guide](development.md)** - Local setup, workflow, and best practices
- **[API Reference](api-reference.md)** - Livewire components, models, and services
- **[Testing](testing.md)** - Test suite overview and testing guidelines
- **[Contributing](contributing.md)** - How to contribute to AutoClean

### Deployment & Operations

- **[Deployment Guide](deployment.md)** - Production deployment and server configuration
- **[User Guide](user-guide.md)** - End-user documentation for admins and employees

### Reference

- **[Changelog](changelog.md)** - Version history and recent changes

## 🚀 Quick Start

### For Users
If you're an admin or employee using AutoClean:
1. Read the [User Guide](user-guide.md)
2. Learn about [Features](features.md)

### For Developers
If you're setting up or contributing to AutoClean:
1. Follow the [Installation Guide](installation.md)
2. Review the [Development Guide](development.md)
3. Understand the [Architecture](architecture.md)
4. Check the [Contributing Guide](contributing.md)

### For System Administrators
If you're deploying AutoClean to production:
1. Review [Installation](installation.md) requirements
2. Follow the [Deployment Guide](deployment.md)
3. Configure using the [Configuration Reference](configuration.md)

## 🎯 Core Concepts

### Stations
Physical locations or departments where work is performed. Each station has:
- Assigned employees
- Scheduled tasks
- Inventory items
- Time tracking logs

### Tasks
Work items that can be:
- **One-time** - Single occurrence tasks
- **Recurring** - Automatically scheduled based on patterns (daily, weekly, monthly, yearly)
- **Template-based** - Created from predefined templates
- **Station-specific** - Assigned to particular locations

### Users & Roles
- **Admin** - Full system access, manages stations, tasks, users, and settings
- **Employee** - Clock in/out, complete tasks, view reports

### Time Tracking
- Clock in/out at stations
- Automatic time log creation
- Time reports with PDF export
- Historical time data

### Inventory
- Track supplies per station
- Transaction history
- Low stock monitoring
- Usage patterns

## 📋 Common Tasks

### Setting Up Your First Station
1. Navigate to Admin → Stations
2. Click "Create Station"
3. Enter station details
4. Assign users to the station
5. Add inventory items (optional)

### Creating a Recurring Task
1. Go to Admin → Tasks
2. Click "Create Task"
3. Fill in task details
4. Select recurrence pattern (daily/weekly/monthly/yearly)
5. Assign to a station
6. Save

### Generating Time Reports
1. Navigate to Time Reports
2. Select date range
3. Filter by user/station (optional)
4. Click "Generate Report"
5. Export to PDF if needed

## 🔧 System Requirements

### Minimum Requirements
- PHP 8.2+
- MySQL 5.7+ or MariaDB 10.3+
- Node.js 18+
- 512MB RAM
- 500MB disk space

### Recommended Requirements
- PHP 8.3
- MySQL 8.0+ or MariaDB 10.6+
- Node.js 20+
- 1GB RAM
- 2GB disk space
- Redis for caching (optional)

## 🆘 Getting Help

### Documentation
- Browse the documentation files listed above
- Check the [Architecture Guide](architecture.md) for technical details
- Review [Features](features.md) for capabilities

### Troubleshooting
- See common issues in [Installation Guide](installation.md#troubleshooting)
- Check logs with `php artisan pail`
- Review error messages in Laravel log files

### Community & Support
- Report bugs via GitHub Issues
- Request features via GitHub Issues
- Contribute improvements via Pull Requests

## 📚 Additional Resources

### Laravel Resources
- [Laravel Documentation](https://laravel.com/docs/12.x)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Flux UI Components](https://flux.laravel.com)

### Project Resources
- [GitHub Repository](https://github.com/yourusername/autoclean)
- [Issue Tracker](https://github.com/yourusername/autoclean/issues)
- [Pull Requests](https://github.com/yourusername/autoclean/pulls)

## 📝 Documentation Status

| Document | Status | Last Updated |
|----------|--------|--------------|
| Installation | ✅ Complete | 2025-01-15 |
| Configuration | ✅ Complete | 2025-01-15 |
| Architecture | ✅ Complete | 2025-01-15 |
| Database Schema | ✅ Complete | 2025-01-15 |
| Features | ✅ Complete | 2025-01-15 |
| API Reference | ✅ Complete | 2025-01-15 |
| Development | ✅ Complete | 2025-01-15 |
| Testing | ✅ Complete | 2025-01-15 |
| Deployment | ✅ Complete | 2025-01-15 |
| User Guide | ✅ Complete | 2025-01-15 |
| Contributing | ✅ Complete | 2025-01-15 |
| Changelog | ✅ Complete | 2025-01-15 |

---

**Navigation**: [Installation](installation.md) | [Configuration](configuration.md) | [Architecture](architecture.md) | [Features](features.md) | [Development](development.md)
