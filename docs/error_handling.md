# Error Handling Guide for MetaStrip Image

## Table of Contents
1. [Common Exceptions](#common-exceptions)
2. [Error Types](#error-types)
3. [Best Practices](#best-practices)
4. [Code Examples](#code-examples)
5. [Troubleshooting](#troubleshooting)

## Common Exceptions

### ImageProcessingException
Base exception for all image processing errors.

```php
try {
    $processor->processFile($input, $output);
} catch (ImageProcessingException $e) {
    // Handle general processing errors
    log_error($e->getMessage(), $e->getCode());
}
```

### UnsupportedFormatException
Thrown when attempting to process an unsupported image format.

```php
try {
    $processor->processFile('image.webp', 'output.webp');
} catch (UnsupportedFormatException $e) {
    // Handle unsupported format
    echo "Format not supported: " . $e->getMessage();
}
```

### InvalidConfigurationException
Thrown when configuration options are invalid.

```php
try {
    $processor->setOptions(['jpeg_quality' => 101]); // Invalid quality
} catch (InvalidConfigurationException $e) {
    // Handle configuration errors
    echo "Invalid configuration: " . $e->getMessage();
}
```

### MetadataExtractionException
Thrown when metadata cannot be extracted from an image.

```php
try {
    $metadata = $processor->extractMetadata($input);
} catch (MetadataExtractionException $e) {
    // Handle metadata extraction errors
    log_error("Metadata extraction failed: " . $e->getMessage());
}
```

## Error Types

### Critical Errors
These errors prevent image processing and must be handled:
- File system errors (permissions, missing files)
- Memory allocation errors
- Unsupported image formats
- Invalid configuration

```php
try {
    $processor->processFile($input, $output);
} catch (RuntimeException $e) {
    // Critical error handling
    log_critical_error($e);
    throw $e; // Re-throw if unrecoverable
}
```

### Non-Critical Errors
These errors may allow processing to continue:
- Partial metadata removal
- Minor format-specific issues
- Performance warnings

```php
try {
    $result = $processor->processFile($input, $output);
    if ($result->hasWarnings()) {
        foreach ($result->getWarnings() as $warning) {
            log_warning($warning);
        }
    }
} catch (Exception $e) {
    // Handle error
}
```

## Best Practices

### 1. Use Specific Exception Handling
```php
try {
    $processor->processFile($input, $output);
} catch (UnsupportedFormatException $e) {
    // Handle format issues
} catch (FileSystemException $e) {
    // Handle file system issues
} catch (MemoryException $e) {
    // Handle memory issues
} catch (Exception $e) {
    // Handle other issues
}
```

### 2. Implement Logging
```php
use Psr\Log\LoggerInterface;

class ImageProcessor {
    private $logger;

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    private function handleError(\Exception $e) {
        $this->logger->error('Processing failed', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
}
```

### 3. Validate Input
```php
public function processFile(string $input, string $output): void {
    // Validate input file
    if (!file_exists($input)) {
        throw new FileNotFoundException("Input file not found: $input");
    }

    // Validate output directory
    $outputDir = dirname($output);
    if (!is_writable($outputDir)) {
        throw new FileSystemException("Output directory not writable: $outputDir");
    }

    // Validate image format
    $format = $this->detectImageFormat($input);
    if (!$this->isSupportedFormat($format)) {
        throw new UnsupportedFormatException("Unsupported format: $format");
    }
}
```

### 4. Memory Management
```php
public function processLargeImage(string $input): void {
    // Calculate memory requirements
    $imageInfo = getimagesize($input);
    $requiredMemory = $this->calculateRequiredMemory($imageInfo);
    
    // Check available memory
    if (!$this->hasEnoughMemory($requiredMemory)) {
        throw new MemoryException(
            "Insufficient memory. Required: {$requiredMemory}MB"
        );
    }

    // Process with memory limit
    $currentLimit = ini_get('memory_limit');
    ini_set('memory_limit', $requiredMemory . 'M');
    
    try {
        // Process image
        $this->processFile($input, $output);
    } finally {
        // Restore memory limit
        ini_set('memory_limit', $currentLimit);
    }
}
```

## Code Examples

### Batch Processing with Error Recovery
```php
class BatchProcessor {
    private $errors = [];
    private $processed = [];
    private $skipped = [];

    public function processDirectory(string $dir): array {
        $files = glob("$dir/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
        
        foreach ($files as $file) {
            try {
                $this->processFile($file);
                $this->processed[] = $file;
            } catch (UnsupportedFormatException $e) {
                $this->skipped[] = [
                    'file' => $file,
                    'reason' => 'unsupported_format',
                    'message' => $e->getMessage()
                ];
            } catch (Exception $e) {
                $this->errors[] = [
                    'file' => $file,
                    'error' => $e->getMessage(),
                    'type' => get_class($e)
                ];
            }
        }

        return [
            'processed' => $this->processed,
            'errors' => $this->errors,
            'skipped' => $this->skipped
        ];
    }
}
```

### Custom Error Handler
```php
class ErrorHandler {
    private $logger;
    private $notifier;

    public function __construct(LoggerInterface $logger, ErrorNotifier $notifier) {
        $this->logger = $logger;
        $this->notifier = $notifier;
    }

    public function handleError(Exception $e, string $context = ''): void {
        // Log the error
        $this->logger->error($e->getMessage(), [
            'context' => $context,
            'stack_trace' => $e->getTraceAsString()
        ]);

        // Notify if critical
        if ($this->isCriticalError($e)) {
            $this->notifier->sendNotification([
                'error' => $e->getMessage(),
                'context' => $context,
                'severity' => 'critical'
            ]);
        }
    }

    private function isCriticalError(Exception $e): bool {
        return $e instanceof MemoryException ||
               $e instanceof FileSystemException ||
               $e instanceof SecurityException;
    }
}
```

## Troubleshooting

### Memory Issues
1. Check available memory:
```php
echo ini_get('memory_limit');
```

2. Calculate required memory:
```php
$imageInfo = getimagesize($file);
$requiredBytes = $imageInfo[0] * $imageInfo[1] * 4; // RGBA
$requiredMB = ceil($requiredBytes / 1024 / 1024);
```

### File System Issues
1. Check permissions:
```php
if (!is_readable($input)) {
    throw new FileSystemException("Input file not readable");
}
if (!is_writable(dirname($output))) {
    throw new FileSystemException("Output directory not writable");
}
```

2. Validate paths:
```php
$realPath = realpath($path);
if ($realPath === false) {
    throw new FileSystemException("Invalid path: $path");
}
```

### Format Detection Issues
1. Check MIME type:
```php
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file);
finfo_close($finfo);

if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
    throw new UnsupportedFormatException("Unsupported MIME type: $mimeType");
}
```

2. Validate image data:
```php
if (($imageInfo = getimagesize($file)) === false) {
    throw new InvalidImageException("Invalid or corrupted image file");
}
```
