<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MetaStrip\Image\ImageProcessorFactory;
use MetaStrip\Image\Exception\ImageProcessingException;
use MetaStrip\Image\Exception\UnsupportedFormatException;
use MetaStrip\Image\Exception\InvalidConfigurationException;
use MetaStrip\Image\Exception\FileSystemException;
use Psr\Log\LoggerInterface;

/**
 * Example demonstrating comprehensive error handling in MetaStrip Image
 * 
 * This example shows:
 * - Different types of error handling
 * - Logging integration
 * - Memory management
 * - Input validation
 * - Batch processing with error recovery
 */
class ErrorHandlingExample
{
    /** @var \MetaStrip\Image\ImageProcessor */
    private $processor;

    /** @var LoggerInterface */
    private $logger;

    /** @var array Processing statistics */
    private $stats = [
        'processed' => 0,
        'failed' => 0,
        'skipped' => 0
    ];

    /** @var array Collection of errors */
    private $errors = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->processor = ImageProcessorFactory::create();
        $this->logger = $logger;
    }

    /**
     * Process a single image with comprehensive error handling
     * 
     * @param string $input Input file path
     * @param string $output Output file path
     * @return bool True if processing was successful
     */
    public function processSingleImage(string $input, string $output): bool
    {
        try {
            // Input validation
            $this->validateInput($input);
            
            // Output validation
            $this->validateOutput($output);
            
            // Memory check
            $this->checkMemoryRequirements($input);
            
            // Process the image
            $this->processor->processFile($input, $output);
            
            $this->stats['processed']++;
            $this->logger->info("Successfully processed image", [
                'input' => $input,
                'output' => $output
            ]);
            
            return true;

        } catch (UnsupportedFormatException $e) {
            $this->handleError($e, 'format', $input);
            $this->stats['skipped']++;
            return false;
            
        } catch (InvalidConfigurationException $e) {
            $this->handleError($e, 'config', $input);
            $this->stats['failed']++;
            return false;
            
        } catch (FileSystemException $e) {
            $this->handleError($e, 'filesystem', $input);
            $this->stats['failed']++;
            return false;
            
        } catch (ImageProcessingException $e) {
            $this->handleError($e, 'processing', $input);
            $this->stats['failed']++;
            return false;
            
        } catch (\Exception $e) {
            $this->handleError($e, 'unknown', $input);
            $this->stats['failed']++;
            return false;
        }
    }

    /**
     * Process multiple images with error recovery
     * 
     * @param array $files Array of input => output file paths
     * @return array Processing statistics
     */
    public function processMultipleImages(array $files): array
    {
        foreach ($files as $input => $output) {
            try {
                $this->processSingleImage($input, $output);
            } catch (\Exception $e) {
                // Log but continue processing other files
                $this->logger->error("Failed to process file", [
                    'file' => $input,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $this->getProcessingStats();
    }

    /**
     * Validate input file
     * 
     * @param string $input Input file path
     * @throws FileSystemException If file is invalid
     */
    private function validateInput(string $input): void
    {
        if (!file_exists($input)) {
            throw new FileSystemException("Input file not found: $input");
        }

        if (!is_readable($input)) {
            throw new FileSystemException("Input file not readable: $input");
        }

        // Validate image format
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $input);
        finfo_close($finfo);

        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            throw new UnsupportedFormatException("Unsupported MIME type: $mimeType");
        }
    }

    /**
     * Validate output path
     * 
     * @param string $output Output file path
     * @throws FileSystemException If path is invalid
     */
    private function validateOutput(string $output): void
    {
        $dir = dirname($output);
        
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new FileSystemException("Failed to create output directory: $dir");
            }
        }

        if (!is_writable($dir)) {
            throw new FileSystemException("Output directory not writable: $dir");
        }
    }

    /**
     * Check if enough memory is available for processing
     * 
     * @param string $input Input file path
     * @throws \RuntimeException If not enough memory
     */
    private function checkMemoryRequirements(string $input): void
    {
        $imageInfo = getimagesize($input);
        if ($imageInfo === false) {
            throw new ImageProcessingException("Failed to get image dimensions");
        }

        // Calculate required memory (width * height * channels * safety factor)
        $requiredBytes = $imageInfo[0] * $imageInfo[1] * 4 * 2;
        $requiredMB = ceil($requiredBytes / 1024 / 1024);
        
        $limit = $this->getMemoryLimitMB();
        if ($limit !== -1 && $requiredMB > $limit) {
            throw new \RuntimeException(
                "Insufficient memory. Required: {$requiredMB}MB, Available: {$limit}MB"
            );
        }
    }

    /**
     * Get PHP memory limit in MB
     * 
     * @return int Memory limit in MB (-1 for unlimited)
     */
    private function getMemoryLimitMB(): int
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') return -1;
        
        if (preg_match('/^(\d+)(.)$/', $limit, $matches)) {
            $value = (int)$matches[1];
            switch (strtoupper($matches[2])) {
                case 'G': $value *= 1024;
                case 'M': return $value;
                case 'K': return $value / 1024;
            }
        }
        
        return (int)($limit / 1024 / 1024);
    }

    /**
     * Handle and log an error
     * 
     * @param \Exception $e Exception to handle
     * @param string $type Error type
     * @param string $file File being processed
     */
    private function handleError(\Exception $e, string $type, string $file): void
    {
        $error = [
            'file' => $file,
            'type' => $type,
            'message' => $e->getMessage(),
            'class' => get_class($e)
        ];

        $this->errors[] = $error;
        
        $this->logger->error("Image processing failed", $error);
    }

    /**
     * Get processing statistics
     * 
     * @return array Statistics and errors
     */
    public function getProcessingStats(): array
    {
        return [
            'stats' => $this->stats,
            'errors' => $this->errors
        ];
    }
}

// Usage example
try {
    // Create a PSR-3 compatible logger
    $logger = new class implements LoggerInterface {
        public function emergency($message, array $context = array()) { /* ... */ }
        public function alert($message, array $context = array()) { /* ... */ }
        public function critical($message, array $context = array()) { /* ... */ }
        public function error($message, array $context = array()) {
            echo "ERROR: $message\n";
            if (!empty($context)) {
                echo "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
            }
        }
        public function warning($message, array $context = array()) { /* ... */ }
        public function notice($message, array $context = array()) { /* ... */ }
        public function info($message, array $context = array()) {
            echo "INFO: $message\n";
        }
        public function debug($message, array $context = array()) { /* ... */ }
        public function log($level, $message, array $context = array()) { /* ... */ }
    };

    // Create processor with error handling
    $processor = new ErrorHandlingExample($logger);

    // Process multiple images
    $files = [
        'input1.jpg' => 'output1.jpg',
        'input2.png' => 'output2.png',
        'invalid.txt' => 'output3.jpg',
        'toolarge.jpg' => 'output4.jpg'
    ];

    $result = $processor->processMultipleImages($files);

    // Display results
    echo "\nProcessing completed:\n";
    echo "Processed: {$result['stats']['processed']}\n";
    echo "Failed: {$result['stats']['failed']}\n";
    echo "Skipped: {$result['stats']['skipped']}\n";

    if (!empty($result['errors'])) {
        echo "\nErrors encountered:\n";
        foreach ($result['errors'] as $error) {
            echo "- {$error['file']}: {$error['message']} ({$error['type']})\n";
        }
    }

} catch (\Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
}
