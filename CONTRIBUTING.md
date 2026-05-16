# Contributing to Goralys

Thank you for your interest in contributing to Goralys! This document provides guidelines for contributing to the project.

## How to Contribute

### Pull Requests

1. Fork the repository and create your branch from `develop`
2. Set up the project: `scripts/setup.bat` or on Linux: `scripts/setup.sh`
3. Make your changes following the coding conventions below
4. Write clear commit messages
5. Update documentation as needed
6. Submit a pull request to the `develop` branch

_Note: Never create a pull request to the `main` branch, all pull requests to `main` will be closed._

#### Coding Conventions

- Follow PER coding style for PHP code
- Use four spaces for indentation (no tabs)
- Use camelCase naming for variable and function
- Use lowercase SQL queries
- Add comments for complex logic
- Always write the meaning of the HTTP codes in PHP
- Write descriptive variable/function names
- Write code in English

##### Function Declaration Style

- **Inside React components:** Use arrow function syntax
```tsx
  const MyComponent = () => {
    const handleClick = () => { /* ... */ };
    return <button onClick={handleClick}>Click</button>;
  };
```

- **Outside components (utils, services, etc.):** Use function declaration
```tsx
  function calculateTotal(items) {
    return items.reduce((sum, item) => sum + item.price, 0);
  }
```

#### Security Guidelines

- Never commit sensitive credentials (use `.env` for local testing)
- Use prepared statements for database queries (via the `DbContainer`)
- Implement CSRF protection for forms

### File Organization

New code should follow the existing project structure:

```
goralys/
├── app/                   # Next.js frontend pages and components
│   ├── hooks/             # Custom React hooks
│   ├── lib/               # Utility functions and types
│   ├── ui/                # UI components
├── backend/               # Backend PHP files (Symfony-like structure)
│   ├── API/               # API endpoints
│   ├── src/               # Core backend logic source code (Kernel, Core, Shared)
│   ├── tests/             # Backend tests
│   └── vendor/            # Composer dependencies
├── public/                # Static assets (images, fonts)
├── scripts/               # Helper scripts (seeding, etc.)
└── .env                   # Environment configuration file
```

### Documentation

- Update README.md for significant changes
- Keep code comments current
- Update database schema in `backend/data_structure.sql` if needed

### Testing

Before submitting a PR:

1. Test your changes locally
2. Verify CSRF protection works
3. Check form validation
4. Test database operations
5. Verify frontend interactions
6. Test with different user roles (student/teacher/admin)

### Development Environment

Recommended setup:

- PHP 8.5+
- MariaDB
- Composer
- pnpm
- IDE with PHP and Next.js support (VS Code, PhpStorm, etc.)

_Note: I personally recommend PhpStorm for IDE if you can afford it as it integrates seamlessly with a local server and database._

### Getting Help

If you need help:

1. Check the [README.md](README.md)
2. Look through existing issues
3. Open a new issue with your question

## License

By contributing, you agree that your contributions will be licensed under the project's [AGPL-3.0 LICENSE](LICENSE).

## Acknowledgements

Thanks to all contributors who help improve Goralys!
