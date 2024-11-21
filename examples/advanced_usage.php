<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MetaStrip\Image\ImageProcessorFactory;
use MetaStrip\Image\ImageHandler\JpegHandler;
use MetaStrip\Image\ImageHandler\PngHandler;
use MetaStrip\Image\ImageHandler\GifHandler;

// Create a customized processor with specific handlers
$processor = ImageProcessorFactory::createCustom([
    new JpegHandler(['preserve_icc_profile' => true]),
    new PngHandler(['preserve_transparency' => true]),
    new GifHandler(['preserve_animations' => true])
]);

try {
    // Example 1: Process with quality settings
    $processor->processFile(
        'high-quality.jpg',
        'optimized.jpg',
        ['jpeg_quality' => 85]
    );
    
    // Example 2: Process with size limits
    $processor->processFile(
        'large.png',
        'resized.png',
        ['max_dimension' => 2000]
    );
    
    // Example 3: Batch processing with progress callback
    $files = [
        'image1.jpg' => 'output1.jpg',
        'image2.png' => 'output2.png',
        'image3.gif' => 'output3.gif'
    ];
    
    $total = count($files);
    $current = 0;
    
    foreach ($files as $input => $output) {
        $current++;
        $processor->processFile($input, $output);
        echo "Progress: " . ($current / $total * 100) . "%\n";
    }
    
    // Example 4: Error handling and validation
    $processor->setValidationCallback(function($path) {
        $size = filesize($path);
        if ($size > 10 * 1024 * 1024) { // 10MB
            throw new \RuntimeException('File too large: ' . $path);
        }
        return true;
    });
    
    // Example 5: Custom error handling
    try {
        $processor->processFile('invalid.jpg', 'output.jpg');
    } catch (\Exception $e) {
        error_log('Processing error: ' . $e->getMessage());
        // Implement custom error handling
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
