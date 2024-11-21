<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MetaStrip\Image\ImageProcessorFactory;
use MetaStrip\Image\ImageHandler\ImageHandlerInterface;

/**
 * Custom Image Handlers Example
 * 
 * This example demonstrates how to create and use custom image handlers
 * for additional image formats (WebP and AVIF). It shows:
 * - How to implement the ImageHandlerInterface
 * - Proper image resource management
 * - Format-specific optimizations
 * - Error handling and validation
 */

/**
 * Custom handler for WebP images
 * 
 * Implements metadata stripping for WebP format while preserving
 * image quality and transparency. Supports both lossy and lossless
 * WebP variants.
 */
class WebPHandler implements ImageHandlerInterface
{
    /** @var array Handler configuration options */
    private $options;

    /**
     * Initialize WebP handler with options
     * 
     * @param array $options Configuration options
     *        - quality: Compression quality (0-100)
     *        - preserve_transparency: Whether to maintain alpha channel
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'quality' => 80,
            'preserve_transparency' => true
        ], $options);
    }

    /**
     * Check if this handler can process the given file
     * 
     * @param string $filePath Path to image file
     * @return bool True if file is a WebP image
     */
    public function canHandle(string $filePath): bool
    {
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'webp';
    }

    /**
     * Process a WebP image to remove metadata
     * 
     * @param string $inputPath Source image path
     * @param string $outputPath Target image path
     * @return bool True if processing was successful
     * @throws \RuntimeException If WebP support is not available
     */
    public function process(string $inputPath, string $outputPath): bool
    {
        if (!function_exists('imagecreatefromwebp')) {
            throw new \RuntimeException('WebP support not available in GD');
        }

        // Load the WebP image
        $image = imagecreatefromwebp($inputPath);
        if ($image === false) {
            throw new \RuntimeException('Failed to load WebP image');
        }

        // Create a new image without metadata
        $width = imagesx($image);
        $height = imagesy($image);
        $newImage = imagecreatetruecolor($width, $height);

        // Handle transparency if needed
        if ($this->options['preserve_transparency']) {
            imagepalettetotruecolor($newImage);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        // Copy the image data (without metadata)
        imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);

        // Save the new image
        $result = imagewebp($newImage, $outputPath, $this->options['quality']);

        // Clean up
        imagedestroy($image);
        imagedestroy($newImage);

        return $result;
    }
}

/**
 * Custom handler for AVIF images
 * 
 * Implements metadata stripping for AVIF format while maintaining
 * image quality. Supports quality and speed settings for encoding.
 */
class AvifHandler implements ImageHandlerInterface
{
    /** @var array Handler configuration options */
    private $options;

    /**
     * Initialize AVIF handler with options
     * 
     * @param array $options Configuration options
     *        - quality: Compression quality (0-100)
     *        - speed: Encoding speed (0-10, higher is faster)
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'quality' => 80,
            'speed' => 6
        ], $options);
    }

    /**
     * Check if this handler can process the given file
     * 
     * @param string $filePath Path to image file
     * @return bool True if file is an AVIF image
     */
    public function canHandle(string $filePath): bool
    {
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'avif';
    }

    /**
     * Process an AVIF image to remove metadata
     * 
     * @param string $inputPath Source image path
     * @param string $outputPath Target image path
     * @return bool True if processing was successful
     * @throws \RuntimeException If AVIF support is not available
     */
    public function process(string $inputPath, string $outputPath): bool
    {
        if (!function_exists('imagecreatefromavif')) {
            throw new \RuntimeException('AVIF support not available in GD');
        }

        // Load the AVIF image
        $image = imagecreatefromavif($inputPath);
        if ($image === false) {
            throw new \RuntimeException('Failed to load AVIF image');
        }

        // Create new image without metadata
        $width = imagesx($image);
        $height = imagesy($image);
        $newImage = imagecreatetruecolor($width, $height);

        // Copy image data
        imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);

        // Save new image
        $result = imageavif($newImage, $outputPath, $this->options['quality'], $this->options['speed']);

        // Clean up
        imagedestroy($image);
        imagedestroy($newImage);

        return $result;
    }
}

// Usage example with custom handlers
try {
    // Create processor with custom handlers and specific options
    $processor = ImageProcessorFactory::createCustom([
        new WebPHandler([
            'quality' => 85,
            'preserve_transparency' => true  // Important for web graphics
        ]),
        new AvifHandler([
            'quality' => 90,  // High quality for AVIF
            'speed' => 4      // Balance between speed and compression
        ])
    ]);

    // Process different image formats with progress tracking
    $files = [
        'image.webp' => 'output.webp',
        'image.avif' => 'output.avif'
    ];

    foreach ($files as $input => $output) {
        try {
            $processor->processFile($input, $output);
            echo "Successfully processed: $input\n";
        } catch (\Exception $e) {
            echo "Error processing $input: " . $e->getMessage() . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
}
