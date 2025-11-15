# Database Schema

Complete database schema documentation for AutoClean.

## Table of Contents
- [Overview](#overview)
- [Entity Relationship Diagram](#entity-relationship-diagram)
- [Core Tables](#core-tables)
- [Pivot Tables](#pivot-tables)
- [Relationships Summary](#relationships-summary)
- [Indexes and Constraints](#indexes-and-constraints)
- [Migrations](#migrations)

## Overview

AutoClean uses MySQL/MariaDB with the following characteristics:
- **Character Set**: UTF8MB4
- **Collation**: utf8mb4_unicode_ci
- **Engine**: InnoDB
- **Total Tables**: 15+ (including Laravel system tables)

## Entity Relationship Diagram

```
┌──────────┐         ┌─────────────┐         ┌──────────┐
│  users   │────────<│station_user │>────────│ stations │
└──────────┘         └─────────────┘         └──────────┘
     │                                              │
     │                                              │
     │ has many                         has many   │
     ▼                                              ▼
┌──────────┐                               ┌──────────┐
│time_logs │                               │  tasks   │
└──────────┘                               └──────────┘
     │                                              │
     │                                    belongs to│
     │                                              ▼
     │                                     ┌──────────────┐
     │                                     │task_schedules│
     │                                     └──────────────┘
     │
     │
┌──────────┐         ┌─────────────────┐         ┌───────────────┐
│ stations │────────<│station_inventory│>────────│inventory_items│
└──────────┘         └─────────────────┘         └───────────────┘
                              │                           │
                              │                           │
                              └──────────>────────────────┘
                                   has many
                              ┌──────────────────────┐
                              │inventory_transactions│
                              └──────────────────────┘

┌──────────┐         ┌───────────────┐
│  tasks   │────────<│task_templates │
└──────────┘         └───────────────┘
     │
     │ has many
     ▼
┌───────────────────────────┐
│completed_additional_tasks │
└───────────────────────────┘

┌──────────┐
│  users   │
└──────────┘
     │
     │ has many
     ▼
┌────────────────────┐
│employee_invitations│
└────────────────────┘
```

## Core Tables

### users

Stores user accounts with role-based access control.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| name | varchar(255) | NOT NULL | User's full name |
| email | varchar(255) | NOT NULL, UNIQUE | Email address for login |
| email_verified_at | timestamp | NULL | Email verification timestamp |
| password | varchar(255) | NOT NULL | Hashed password |
| role | enum('admin','employee') | NOT NULL, DEFAULT 'employee' | User role |
| remember_token | varchar(100) | NULL | Remember me token |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- UNIQUE: `email`
- INDEX: `role`

**Relationships**:
- `belongsToMany`: stations (via station_user pivot)
- `hasMany`: time_logs
- `hasMany`: employee_invitations

---

### stations

Represents physical locations or departments where work is performed.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| name | varchar(255) | NOT NULL | Station name |
| description | text | NULL | Station description |
| active | boolean | DEFAULT true | Whether station is active |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- INDEX: `active`

**Relationships**:
- `belongsToMany`: users (via station_user pivot)
- `hasMany`: tasks
- `hasMany`: time_logs
- `hasMany`: inventory_items
- `hasManyThrough`: station_inventory

---

### station_user (Pivot Table)

Associates users with stations.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| station_id | bigint unsigned | NOT NULL, FOREIGN KEY | Reference to stations.id |
| user_id | bigint unsigned | NOT NULL, FOREIGN KEY | Reference to users.id |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- UNIQUE: (`station_id`, `user_id`)
- FOREIGN KEY: `station_id` → `stations.id` ON DELETE CASCADE
- FOREIGN KEY: `user_id` → `users.id` ON DELETE CASCADE

---

### tasks

Individual task instances (both one-time and generated from schedules).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| name | varchar(255) | NOT NULL | Task name |
| description | text | NULL | Task description |
| station_id | bigint unsigned | NOT NULL, FOREIGN KEY | Reference to stations.id |
| task_schedule_id | bigint unsigned | NULL, FOREIGN KEY | Reference to task_schedules.id |
| due_date | date | NOT NULL | When task is due |
| completed | boolean | DEFAULT false | Whether task is completed |
| completed_at | timestamp | NULL | Completion timestamp |
| completed_by | bigint unsigned | NULL, FOREIGN KEY | Reference to users.id who completed |
| is_additional | boolean | DEFAULT false | Additional task flag |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- INDEX: `station_id`
- INDEX: `task_schedule_id`
- INDEX: `due_date`
- INDEX: `completed`
- INDEX: (`station_id`, `due_date`, `completed`)
- FOREIGN KEY: `station_id` → `stations.id` ON DELETE CASCADE
- FOREIGN KEY: `task_schedule_id` → `task_schedules.id` ON DELETE SET NULL
- FOREIGN KEY: `completed_by` → `users.id` ON DELETE SET NULL

**Relationships**:
- `belongsTo`: station
- `belongsTo`: task_schedule
- `belongsTo`: completedBy (User)
- `hasMany`: completed_additional_tasks

---

### task_schedules

Defines recurring task patterns.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| name | varchar(255) | NOT NULL | Schedule name |
| description | text | NULL | Schedule description |
| station_id | bigint unsigned | NOT NULL, FOREIGN KEY | Reference to stations.id |
| frequency | enum | NOT NULL | 'daily', 'weekly', 'monthly', 'yearly' |
| interval | int | DEFAULT 1 | Frequency interval (e.g., every 2 weeks) |
| start_date | date | NOT NULL | When schedule starts |
| end_date | date | NULL | When schedule ends (null = indefinite) |
| days_of_week | json | NULL | For weekly: [0,1,2,3,4,5,6] |
| day_of_month | int | NULL | For monthly: 1-31 |
| month_of_year | int | NULL | For yearly: 1-12 |
| week_type | enum | NULL | 'even', 'odd', or null |
| active | boolean | DEFAULT true | Whether schedule is active |
| last_generated | date | NULL | Last date tasks were generated |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- INDEX: `station_id`
- INDEX: `active`
- INDEX: (`active`, `start_date`, `end_date`)
- FOREIGN KEY: `station_id` → `stations.id` ON DELETE CASCADE

**Relationships**:
- `belongsTo`: station
- `hasMany`: tasks

---

### task_templates

Reusable task templates for quick task creation.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| name | varchar(255) | NOT NULL | Template name |
| description | text | NULL | Template description |
| station_id | bigint unsigned | NULL, FOREIGN KEY | Default station (null = any) |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- INDEX: `station_id`
- FOREIGN KEY: `station_id` → `stations.id` ON DELETE SET NULL

**Relationships**:
- `belongsTo`: station

---

### time_logs

Tracks employee clock in/out times.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| user_id | bigint unsigned | NOT NULL, FOREIGN KEY | Reference to users.id |
| station_id | bigint unsigned | NOT NULL, FOREIGN KEY | Reference to stations.id |
| clock_in | timestamp | NOT NULL | Clock in time |
| clock_out | timestamp | NULL | Clock out time |
| duration_minutes | int | NULL | Calculated duration in minutes |
| notes | text | NULL | Optional notes |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- INDEX: `user_id`
- INDEX: `station_id`
- INDEX: `clock_in`
- INDEX: (`user_id`, `clock_in`)
- FOREIGN KEY: `user_id` → `users.id` ON DELETE CASCADE
- FOREIGN KEY: `station_id` → `stations.id` ON DELETE CASCADE

**Relationships**:
- `belongsTo`: user
- `belongsTo`: station

---

### inventory_items

Items that can be tracked in inventory.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| name | varchar(255) | NOT NULL | Item name |
| description | text | NULL | Item description |
| sku | varchar(100) | NULL, UNIQUE | Stock keeping unit |
| unit | varchar(50) | DEFAULT 'unit' | Unit of measurement |
| min_quantity | int | DEFAULT 0 | Minimum stock level |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- UNIQUE: `sku`

**Relationships**:
- `belongsToMany`: stations (via station_inventory)
- `hasMany`: inventory_transactions

---

### station_inventory (Pivot Table)

Tracks inventory quantities per station.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| station_id | bigint unsigned | NOT NULL, FOREIGN KEY | Reference to stations.id |
| inventory_item_id | bigint unsigned | NOT NULL, FOREIGN KEY | Reference to inventory_items.id |
| quantity | int | DEFAULT 0 | Current quantity |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- UNIQUE: (`station_id`, `inventory_item_id`)
- FOREIGN KEY: `station_id` → `stations.id` ON DELETE CASCADE
- FOREIGN KEY: `inventory_item_id` → `inventory_items.id` ON DELETE CASCADE

---

### inventory_transactions

Tracks all inventory movements.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| station_inventory_id | bigint unsigned | NOT NULL, FOREIGN KEY | Reference to station_inventory.id |
| user_id | bigint unsigned | NULL, FOREIGN KEY | User who made transaction |
| type | enum | NOT NULL | 'in', 'out', 'adjustment' |
| quantity | int | NOT NULL | Quantity changed |
| notes | text | NULL | Transaction notes |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- INDEX: `station_inventory_id`
- INDEX: `user_id`
- INDEX: `created_at`
- FOREIGN KEY: `station_inventory_id` → `station_inventory.id` ON DELETE CASCADE
- FOREIGN KEY: `user_id` → `users.id` ON DELETE SET NULL

**Relationships**:
- `belongsTo`: station_inventory
- `belongsTo`: user

---

### completed_additional_tasks

Tracks additional tasks completed beyond scheduled tasks.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| task_id | bigint unsigned | NULL, FOREIGN KEY | Reference to parent task |
| user_id | bigint unsigned | NOT NULL, FOREIGN KEY | User who completed |
| station_id | bigint unsigned | NOT NULL, FOREIGN KEY | Station where completed |
| name | varchar(255) | NOT NULL | Task name |
| description | text | NULL | Task description |
| completed_at | timestamp | NOT NULL | Completion timestamp |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- INDEX: `task_id`
- INDEX: `user_id`
- INDEX: `station_id`
- INDEX: `completed_at`
- FOREIGN KEY: `task_id` → `tasks.id` ON DELETE SET NULL
- FOREIGN KEY: `user_id` → `users.id` ON DELETE CASCADE
- FOREIGN KEY: `station_id` → `stations.id` ON DELETE CASCADE

**Relationships**:
- `belongsTo`: task
- `belongsTo`: user
- `belongsTo`: station

---

### employee_invitations

Manages employee invitation system.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| email | varchar(255) | NOT NULL | Invited email address |
| token | varchar(255) | NOT NULL, UNIQUE | Invitation token |
| invited_by | bigint unsigned | NOT NULL, FOREIGN KEY | Admin who sent invitation |
| accepted_at | timestamp | NULL | Acceptance timestamp |
| expires_at | timestamp | NOT NULL | Expiration timestamp |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- UNIQUE: `token`
- INDEX: `email`
- INDEX: `expires_at`
- FOREIGN KEY: `invited_by` → `users.id` ON DELETE CASCADE

**Relationships**:
- `belongsTo`: invitedBy (User)

---

### settings

Application-wide settings stored in database.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| key | varchar(255) | NOT NULL, UNIQUE | Setting key |
| value | text | NULL | Setting value (JSON or string) |
| description | text | NULL | Setting description |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY: `id`
- UNIQUE: `key`

**Common Settings**:
- `admin_requires_clock_in`: Whether admins must clock in
- `TASK_ROLLOVER_ENABLED`: Enable automatic overdue task rollover
- `app_name`: Application name override
- `default_timezone`: Default timezone

---

## Laravel System Tables

### password_reset_tokens

Stores password reset tokens.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| email | varchar(255) | PRIMARY KEY | Email address |
| token | varchar(255) | NOT NULL | Reset token |
| created_at | timestamp | NULL | Creation timestamp |

---

### sessions

Stores user sessions (if using database driver).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | varchar(255) | PRIMARY KEY | Session ID |
| user_id | bigint unsigned | NULL, INDEX | Associated user |
| ip_address | varchar(45) | NULL | Client IP |
| user_agent | text | NULL | Browser user agent |
| payload | longtext | NOT NULL | Session data |
| last_activity | int | INDEX | Last activity timestamp |

---

### cache / cache_locks

Stores cached data (if using database driver).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| key | varchar(255) | PRIMARY KEY | Cache key |
| value | mediumtext | NOT NULL | Cached value |
| expiration | int | NOT NULL | Expiration timestamp |

---

### jobs / failed_jobs

Queue system tables (if using database driver).

**jobs**:
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Job ID |
| queue | varchar(255) | NOT NULL, INDEX | Queue name |
| payload | longtext | NOT NULL | Job payload |
| attempts | tinyint unsigned | NOT NULL | Attempt count |
| reserved_at | int unsigned | NULL | Reserved timestamp |
| available_at | int unsigned | NOT NULL | Available timestamp |
| created_at | int unsigned | NOT NULL | Creation timestamp |

**failed_jobs**:
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PRIMARY KEY, AUTO_INCREMENT | Failed job ID |
| uuid | varchar(255) | UNIQUE | Job UUID |
| connection | text | NOT NULL | Connection name |
| queue | text | NOT NULL | Queue name |
| payload | longtext | NOT NULL | Job payload |
| exception | longtext | NOT NULL | Exception details |
| failed_at | timestamp | NOT NULL | Failure timestamp |

---

## Relationships Summary

### One-to-Many Relationships

- **User → TimeLogs**: One user has many time logs
- **User → EmployeeInvitations**: One admin can send many invitations
- **Station → Tasks**: One station has many tasks
- **Station → TimeLogs**: One station has many time logs
- **TaskSchedule → Tasks**: One schedule generates many tasks
- **Task → CompletedAdditionalTasks**: One task can have many additional tasks

### Many-to-Many Relationships

- **User ↔ Station**: Users can work at multiple stations, stations can have multiple users
- **Station ↔ InventoryItem**: Stations can have multiple inventory items with quantities

### Belongs-To Relationships

- **Task → Station**: Each task belongs to a station
- **Task → TaskSchedule**: Each task may belong to a schedule
- **Task → User** (completed_by): Each completed task references the user
- **TimeLog → User**: Each time log belongs to a user
- **TimeLog → Station**: Each time log belongs to a station

---

## Indexes and Constraints

### Performance Indexes

Key indexes for query optimization:

1. **Composite Indexes**:
   - `tasks(station_id, due_date, completed)` - Dashboard queries
   - `time_logs(user_id, clock_in)` - Time reports
   - `task_schedules(active, start_date, end_date)` - Schedule generation

2. **Foreign Key Indexes**:
   - All foreign key columns are indexed automatically

3. **Unique Constraints**:
   - `users.email`
   - `inventory_items.sku`
   - `employee_invitations.token`
   - `settings.key`
   - `station_user(station_id, user_id)`
   - `station_inventory(station_id, inventory_item_id)`

### Referential Integrity

All foreign keys use:
- **ON DELETE CASCADE**: For dependent records that should be deleted
- **ON DELETE SET NULL**: For optional references that should be preserved

---

## Migrations

Migrations are located in `database/migrations/` and use anonymous classes:

```php
// Example migration structure
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_name', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_name');
    }
};
```

### Running Migrations

```bash
# Run all pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Reset database (destructive)
php artisan migrate:fresh

# Reset and seed
php artisan migrate:fresh --seed
```

---

## Data Integrity Rules

### Business Rules Enforced by Database

1. **Users must have a role**: `role` enum with no null
2. **Tasks must belong to a station**: `station_id` NOT NULL with foreign key
3. **Time logs must have clock_in**: `clock_in` NOT NULL
4. **Task schedules must have valid frequency**: `frequency` enum
5. **Inventory quantities are integers**: Enforced by column type

### Application-Level Rules

1. **Email uniqueness**: Enforced by unique constraint
2. **Task completion requires user**: `completed_by` set when `completed = true`
3. **Schedule generation limits**: Controlled by `tasks:generate` command
4. **Inventory transactions update quantities**: Handled by model events

---

## Database Diagram (ERD)

```
Legend:
──────  One-to-Many
──────< Many-to-Many
──────> Belongs-To

                          ┌──────────────┐
                          │task_schedules│
                          └──────┬───────┘
                                 │ has many
                                 ▼
┌──────────┐            ┌────────────────┐           ┌──────────┐
│  users   │────────────│station_user   │───────────│ stations │
│          │<many-to-   │(pivot)        │   -to many>│          │
│  - id    │  -many     └───────────────┘            │  - id    │
│  - name  │                                          │  - name  │
│  - email │                                          └────┬─────┘
│  - role  │                                               │
└────┬─────┘                                               │ has many
     │                                                      ▼
     │ has many                                    ┌────────────────┐
     ▼                                             │     tasks      │
┌──────────┐                                       │  - id          │
│time_logs │                                       │  - name        │
│  - id    │                                       │  - station_id  │
│  - clock │                                       │  - due_date    │
│  - in/out│                                       │  - completed   │
└──────────┘                                       └────────────────┘


┌──────────────┐    ┌──────────────────┐    ┌───────────────┐
│   stations   │────│station_inventory │────│inventory_items│
│              │<───│(pivot with qty)  │───>│               │
└──────────────┘    └────────┬─────────┘    └───────────────┘
                             │
                             │ has many
                             ▼
                    ┌──────────────────────┐
                    │inventory_transactions│
                    └──────────────────────┘
```

---

## Next Steps

- Review [Architecture](architecture.md) for system design
- Check [API Reference](api-reference.md) for model methods
- See [Development Guide](development.md) for migration practices

---

**Navigation**: [← Architecture](architecture.md) | [Back to Documentation](README.md) | [Features →](features.md)
