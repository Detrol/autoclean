# User Guide

Complete guide for using AutoClean - for both administrators and employees.

## Table of Contents
- [Getting Started](#getting-started)
- [Admin Guide](#admin-guide)
- [Employee Guide](#employee-guide)
- [Common Tasks](#common-tasks)
- [Tips and Best Practices](#tips-and-best-practices)
- [Troubleshooting](#troubleshooting)

## Getting Started

### Logging In

1. Navigate to your AutoClean URL (e.g., https://yourdomain.com)
2. Enter your email address and password
3. Click "Log In"

**First-Time Login**:
- Check your email for an invitation link
- Click the invitation link
- Set your password
- Log in with your new credentials

### Dashboard Overview

After logging in, you'll see your dashboard:

**Admin Dashboard**:
- System overview
- All stations and tasks
- User management
- Settings access

**Employee Dashboard**:
- Today's tasks
- Clock in/out
- Assigned stations
- Time reports

---

## Admin Guide

### Managing Stations

Stations represent physical locations or work areas (e.g., "Building A", "Floor 3", "Kitchen").

#### Creating a Station

1. Go to **Admin → Stations**
2. Click **"Create Station"**
3. Fill in details:
   - **Name**: Descriptive name (e.g., "Main Office - 3rd Floor")
   - **Description**: Optional detailed description
4. Assign users to the station
5. Click **"Save"**

#### Editing a Station

1. Go to **Admin → Stations**
2. Click **"Edit"** next to the station
3. Update details or user assignments
4. Click **"Save"**

#### Deactivating a Station

1. Go to **Admin → Stations**
2. Click **"Edit"** next to the station
3. Uncheck **"Active"**
4. Click **"Save"**

*Note: Deactivated stations won't appear in dropdowns but retain all historical data.*

---

### Managing Tasks

#### Creating a One-Time Task

1. Go to **Admin → Tasks**
2. Click **"Create Task"**
3. Select **"One-Time Task"**
4. Fill in:
   - **Name**: Task description (e.g., "Clean conference room")
   - **Station**: Where the task occurs
   - **Due Date**: When it should be completed
   - **Description**: Optional detailed instructions
5. Click **"Save"**

#### Creating a Recurring Task Schedule

1. Go to **Admin → Task Schedules**
2. Click **"Create Schedule"**
3. Fill in basic details:
   - **Name**: Schedule name (e.g., "Daily Restroom Cleaning")
   - **Station**: Assigned location
   - **Start Date**: When schedule begins
   - **End Date**: When schedule ends (optional)

4. Select **Frequency**:

   **Daily Tasks**:
   - Every day
   - Or weekdays only (Monday-Friday)

   **Weekly Tasks**:
   - Select specific days (e.g., Monday, Wednesday, Friday)
   - Optional: Even/odd weeks only

   **Monthly Tasks**:
   - Specific day number (e.g., "1st of every month")
   - Or specific weekday (e.g., "2nd Tuesday", "Last Friday")

   **Yearly Tasks**:
   - Specific date (e.g., "June 15th every year")

5. Click **"Save"**

The system automatically generates individual tasks based on this schedule.

#### Examples

**Daily Cleaning (Weekdays Only)**:
- Frequency: Daily
- Days of Week: Monday, Tuesday, Wednesday, Thursday, Friday

**Bi-Weekly Deep Clean (Every Other Monday)**:
- Frequency: Weekly
- Days of Week: Monday
- Week Type: Even (or Odd)

**Monthly Inspection (First Day of Month)**:
- Frequency: Monthly
- Day of Month: 1

**Quarterly Review (First Monday of Quarter)**:
- Create 4 yearly tasks for: Jan 1st Monday, Apr 1st Monday, Jul 1st Monday, Oct 1st Monday
- Or use monthly with "1st Monday" every 3 months

---

### Managing Users

#### Inviting an Employee

1. Go to **Admin → Users**
2. Click **"Invite Employee"**
3. Enter email address
4. Click **"Send Invitation"**
5. Employee receives email with invitation link
6. They set password and can log in

#### Assigning Users to Stations

**Method 1: During Station Creation/Edit**:
1. Edit a station
2. Select users from the list
3. Save

**Method 2: During User Management**:
1. Go to **Admin → Users**
2. Click **"Edit"** next to user
3. Select assigned stations
4. Save

#### Promoting to Admin

1. Go to **Admin → Users**
2. Find the user
3. Click **"Edit"**
4. Change role to **"Admin"**
5. Save

*Warning: Admins have full system access.*

---

### Managing Inventory

#### Adding Inventory Items

1. Go to **Admin → Inventory**
2. Click **"Create Item"**
3. Fill in:
   - **Name**: Item name (e.g., "Paper Towels")
   - **SKU**: Stock keeping unit (optional)
   - **Unit**: Measurement (e.g., "roll", "bottle", "box")
   - **Min Quantity**: Minimum stock level for alerts
4. Click **"Save"**

#### Assigning Inventory to Stations

1. Go to **Admin → Stations**
2. Click **"Inventory"** next to a station
3. Click **"Add Item"**
4. Select item and set initial quantity
5. Click **"Save"**

#### Recording Inventory Transactions

**Adding Stock**:
1. Go to station's inventory
2. Click **"Add Stock"** next to item
3. Enter quantity
4. Add notes (optional)
5. Save

**Using/Removing Stock**:
1. Go to station's inventory
2. Click **"Remove Stock"**
3. Enter quantity used
4. Add notes (optional)
5. Save

**Adjusting Stock** (corrections):
1. Use "Adjust Stock"
2. Enter positive or negative adjustment
3. Add reason in notes
4. Save

---

### Task Templates

#### Creating Templates

1. Go to **Admin → Task Templates**
2. Click **"Create Template"**
3. Fill in:
   - **Name**: Template name
   - **Description**: Task instructions
   - **Default Station**: Optional
4. Save

#### Using Templates

When creating a task:
1. Click **"From Template"**
2. Select template
3. Form auto-fills
4. Adjust as needed
5. Save

---

### System Settings

1. Go to **Admin → Settings**
2. Configure:
   - **Admin Requires Clock In**: Whether admins must clock in
   - **Task Rollover Enabled**: Automatically move overdue tasks to today
   - **Application Name**: Custom app name
   - **Timezone**: System timezone
3. Click **"Save Settings"**

---

### Viewing Time Logs

1. Go to **Admin → Time Logs**
2. Filter by:
   - Date range
   - User
   - Station
3. View detailed time entries
4. Edit if corrections needed
5. Export report (PDF)

---

## Employee Guide

### Dashboard Overview

Your dashboard shows:
- **Today's Tasks**: Tasks due today at your assigned stations
- **Overdue Tasks**: Past-due tasks you need to complete
- **Clock In/Out**: Quick time tracking
- **Recent Activity**: Your recent task completions

---

### Clocking In and Out

#### Clock In

1. Go to **Dashboard**
2. Select your station from dropdown
3. Click **"Clock In"**
4. Confirmation message appears

*You're now clocked in at that station.*

#### Clock Out

1. Click **"Clock Out"** button
2. Optional: Add notes about your shift
3. Click **"Confirm Clock Out"**

*Your shift time is automatically calculated.*

**Important**:
- You can only be clocked in at one station at a time
- You must clock out before clocking in at another station
- Admins can adjust time logs if you forget to clock out

---

### Completing Tasks

#### Mark Task Complete

1. View your task list on dashboard
2. When task is physically completed
3. Click **"Mark Complete"** next to the task
4. Task moves to completed list
5. Completion is recorded with your name and timestamp

#### Adding Additional Tasks

If you completed work not on the task list:

1. Click **"Add Additional Task"**
2. Enter task name and description
3. Click **"Save"**

This logs extra work performed beyond scheduled tasks.

---

### Viewing Time Reports

1. Go to **Time Reports**
2. Select date range
3. Click **"Generate Report"**
4. View summary:
   - Total hours worked
   - Number of shifts
   - Average shift duration
   - Detailed shift list
5. Click **"Export PDF"** to download

Use time reports for:
- Weekly timesheets
- Monthly hour summaries
- Payroll verification

---

## Common Tasks

### Scenario: Setting Up Daily Cleaning

**Goal**: Restroom cleaning every weekday

**Steps**:
1. Create station: "Restroom - 2nd Floor"
2. Assign cleaning staff to station
3. Create task schedule:
   - Name: "Daily Restroom Cleaning"
   - Frequency: Daily
   - Days: Mon-Fri
   - Station: "Restroom - 2nd Floor"
4. Add task template for consistency:
   - Name: "Restroom Cleaning Checklist"
   - Description: "1. Clean sinks\n2. Clean toilets\n3. Mop floors\n4. Restock supplies"

**Result**: Tasks automatically appear every weekday for assigned employees.

---

### Scenario: Monthly Deep Clean

**Goal**: Comprehensive cleaning on first Saturday of each month

**Steps**:
1. Create task schedule:
   - Name: "Monthly Deep Clean"
   - Frequency: Monthly
   - Week of Month: 1st
   - Day of Week: Saturday
   - Station: Select station

**Result**: Task generated for 1st Saturday of every month.

---

### Scenario: Employee Workflow

**Daily Routine**:
1. Log in to AutoClean
2. View today's tasks on dashboard
3. Clock in at station
4. Complete physical tasks
5. Mark each task complete in system
6. Log any additional tasks performed
7. Clock out when finished
8. Add notes if needed

**Weekly**:
1. Generate time report
2. Review hours worked
3. Export PDF for records

---

## Tips and Best Practices

### For Admins

**Station Setup**:
- ✅ Use clear, descriptive names
- ✅ Group by physical location or department
- ✅ Assign relevant users immediately
- ✅ Deactivate instead of delete for historical data

**Task Scheduling**:
- ✅ Use task templates for consistency
- ✅ Test recurrence patterns on a single schedule first
- ✅ Review generated tasks regularly
- ✅ Set realistic due dates
- ✅ Use task descriptions for detailed instructions

**User Management**:
- ✅ Assign users to appropriate stations only
- ✅ Grant admin role sparingly
- ✅ Use employee invitations for secure onboarding
- ✅ Review user assignments quarterly

**Inventory Management**:
- ✅ Set minimum quantities for low-stock alerts
- ✅ Record all stock movements
- ✅ Use notes field for transaction details
- ✅ Regular physical inventory counts

---

### For Employees

**Time Tracking**:
- ✅ Clock in immediately when starting work
- ✅ Don't forget to clock out
- ✅ Add notes for unusual shifts
- ✅ Review time logs weekly for accuracy

**Task Completion**:
- ✅ Mark tasks complete only when fully done
- ✅ Log additional tasks performed
- ✅ Check dashboard at start of shift
- ✅ Complete overdue tasks first

**Communication**:
- ✅ Use task notes for issues
- ✅ Report low inventory to admin
- ✅ Notify admin of equipment problems

---

## Troubleshooting

### I Can't Log In

**Check**:
- Email address is correct
- Password is correct (case-sensitive)
- Account has been created/invited

**Solution**:
- Use "Forgot Password" link
- Contact admin to resend invitation
- Clear browser cache and try again

---

### I Don't See Any Tasks

**Possible Reasons**:
- No tasks scheduled for today
- Not assigned to any stations
- Tasks not generated by system yet

**Solution**:
- Contact admin to verify station assignment
- Admin should check task schedules are active
- Admin should run: `php artisan tasks:generate`

---

### I Can't Clock In

**Possible Reasons**:
- Already clocked in somewhere else
- Not assigned to selected station

**Solution**:
- Clock out from previous station first
- Contact admin to verify station assignment
- Refresh page and try again

---

### I Forgot to Clock Out

**Solution**:
- Contact admin
- Admin can edit time log with correct clock-out time
- Go to: Admin → Time Logs → Edit entry

---

### Time Report Shows Wrong Hours

**Check**:
- Date range is correct
- Clock in/out times are accurate
- No duplicate entries

**Solution**:
- Admin can review and edit time logs
- Generate new report after corrections

---

### Low Stock Alert Not Showing

**Check**:
- Minimum quantity is set on inventory item
- Current quantity is below minimum

**Solution**:
- Admin should verify inventory item settings
- Update stock quantities

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl + K` | Open search (if enabled) |
| `Esc` | Close modal dialogs |
| `Tab` | Navigate form fields |

*Note: Most actions require clicking for security.*

---

## Getting Help

### In-App Help
- Hover over ⓘ icons for tooltips
- Check field descriptions
- Review error messages

### Contact Support
- Email: support@yourdomain.com
- Report bugs via GitHub Issues
- Check documentation: https://docs.yourdomain.com

### Training Resources
- Video tutorials (if available)
- User onboarding guides
- Admin training materials

---

## Next Steps

**For New Admins**:
1. Complete [Installation](installation.md)
2. Review [Configuration](configuration.md)
3. Read [Features Guide](features.md)

**For Developers**:
1. Check [Development Guide](development.md)
2. Review [API Reference](api-reference.md)
3. Read [Contributing Guidelines](contributing.md)

---

**Navigation**: [← Deployment](deployment.md) | [Back to Documentation](README.md)
