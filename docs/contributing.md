# Contributing to AutoClean

Thank you for considering contributing to AutoClean! This document provides guidelines and instructions for contributing.

## Table of Contents
- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)
- [Issue Guidelines](#issue-guidelines)

## Code of Conduct

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

**Positive behavior includes**:
- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

**Unacceptable behavior includes**:
- Trolling, insulting/derogatory comments, and personal attacks
- Public or private harassment
- Publishing others' private information without permission
- Other conduct which could reasonably be considered inappropriate

### Enforcement

Project maintainers are responsible for clarifying standards of acceptable behavior and will take appropriate action in response to any instances of unacceptable behavior.

---

## How Can I Contribute?

### Reporting Bugs

Before submitting a bug report:
- Check existing issues to avoid duplicates
- Gather information about the bug
- Verify the bug in the latest version

**Good bug reports include**:
- Clear, descriptive title
- Exact steps to reproduce
- Expected vs. actual behavior
- Screenshots (if applicable)
- Environment details (PHP version, OS, browser)

**Use the bug report template** when creating a new issue.

---

### Suggesting Enhancements

Enhancement suggestions are welcome! Before suggesting:
- Check if the enhancement already exists
- Determine if it fits the project scope
- Provide detailed explanation of the use case

**Good enhancement suggestions include**:
- Clear use case description
- Benefits to users
- Potential implementation approach
- Examples or mockups

**Use the feature request template** when creating a new issue.

---

### Contributing Code

We welcome code contributions! Here's how:

1. **Find an issue to work on**:
   - Look for issues labeled `good first issue` or `help wanted`
   - Comment on the issue to claim it
   - Wait for maintainer approval before starting

2. **Propose new features**:
   - Open an issue first to discuss
   - Get maintainer approval before implementing
   - Large features may require design discussion

3. **Fix bugs**:
   - Small bug fixes can be submitted directly
   - For complex bugs, discuss approach first

---

### Improving Documentation

Documentation improvements are highly valued!

**Documentation contributions include**:
- Fixing typos or clarifying content
- Adding examples
- Improving readability
- Translating documentation
- Adding missing documentation

**Documentation locations**:
- `/docs/*.md` - Main documentation
- `README.md` - Project overview
- Code comments and PHPDoc
- Inline Blade comments

---

## Getting Started

### Development Setup

1. **Fork the repository**
   ```bash
   # Click "Fork" on GitHub
   git clone https://github.com/YOUR_USERNAME/autoclean.git
   cd autoclean
   ```

2. **Add upstream remote**
   ```bash
   git remote add upstream https://github.com/ORIGINAL_OWNER/autoclean.git
   ```

3. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

4. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Create database**
   ```bash
   # Configure .env with database credentials
   php artisan migrate:fresh --seed
   ```

6. **Start development server**
   ```bash
   composer dev
   ```

**Detailed setup**: See [Development Guide](development.md)

---

## Development Workflow

### Branch Strategy

- `main` - Production-ready code
- `develop` - Development branch (if using Git Flow)
- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Urgent production fixes

### Creating a Feature Branch

```bash
# Update your fork
git checkout main
git pull upstream main

# Create feature branch
git checkout -b feature/your-feature-name

# Make changes, commit, push
git add .
git commit -m "Add: your feature"
git push origin feature/your-feature-name
```

### Keeping Your Fork Updated

```bash
# Fetch upstream changes
git fetch upstream

# Merge into your main branch
git checkout main
git merge upstream/main

# Update your feature branch
git checkout feature/your-feature-name
git rebase main
```

---

## Coding Standards

AutoClean follows Laravel and PHP best practices.

### PHP Code Style

- **PSR-12 Coding Standard**
- **Laravel conventions**
- **Use Laravel Pint for formatting**

```bash
# Format all code
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test

# Format specific file
./vendor/bin/pint app/Models/Task.php
```

### Naming Conventions

**Classes**:
```php
// Models: Singular, PascalCase
class Task extends Model {}

// Livewire: Nested namespaces
class Admin\Tasks\Create extends Component {}

// Services: Descriptive name
class RecurrenceCalculator {}
```

**Methods**:
```php
// camelCase
public function calculateNextOccurrence() {}
public function getTodayTasks() {}
```

**Variables**:
```php
// camelCase
$taskSchedule = TaskSchedule::find($id);
$completedTasks = Task::completed()->get();
```

**Database**:
```bash
# Tables: plural, snake_case
tasks, task_schedules, time_logs

# Columns: snake_case
due_date, completed_at, station_id
```

### Code Quality

**Required**:
- ✅ All tests must pass
- ✅ Code must be formatted with Pint
- ✅ No debug code (dd(), dump(), console.log())
- ✅ PHPDoc comments for public methods
- ✅ Type hints for parameters and returns

**Recommended**:
- Single Responsibility Principle
- DRY (Don't Repeat Yourself)
- Clear, descriptive names
- Minimal complexity

### Writing Tests

All new features must include tests.

```php
// Feature test example
test('admin can create task schedule', function () {
    actingAsAdmin();

    $station = Station::factory()->create();

    Livewire::test(Create::class)
        ->set('name', 'Daily Cleaning')
        ->set('station_id', $station->id)
        ->set('frequency', 'daily')
        ->call('save')
        ->assertRedirect();

    $this->assertDatabaseHas('task_schedules', [
        'name' => 'Daily Cleaning',
    ]);
});
```

See [Testing Guide](testing.md) for details.

---

## Commit Guidelines

### Commit Message Format

```
Type: Brief description (max 50 chars)

More detailed explanation if needed (wrap at 72 chars).
Explain the problem this commit solves and why this approach was chosen.

References: #issue-number
```

### Commit Types

- `Add`: New feature or capability
- `Fix`: Bug fix
- `Update`: Modification to existing feature
- `Refactor`: Code restructuring without behavior change
- `Docs`: Documentation changes
- `Test`: Adding or modifying tests
- `Style`: Code formatting (no logic change)
- `Chore`: Maintenance tasks (dependencies, config)

### Examples

**Good commits**:
```
Add: Task template management system

Implements CRUD operations for task templates allowing admins to create
reusable task definitions. Templates can be used when creating new tasks
to save time and ensure consistency.

References: #42
```

```
Fix: Clock out validation error

Fixes issue where employees could clock in at multiple stations
simultaneously. Added validation to check for active time logs
before allowing new clock-in.

Closes: #87
```

**Bad commits**:
```
fixed stuff
```

```
WIP
```

```
asdfasdf
```

### Commit Hygiene

- **Make atomic commits**: Each commit should represent one logical change
- **Commit often**: Don't wait until everything is perfect
- **Write clear messages**: Future you will thank you
- **Reference issues**: Use `Fixes #123` or `References #456`

---

## Pull Request Process

### Before Submitting

**Checklist**:
- [ ] Tests pass: `composer test`
- [ ] Code formatted: `./vendor/bin/pint`
- [ ] Documentation updated
- [ ] CHANGELOG.md updated (for significant changes)
- [ ] No merge conflicts with main branch
- [ ] Commit messages follow guidelines
- [ ] Self-review completed

### Creating a Pull Request

1. **Push your branch**
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Open PR on GitHub**
   - Click "New Pull Request"
   - Select your branch
   - Fill in PR template
   - Link related issues

3. **Complete PR template**
   - Description of changes
   - Type of change (bug fix, new feature, etc.)
   - Testing performed
   - Screenshots (if UI changes)

4. **Request review**
   - PR will be automatically assigned to maintainers
   - Address review comments
   - Keep PR updated with main branch

### PR Title Format

Use the same format as commit messages:

```
Add: Task template management
Fix: Clock out validation error
Update: Improve recurrence calculation performance
```

### PR Description Template

```markdown
## Description
Brief description of what this PR does.

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
Describe the testing performed.

## Screenshots (if applicable)
Add screenshots for UI changes.

## Related Issues
Fixes #123
References #456

## Checklist
- [ ] Tests pass
- [ ] Code formatted
- [ ] Documentation updated
- [ ] Self-reviewed
```

### Review Process

1. **Automated checks run**: CI/CD pipeline
2. **Code review**: Maintainer reviews code
3. **Revisions**: Address comments and push updates
4. **Approval**: Maintainer approves PR
5. **Merge**: Maintainer merges PR

**Timeline**: Most PRs reviewed within 1-3 business days.

---

## Issue Guidelines

### Creating Issues

**Use templates** for:
- Bug reports
- Feature requests
- Documentation improvements

**Before creating an issue**:
1. Search existing issues
2. Check documentation
3. Verify the latest version

### Issue Labels

- `bug` - Something isn't working
- `enhancement` - New feature request
- `documentation` - Documentation improvements
- `good first issue` - Good for newcomers
- `help wanted` - Extra attention needed
- `question` - Further information requested
- `wontfix` - This will not be worked on
- `duplicate` - Duplicate of existing issue

### Issue Lifecycle

1. **Open**: Issue created
2. **Triaged**: Maintainer reviews and labels
3. **In Progress**: Someone is working on it
4. **Closed**: Issue resolved or declined

---

## Development Best Practices

### Security

- Never commit secrets (.env, credentials)
- Validate all user input
- Use parameterized queries (Eloquent handles this)
- Implement CSRF protection (Laravel handles this)
- Follow OWASP Top 10 guidelines

### Performance

- Use eager loading to avoid N+1 queries
- Cache expensive operations
- Use indexes on database columns
- Minimize query count
- Profile slow pages

### Accessibility

- Use semantic HTML
- Include ARIA labels
- Ensure keyboard navigation
- Maintain color contrast
- Test with screen readers

### Mobile Responsiveness

- Test on mobile devices
- Use responsive Tailwind classes
- Optimize touch targets
- Test portrait and landscape

---

## Getting Help

### Resources

- **Documentation**: `/docs` directory
- **Development Guide**: [development.md](development.md)
- **API Reference**: [api-reference.md](api-reference.md)
- **Testing Guide**: [testing.md](testing.md)

### Communication

- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: Questions and community help
- **Pull Request Comments**: Code review discussions

### Maintainers

Current maintainers:
- @maintainer1
- @maintainer2

*Response time: Usually within 1-3 business days*

---

## Recognition

Contributors are recognized in:
- Project README
- Release notes
- Git commit history

Thank you for contributing to AutoClean! 🎉

---

**Navigation**: [← User Guide](user-guide.md) | [Back to Documentation](README.md)
