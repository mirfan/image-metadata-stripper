<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MetaStrip\Image\ImageProcessorFactory;

// Create an instance of the image processor
$processor = ImageProcessorFactory::create();

try {
    // Process a single image
    $processor->processFile('input.jpg', 'output.jpg');
    echo "Successfully processed single image\n";

    // Process multiple images in a directory
    $inputDir = 'input_directory';
    $outputDir = 'output_directory';
    
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    $images = glob($inputDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    foreach ($images as $image) {
        $outputPath = $outputDir . '/' . basename($image);
        $processor->processFile($image, $outputPath);
        echo "Processed: " . basename($image) . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
