# Contributing to AutoClean

Thank you for your interest in contributing to AutoClean! This guide will help you get started.

## Quick Links

- **Full Contributing Guide**: [docs/contributing.md](../docs/contributing.md)
- **Development Guide**: [docs/development.md](../docs/development.md)
- **Testing Guide**: [docs/testing.md](../docs/testing.md)
- **Code of Conduct**: See below

## How to Contribute

1. **Report Bugs**: Use the [bug report template](.github/ISSUE_TEMPLATE/bug_report.md)
2. **Suggest Features**: Use the [feature request template](.github/ISSUE_TEMPLATE/feature_request.md)
3. **Submit Code**: Follow the [pull request process](../docs/contributing.md#pull-request-process)
4. **Improve Documentation**: PRs for documentation improvements are always welcome

## Quick Start for Developers

```bash
# 1. Fork and clone
git clone https://github.com/YOUR_USERNAME/autoclean.git
cd autoclean

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed

# 4. Start development
composer dev

# 5. Run tests
composer test

# 6. Format code
./vendor/bin/pint
```

## Code Standards

- **PHP**: PSR-12, Laravel conventions
- **Formatting**: Laravel Pint
- **Testing**: Pest PHP (all features must include tests)
- **Commits**: Conventional commits format

**Example commit**:
```
Add: Task template management

Implements CRUD operations for task templates allowing admins
to create reusable task definitions.

Closes #42
```

## Pull Request Process

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Make your changes and commit
3. Ensure tests pass: `composer test`
4. Format code: `./vendor/bin/pint`
5. Push and create a PR
6. Fill out the PR template completely
7. Wait for review and address feedback

## Before Submitting

**Checklist**:
- [ ] Tests pass
- [ ] Code formatted with Pint
- [ ] Documentation updated
- [ ] No debug code (dd(), dump())
- [ ] Commit messages follow guidelines
- [ ] Self-review completed

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive environment for all contributors.

### Our Standards

**Positive behaviors**:
- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what is best for the community

**Unacceptable behaviors**:
- Trolling, insulting comments, and personal attacks
- Public or private harassment
- Publishing others' private information without permission

### Enforcement

Violations may result in temporary or permanent removal from the project.

## Getting Help

- **Documentation**: Check the `/docs` directory
- **Issues**: Search existing issues or create a new one
- **Discussions**: Use GitHub Discussions for questions

## First Time Contributors

Look for issues labeled:
- `good first issue` - Good for newcomers
- `help wanted` - Extra attention needed
- `documentation` - Documentation improvements

## Recognition

All contributors are recognized in:
- Project README
- Release notes
- Git commit history

Thank you for contributing! 🎉

---

For detailed contributing guidelines, see [docs/contributing.md](../docs/contributing.md)
