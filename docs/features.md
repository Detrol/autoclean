# Features Guide

Comprehensive guide to all AutoClean features and capabilities.

## Table of Contents
- [User Roles](#user-roles)
- [Admin Features](#admin-features)
- [Employee Features](#employee-features)
- [Recurrence System](#recurrence-system)
- [Time Tracking](#time-tracking)
- [Inventory Management](#inventory-management)
- [Reporting](#reporting)
- [Settings](#settings)

## User Roles

AutoClean has two primary user roles:

### Admin
Full system access including:
- Station management
- User management
- Task and schedule creation
- Inventory management
- System settings
- All employee features

### Employee
Limited access including:
- Dashboard view
- Clock in/out
- Task completion
- View assigned tasks
- Time reports
- View inventory

## Admin Features

### Station Management

**Purpose**: Organize work by location or department

**Capabilities**:
- **Create Stations** (`/admin/stations/create`)
  - Name and description
  - Active/inactive status
  - Assign users to stations

- **Edit Stations** (`/admin/stations/{id}/edit`)
  - Update station details
  - Modify assigned users
  - Deactivate stations

- **View Stations** (`/admin/stations`)
  - List all stations
  - View task counts per station
  - View assigned employees

**Use Cases**:
```
Example 1: Multi-Location Cleaning Company
- Station: "Downtown Office"
- Station: "Airport Terminal"
- Station: "Shopping Mall"
- Assign specific employees to each location

Example 2: Department-Based Organization
- Station: "Restrooms"
- Station: "Kitchen Areas"
- Station: "Common Areas"
- Assign teams to different areas
```

---

### Task Scheduling

**Purpose**: Automate recurring tasks and manage one-time tasks

**Task Creation** (`/admin/tasks/create`):
- **Name**: Task description
- **Station**: Where task is performed
- **Due Date**: When task should be completed
- **Description**: Detailed instructions (optional)
- **From Template**: Create from pre-defined templates

**Task Schedule Creation** (`/admin/task-schedules/create`):
- **Name**: Schedule name
- **Station**: Assigned location
- **Frequency**: daily, weekly, monthly, yearly
- **Recurrence Pattern**: Complex patterns (see [Recurrence System](#recurrence-system))
- **Start/End Date**: Schedule duration
- **Active Status**: Enable/disable schedule

**Features**:
- ✅ Automatic task generation based on schedules
- ✅ Edit/delete existing tasks
- ✅ View all tasks across all stations
- ✅ Filter tasks by station, date, completion status
- ✅ Overdue task rollover (if enabled)

---

### User Management

**Purpose**: Manage employee and admin accounts

**Create Users** (`/admin/users/create`):
- Name and email
- Role assignment (admin/employee)
- Station assignments
- Initial password setup

**Employee Invitations** (`/admin/employee-invitations`):
- Send email invitations
- Time-limited invitation tokens
- Track pending/accepted invitations
- Resend invitations

**User Assignment**:
- Assign users to multiple stations
- View user's assigned stations
- Remove users from stations

**Features**:
- ✅ Role-based access control
- ✅ Multi-station assignment
- ✅ Email-based invitations
- ✅ Secure password management

---

### Task Templates

**Purpose**: Standardize common tasks for quick creation

**Template Management** (`/admin/task-templates`):
- Create reusable task templates
- Define default station (optional)
- Include description and instructions
- Quick task creation from templates

**Use Cases**:
```
Template: "Daily Cleaning Checklist"
- Vacuum floors
- Empty trash bins
- Sanitize surfaces
- Restock supplies

Template: "Weekly Deep Clean"
- Detail clean restrooms
- Floor mopping
- Window cleaning
- Equipment sanitization
```

---

### Inventory Management

**Purpose**: Track supplies and materials

**Features**:
- **Inventory Items** (`/admin/inventory-items`)
  - Create inventory items
  - Set SKU (Stock Keeping Unit)
  - Define units (bottles, rolls, boxes, etc.)
  - Set minimum quantity thresholds

- **Station Inventory** (`/admin/stations/{id}/inventory`)
  - Assign items to stations
  - Track quantities per station
  - Low stock alerts
  - Transaction history

- **Inventory Transactions**
  - Record stock movements (in/out/adjustment)
  - Track who made changes
  - Add notes to transactions
  - Audit trail

**Workflow**:
```
1. Admin creates inventory item: "Paper Towels"
2. Assigns to stations with initial quantities
3. Employee uses supplies → records transaction
4. System alerts when quantity < minimum threshold
5. Admin restocks → records incoming transaction
```

---

### System Settings

**Purpose**: Configure application behavior

**Settings Interface** (`/admin/settings`):
- **General Settings**
  - Application name
  - Default timezone
  - Admin clock-in requirement

- **Task Settings**
  - Enable/disable overdue task rollover
  - Task generation lookahead period
  - Completion requirements

- **Email Settings**
  - SMTP configuration
  - Email templates
  - Notification preferences

**Database-Driven Settings**:
Settings stored in database and cached for performance. Accessed via `settings()` helper function.

---

### Time Log Management

**Purpose**: Review and manage employee time tracking

**Features** (`/admin/time-logs`):
- View all employee time logs
- Filter by date range, user, station
- Manually adjust clock in/out times
- Add/edit notes
- Delete invalid entries
- Export time data

---

## Employee Features

### Dashboard

**Purpose**: Central hub for daily tasks and activities

**Components** (`/dashboard`):
- **Today's Tasks**: Tasks due today at assigned stations
- **Overdue Tasks**: Past-due incomplete tasks
- **Quick Clock In/Out**: One-click time tracking
- **Recent Activity**: Recent task completions
- **Station Summary**: Overview of assigned stations

**Features**:
- ✅ Real-time updates via Livewire
- ✅ Task filtering and sorting
- ✅ One-click task completion
- ✅ Visual task status indicators
- ✅ Mobile-friendly responsive design

---

### Time Tracking (Clock In/Out)

**Purpose**: Track work hours accurately

**Clock In Process**:
1. Employee navigates to dashboard
2. Selects station from dropdown
3. Clicks "Clock In"
4. Time log created with current timestamp

**Clock Out Process**:
1. Employee clicks "Clock Out"
2. Clock out timestamp recorded
3. Duration calculated automatically
4. Optional notes field

**Features**:
- ✅ One active session per employee
- ✅ Station-specific time tracking
- ✅ Automatic duration calculation
- ✅ Cannot clock in at multiple stations simultaneously
- ✅ Admin can configure if clock-in is required

**Validations**:
- Must clock out before clocking in again
- Cannot have overlapping time logs
- Cannot clock in to unassigned stations

---

### Task Completion

**Purpose**: Mark tasks as completed

**Completion Process**:
1. Employee views task list
2. Completes physical work
3. Clicks "Complete" on task
4. System records:
   - Completion timestamp
   - User who completed task
   - Marks task as completed

**Additional Tasks**:
Employees can log additional tasks performed beyond scheduled tasks:
- Click "Add Additional Task"
- Enter task name and description
- Record completion
- Tracks extra work performed

**Features**:
- ✅ Quick one-click completion
- ✅ Completion confirmation
- ✅ Track who completed each task
- ✅ Log additional unscheduled work
- ✅ Cannot complete future tasks (configurable)

---

### Time Reports

**Purpose**: View personal work history

**Report Features** (`/employee/time-reports`):
- **Date Range Selection**: Custom date ranges
- **Summary Statistics**:
  - Total hours worked
  - Number of shifts
  - Average shift duration
  - Tasks completed

- **Detailed View**:
  - Clock in/out times per shift
  - Station worked
  - Duration per shift
  - Notes

- **PDF Export**: Download reports for personal records

**Use Cases**:
- Weekly hour summaries
- Monthly timesheets
- Payroll verification
- Personal record keeping

---

## Recurrence System

**Purpose**: Automate task scheduling with flexible patterns

### Recurrence Calculator Service

The `RecurrenceCalculator` service handles all recurrence logic.

### Frequency Types

#### 1. Daily Tasks

**Options**:
- **Every Day**: Task occurs every single day
- **Weekdays Only**: Monday through Friday

**Examples**:
```
Daily Cleaning
- Start: 2025-01-01
- Frequency: daily
- Result: Task generated for every day

Weekday Cleaning
- Start: 2025-01-01
- Frequency: daily
- Days of Week: [1,2,3,4,5] (Mon-Fri)
- Result: Tasks only on weekdays
```

**Configuration**:
```php
frequency: 'daily'
interval: 1 (every day) or 2 (every other day)
days_of_week: null (all days) or [1,2,3,4,5] (weekdays)
```

---

#### 2. Weekly Tasks

**Options**:
- **Specific Days**: Select which days of week
- **Even/Odd Weeks**: Bi-weekly patterns

**Examples**:
```
Monday & Thursday Cleaning
- Frequency: weekly
- Days of Week: [1, 4] (Mon, Thu)
- Result: Tasks every Monday and Thursday

Bi-Weekly Cleaning (Even Weeks)
- Frequency: weekly
- Week Type: even
- Days of Week: [1] (Mon)
- Result: Task every other Monday (even-numbered weeks)
```

**Configuration**:
```php
frequency: 'weekly'
days_of_week: [0,1,2,3,4,5,6] // 0=Sunday, 6=Saturday
week_type: null | 'even' | 'odd'
interval: 1 (every week) or 2 (every 2 weeks)
```

---

#### 3. Monthly Tasks

**Options**:
- **Specific Day Number**: Day 1-31 of month
- **Specific Weekday**: e.g., "2nd Tuesday", "Last Friday"

**Examples**:
```
1st of Month Cleaning
- Frequency: monthly
- Day of Month: 1
- Result: Task on 1st of every month

2nd Tuesday of Month
- Frequency: monthly
- Week of Month: 2
- Day of Week: 2 (Tuesday)
- Result: Task on 2nd Tuesday each month

Last Friday of Month
- Frequency: monthly
- Week of Month: -1 (last)
- Day of Week: 5 (Friday)
- Result: Task on last Friday of month
```

**Configuration**:
```php
frequency: 'monthly'
day_of_month: 1-31 // Specific day number
// OR
week_of_month: 1-4 or -1 (last)
day_of_week: 0-6 // Specific weekday
```

---

#### 4. Yearly Tasks

**Options**:
- **Specific Date**: Month and day each year

**Examples**:
```
Annual Inspection
- Frequency: yearly
- Month: 6 (June)
- Day of Month: 15
- Result: Task on June 15 every year

New Year Cleaning
- Frequency: yearly
- Month: 1 (January)
- Day of Month: 1
- Result: Task on January 1 every year
```

**Configuration**:
```php
frequency: 'yearly'
month_of_year: 1-12
day_of_month: 1-31
```

---

### Task Generation

**Automated Process** (via cron):
```bash
# Runs daily at midnight
php artisan tasks:generate
```

**Process**:
1. Find all active schedules
2. Calculate next occurrence dates using RecurrenceCalculator
3. Generate tasks for next 30 days (configurable)
4. Avoid duplicate task generation
5. Update `last_generated` timestamp

**Manual Generation**:
```bash
# Generate for specific number of days
php artisan tasks:generate --days=60
```

---

### Overdue Task Rollover

**Purpose**: Handle incomplete overdue tasks

**Automated Process** (via cron):
```bash
# Runs daily at 1:00 AM
php artisan tasks:rollover-overdue
```

**Process**:
1. Find tasks past due date that are incomplete
2. Create new task for today
3. Mark original task with rollover flag
4. Limit maximum rollover count (default: 5)

**Configuration**:
```
Setting: TASK_ROLLOVER_ENABLED (true/false)
Max Rollovers: 5 (prevent infinite loops)
```

---

## Time Tracking

### Clock In/Out System

**States**:
- **Not Clocked In**: No active time log
- **Clocked In**: Active time log with `clock_out = null`
- **Clocked Out**: Time log completed with duration calculated

**Rules**:
1. Employee can only have one active clock-in per station
2. Must clock out before clocking in again
3. Clock in/out timestamps are immutable (admin can edit)
4. Duration calculated as: `(clock_out - clock_in) in minutes`

### Duration Calculation

```php
// Automatic calculation on clock out
$duration = $clockOut->diffInMinutes($clockIn);
$timeLog->update([
    'clock_out' => $clockOut,
    'duration_minutes' => $duration
]);
```

### Time Log Reports

**Data Included**:
- Date and time of clock in/out
- Station worked
- Total duration
- Tasks completed during shift
- Additional tasks logged
- Notes

**Export Formats**:
- PDF: Formatted report with company header
- CSV: Raw data for spreadsheet import (future)

---

## Inventory Management

### Three-Level System

1. **Inventory Items**: Global item definitions
2. **Station Inventory**: Item quantities per station
3. **Inventory Transactions**: History of all movements

### Transaction Types

**IN**: Adding stock
```
Example: Received 10 bottles of cleaner
- Type: in
- Quantity: +10
- Notes: "Weekly delivery"
```

**OUT**: Using/removing stock
```
Example: Used 2 bottles
- Type: out
- Quantity: -2
- Notes: "Daily cleaning supply usage"
```

**ADJUSTMENT**: Inventory corrections
```
Example: Found discrepancy during count
- Type: adjustment
- Quantity: +3 or -3
- Notes: "Physical count correction"
```

### Low Stock Alerts

```php
// Check if item is low at a station
if ($stationInventory->quantity < $inventoryItem->min_quantity) {
    // Display warning
    // Send notification (future feature)
}
```

---

## Reporting

### Available Reports

#### 1. Time Reports
- Personal or all employees
- Date range selection
- Total hours, shifts, averages
- PDF export

#### 2. Task Completion Reports (Future)
- Tasks completed per station
- Employee productivity
- Completion rates
- Overdue task trends

#### 3. Inventory Reports (Future)
- Usage patterns
- Low stock items
- Transaction history
- Reorder recommendations

---

## Settings

### System Settings

Accessible via: `/admin/settings`

**Categories**:

#### General
- Application name
- Timezone
- Date/time formats
- Language (future)

#### Users & Access
- Admin requires clock-in
- Employee permissions
- Invitation expiration (days)

#### Tasks
- Task rollover enabled
- Lookahead days for generation
- Require clock-in for task completion
- Allow future task completion

#### Notifications (Future)
- Email notifications
- Low stock alerts
- Overdue task reminders
- Daily/weekly summaries

### Settings Storage

Settings stored in `settings` table:
```php
// Get setting
$value = settings('key', 'default');

// Set setting (admin only)
$settingsService->set('key', 'value');
```

**Caching**:
Settings cached for 1 hour to reduce database queries.

---

## Feature Roadmap

### Planned Features

- [ ] Email notifications
- [ ] Mobile app (PWA)
- [ ] Advanced reporting dashboard
- [ ] CSV export for all reports
- [ ] Task comments/notes
- [ ] Photo attachments for task completion
- [ ] Barcode scanning for inventory
- [ ] Multi-language support
- [ ] Custom fields for tasks
- [ ] API for third-party integrations

---

## Feature Comparison Matrix

| Feature | Admin | Employee |
|---------|-------|----------|
| View Dashboard | ✅ | ✅ |
| Create/Edit Stations | ✅ | ❌ |
| Create/Edit Tasks | ✅ | ❌ |
| Create Task Schedules | ✅ | ❌ |
| Manage Users | ✅ | ❌ |
| Manage Inventory | ✅ | ❌ |
| Clock In/Out | ✅* | ✅ |
| Complete Tasks | ✅ | ✅ |
| View Time Reports | ✅ (all) | ✅ (own) |
| Log Additional Tasks | ✅ | ✅ |
| System Settings | ✅ | ❌ |
| View All Time Logs | ✅ | ❌ |
| Invite Employees | ✅ | ❌ |

*Admin clock-in can be configured as optional

---

## Next Steps

- Review [User Guide](user-guide.md) for detailed usage instructions
- Check [API Reference](api-reference.md) for component documentation
- See [Development Guide](development.md) to add new features

---

**Navigation**: [← Database Schema](database-schema.md) | [Back to Documentation](README.md) | [API Reference →](api-reference.md)
