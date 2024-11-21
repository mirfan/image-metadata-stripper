# Contributing to MetaStrip Image

First off, thank you for considering contributing to MetaStrip Image! It's people like you that make this library better for everyone.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone git@github.com:your-username/image.git`
3. Create your feature branch: `git checkout -b feature/amazing-feature`
4. Install dependencies: `composer install`

## Development Process

1. Write your code
2. Add or update tests as needed
3. Update documentation if required
4. Run all checks before committing:
   ```bash
   composer check
   ```
5. Commit your changes using conventional commits:
   ```bash
   git commit -m "feat: add support for WebP format"
   git commit -m "fix: handle empty EXIF data correctly"
   ```

## Pull Request Process

1. Update the README.md with details of changes if needed
2. Update the CHANGELOG.md following the existing format
3. The PR title should follow conventional commits format
4. Link any related issues in the PR description
5. Include screenshots or code examples if relevant

## Development Guidelines

### Code Style

We follow PSR-12 coding standards. Run the following to check and fix style:

```bash
# Check code style
composer cs

# Fix code style automatically
composer cs:fix
```

### Static Analysis

We use PHPStan at level 8. Check your code with:

```bash
composer stan
```

### Testing

- Write unit tests for new features
- Maintain existing tests
- Aim for high coverage
- Run tests with:
  ```bash
  composer test
  composer test:coverage
  ```

### Documentation

- Keep inline documentation up to date
- Update README.md for new features
- Add examples for complex functionality
- Document breaking changes

## Commit Message Guidelines

We use conventional commits to generate changelogs automatically:

- `feat:` New features
- `fix:` Bug fixes
- `docs:` Documentation only changes
- `style:` Code style changes
- `refactor:` Code refactoring
- `perf:` Performance improvements
- `test:` Adding or updating tests
- `chore:` Maintenance tasks

## Questions or Problems?

- Open a GitHub issue for bugs
- Use GitHub Discussions for questions or ideas
- Tag issues appropriately

## Project Structure

```
src/
├── Exception/           # Custom exceptions
├── ImageHandler/        # Format-specific handlers
│   ├── JpegHandler.php
│   ├── PngHandler.php
│   └── GifHandler.php
├── ImageProcessor.php   # Main processor class
└── ImageProcessorFactory.php
```

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
