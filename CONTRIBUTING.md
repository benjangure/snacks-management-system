# Contributing to Mandazi Management System

Thank you for considering contributing to the Mandazi Management System! 

## 🚀 Getting Started

### Prerequisites
- PHP 8.1+
- Composer
- MySQL (XAMPP recommended)
- Git

### Development Setup
1. Fork the repository
2. Clone your fork: `git clone https://github.com/yourusername/mandazi-management-system.git`
3. Run setup: `start-mandazi-system.bat` (Windows) or follow manual setup in README
4. Create a feature branch: `git checkout -b feature/your-feature-name`

## 📝 Development Guidelines

### Code Style
- **Backend**: Follow PSR-12 coding standards
- **Frontend**: Use consistent JavaScript ES6+ syntax
- **Comments**: Document complex logic
- **Naming**: Use descriptive variable and function names

### Commit Messages
Use conventional commit format:
```
feat: add user authentication
fix: resolve payment callback issue
docs: update installation guide
style: improve button hover effects
```

### Testing
- Test all new features thoroughly
- Use the included testing tools
- Ensure M-Pesa integration works in both real and simulation modes
- Test on multiple browsers and devices

## 🐛 Bug Reports

When reporting bugs, please include:
- **Environment**: OS, PHP version, browser
- **Steps to reproduce**: Detailed steps
- **Expected behavior**: What should happen
- **Actual behavior**: What actually happens
- **Screenshots**: If applicable
- **Logs**: Relevant Laravel logs

## 💡 Feature Requests

For new features:
- **Use case**: Why is this feature needed?
- **Proposed solution**: How should it work?
- **Alternatives**: Other ways to solve the problem
- **Additional context**: Screenshots, mockups, etc.

## 🔄 Pull Request Process

1. **Update documentation** if needed
2. **Add tests** for new functionality
3. **Ensure code quality** (no syntax errors)
4. **Test thoroughly** on your local environment
5. **Update README** if adding new features
6. **Submit PR** with clear description

### PR Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tested locally
- [ ] M-Pesa integration tested
- [ ] UI tested on multiple browsers
- [ ] Mobile responsiveness verified

## Screenshots
(If applicable)
```

## 🏗️ Architecture Guidelines

### Backend (Laravel)
- Use proper MVC structure
- Implement proper validation
- Use Eloquent relationships
- Follow RESTful API conventions
- Add comprehensive logging

### Frontend (Vanilla JS)
- Keep JavaScript modular
- Use modern ES6+ features
- Maintain separation of concerns
- Ensure accessibility compliance
- Implement proper error handling

### Database
- Use migrations for schema changes
- Add proper indexes
- Use foreign key constraints
- Follow naming conventions

## 🧪 Testing Guidelines

### Manual Testing
- Use `demo-login.html` for cache-free testing
- Test all user flows (buyer and seller)
- Verify M-Pesa integration
- Test on different screen sizes

### Callback Testing
- Use `test-callback.html` for M-Pesa callbacks
- Test success, failure, and timeout scenarios
- Verify database updates
- Check log entries

## 📚 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [M-Pesa Daraja API](https://developer.safaricom.co.ke/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [JavaScript MDN](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

## 🤝 Community

- **Issues**: Use GitHub Issues for bug reports and feature requests
- **Discussions**: Use GitHub Discussions for questions and ideas
- **Code Review**: All PRs require review before merging

## 📄 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

**Thank you for contributing to the Mandazi Management System! 🎉**