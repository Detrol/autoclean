# Pull Request

## Description
Provide a clear and concise description of what this PR does.

## Type of Change
Please select the type of change:
- [ ] 🐛 Bug fix (non-breaking change which fixes an issue)
- [ ] ✨ New feature (non-breaking change which adds functionality)
- [ ] 💥 Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] 📝 Documentation update
- [ ] 🎨 Code style update (formatting, renaming)
- [ ] ♻️ Refactoring (no functional changes)
- [ ] ⚡ Performance improvement
- [ ] ✅ Test update
- [ ] 🔧 Build/config update

## Related Issues
Link related issues here:
- Fixes #(issue number)
- References #(issue number)
- Closes #(issue number)

## Changes Made
List the key changes made in this PR:
- Change 1
- Change 2
- Change 3

## Testing Performed
Describe the testing you performed:
- [ ] All existing tests pass (`composer test`)
- [ ] New tests added for new functionality
- [ ] Manual testing performed
- [ ] Tested on multiple browsers (if UI changes)
- [ ] Tested on mobile devices (if UI changes)

**Test Details**:
Describe specific test scenarios covered:
```
Example: Tested task creation with various recurrence patterns
- Daily tasks
- Weekly tasks with even/odd weeks
- Monthly tasks
```

## Screenshots (if applicable)
Add screenshots or GIFs for UI changes:

**Before**:
<!-- Add screenshot of before state -->

**After**:
<!-- Add screenshot of after state -->

## Database Changes
- [ ] Includes new migrations
- [ ] Migration rollback tested
- [ ] Seeders updated (if needed)
- [ ] No database changes

**Migration Details**:
<!-- Describe any database changes -->

## Documentation
- [ ] Documentation updated in `/docs`
- [ ] README updated (if needed)
- [ ] CHANGELOG.md updated
- [ ] Code comments added for complex logic
- [ ] API documentation updated (if applicable)
- [ ] No documentation changes needed

## Code Quality Checklist
- [ ] Code follows the project's coding standards
- [ ] Code formatted with Laravel Pint (`./vendor/bin/pint`)
- [ ] No debug code left (dd(), dump(), console.log())
- [ ] PHPDoc comments added for public methods
- [ ] Type hints added for parameters and returns
- [ ] Validation rules added for user inputs
- [ ] Security considerations addressed
- [ ] Performance considerations addressed

## Breaking Changes
Does this PR introduce breaking changes?
- [ ] Yes (describe below)
- [ ] No

**If yes, describe the breaking changes and migration path**:
<!-- Describe breaking changes and how to migrate -->

## Deployment Notes
Any special deployment considerations:
- [ ] Requires environment variable changes
- [ ] Requires new dependencies (`composer install` / `npm install`)
- [ ] Requires cache clearing
- [ ] Requires queue worker restart
- [ ] Requires database migration
- [ ] No special deployment steps

**Deployment Instructions**:
```bash
# Add any special deployment commands here
```

## Additional Notes
Add any other notes or context about the PR here.

## Reviewer Checklist
For reviewers:
- [ ] Code follows project standards
- [ ] Tests are adequate and passing
- [ ] Documentation is updated
- [ ] No security concerns
- [ ] No performance concerns
- [ ] Breaking changes are documented
- [ ] Migration path is clear (if breaking changes)

---

**By submitting this PR, I confirm that**:
- [ ] I have read the [Contributing Guidelines](../docs/contributing.md)
- [ ] My code follows the project's coding standards
- [ ] I have performed a self-review of my code
- [ ] I have commented my code where necessary
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes
