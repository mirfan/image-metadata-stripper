# MetaStrip Image

[![CI](https://github.com/metastrip/image/actions/workflows/ci.yml/badge.svg)](https://github.com/metastrip/image/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/metastrip/image/branch/main/graph/badge.svg)](https://codecov.io/gh/metastrip/image)
[![Latest Stable Version](http://poser.pugx.org/metastrip/image/v)](https://packagist.org/packages/metastrip/image)
[![License](http://poser.pugx.org/metastrip/image/license)](https://packagist.org/packages/metastrip/image)
[![PHP Version Require](http://poser.pugx.org/metastrip/image/require/php)](https://packagist.org/packages/metastrip/image)

A lightweight PHP library for efficiently removing EXIF, IPTC, and other metadata from images while preserving quality. Supports JPEG, PNG, and GIF formats.

## Features

- Efficient metadata removal with zero quality loss
- Supports multiple image formats and metadata types:
  - JPEG: EXIF and IPTC markers
  - PNG: tEXt, iTXt, and zTXt chunks
  - GIF: XMP and Application Extension blocks
- Privacy-focused: removes sensitive information
- Zero external dependencies (PHP GD only)
- Clean, object-oriented architecture
- Comprehensive test coverage
- PSR-12 compliant

## Requirements

- PHP 8.0 or higher
- GD extension

## Installation

Install via Composer:

```bash
composer require metastrip/image
```

## Usage

Basic usage:

```php
use MetaStrip\Image\ImageProcessorFactory;

// Create processor instance
$processor = ImageProcessorFactory::create();

// Strip metadata from an image
$processor->stripExifData('/path/to/image.jpg');
```

Advanced usage with specific handlers:

```php
use MetaStrip\Image\ImageProcessor;
use MetaStrip\Image\ImageHandler\JpegHandler;
use MetaStrip\Image\ImageHandler\PngHandler;
use MetaStrip\Image\ImageHandler\GifHandler;

// Create processor with specific handlers
$processor = new ImageProcessor([
    IMAGETYPE_JPEG => new JpegHandler(),
    IMAGETYPE_PNG => new PngHandler(),
    IMAGETYPE_GIF => new GifHandler(),
]);

// Process multiple images
$processor->stripExifData('/path/to/image1.jpg');
$processor->stripExifData('/path/to/image2.png');
$processor->stripExifData('/path/to/image3.gif');
```

## Development

### Setup

1. Clone the repository:
```bash
git clone https://github.com/metastrip/image.git
cd image
```

2. Install dependencies:
```bash
composer install
```

### Quality Tools

We use three main tools to ensure code quality:

1. **PHPUnit** - Testing Framework
   ```bash
   # Run tests
   composer test
   
   # Generate coverage report
   composer test:coverage
   ```

2. **PHP_CodeSniffer** - Code Style
   ```bash
   # Check coding standards
   composer cs
   
   # Fix coding standards automatically
   composer cs:fix
   ```

3. **PHPStan** - Static Analysis
   ```bash
   # Run static analysis
   composer stan
   ```

Run all checks at once:
```bash
composer check
```

### Continuous Integration

Our GitHub Actions workflow automatically runs:
- Tests on PHP 8.0, 8.1, and 8.2
- Code style checks
- Static analysis
- Code coverage reporting

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. By participating in this project, you agree to abide by its terms.

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Make your changes
4. Run the tests: `composer check`
5. Commit your changes: `git commit -m 'Add feature'`
6. Push to the branch: `git push origin feature-name`
7. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
