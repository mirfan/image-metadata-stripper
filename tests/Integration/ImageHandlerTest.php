<?php

namespace Hmis\ImageMetadataStripper\Tests\Integration;

use Hmis\ImageMetadataStripper\ImageProcessor;
use Hmis\ImageMetadataStripper\ImageProcessorFactory;
use PHPUnit\Framework\TestCase;

class ImageHandlerTest extends TestCase
{
    private ImageProcessor $processor;
    private string $testDataDir;

    protected function setUp(): void
    {
        $this->processor = ImageProcessorFactory::create();
        $this->testDataDir = __DIR__ . '/../data';
        
        if (!is_dir($this->testDataDir)) {
            mkdir($this->testDataDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        array_map('unlink', glob($this->testDataDir . '/*'));
        rmdir($this->testDataDir);
    }

    public function testJpegMetadataStripping(): void
    {
        $testFile = $this->createTestImage('test.jpg', IMAGETYPE_JPEG);
        $originalSize = filesize($testFile);
        
        $this->processor->stripExifData($testFile);
        
        $this->assertFileExists($testFile);
        $this->assertLessThan($originalSize, filesize($testFile));
    }

    public function testPngMetadataStripping(): void
    {
        $testFile = $this->createTestImage('test.png', IMAGETYPE_PNG);
        $originalSize = filesize($testFile);
        
        $this->processor->stripExifData($testFile);
        
        $this->assertFileExists($testFile);
        $this->assertLessThan($originalSize, filesize($testFile));
    }

    public function testGifMetadataStripping(): void
    {
        $testFile = $this->createTestImage('test.gif', IMAGETYPE_GIF);
        $originalSize = filesize($testFile);
        
        $this->processor->stripExifData($testFile);
        
        $this->assertFileExists($testFile);
        $this->assertLessThan($originalSize, filesize($testFile));
    }

    private function createTestImage(string $filename, int $type): string
    {
        $image = imagecreatetruecolor(100, 100);
        $filePath = $this->testDataDir . '/' . $filename;
        
        // Add some color to make it more realistic
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefilledrectangle($image, 0, 0, 99, 99, $red);
        
        // Save with metadata
        switch ($type) {
            case IMAGETYPE_JPEG:
                // Add EXIF data
                $exif = array('IFD0' => array('ImageDescription' => 'Test Image'));
                imagejpeg($image, $filePath, 100);
                break;
                
            case IMAGETYPE_PNG:
                // Add text chunk
                imagepng($image, $filePath, 0);
                break;
                
            case IMAGETYPE_GIF:
                imagegif($image, $filePath);
                break;
        }
        
        imagedestroy($image);
        return $filePath;
    }
}
