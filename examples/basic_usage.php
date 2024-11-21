<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hmis\ImageMetadataStripper\ImageProcessorFactory;

// Create a test image with metadata
$image = imagecreatetruecolor(100, 100);
$red = imagecolorallocate($image, 255, 0, 0);
imagefilledrectangle($image, 0, 0, 99, 99, $red);

// Save test images with metadata
$jpegFile = __DIR__ . '/test.jpg';
$pngFile = __DIR__ . '/test.png';
$gifFile = __DIR__ . '/test.gif';

imagejpeg($image, $jpegFile, 100);
imagepng($image, $pngFile, 0);
imagegif($image, $gifFile);
imagedestroy($image);

// Create processor
$processor = ImageProcessorFactory::create();

// Process each image type
echo "Processing JPEG file...\n";
$jpegSizeBefore = filesize($jpegFile);
$processor->stripExifData($jpegFile);
$jpegSizeAfter = filesize($jpegFile);
echo "JPEG size reduced from {$jpegSizeBefore} to {$jpegSizeAfter} bytes\n";

echo "\nProcessing PNG file...\n";
$pngSizeBefore = filesize($pngFile);
$processor->stripExifData($pngFile);
$pngSizeAfter = filesize($pngFile);
echo "PNG size reduced from {$pngSizeBefore} to {$pngSizeAfter} bytes\n";

echo "\nProcessing GIF file...\n";
$gifSizeBefore = filesize($gifFile);
$processor->stripExifData($gifFile);
$gifSizeAfter = filesize($gifFile);
echo "GIF size reduced from {$gifSizeBefore} to {$gifSizeAfter} bytes\n";
