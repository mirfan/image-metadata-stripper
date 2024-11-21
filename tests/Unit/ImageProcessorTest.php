<?php

namespace Hmis\ImageMetadataStripper\Tests\Unit;

use Hmis\ImageMetadataStripper\ImageProcessor;
use Hmis\ImageMetadataStripper\ImageHandler\ImageHandlerInterface;
use Hmis\ImageMetadataStripper\Exception\ImageProcessingException;
use PHPUnit\Framework\TestCase;

class ImageProcessorTest extends TestCase
{
    private ImageProcessor $processor;
    private ImageHandlerInterface $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = $this->createMock(ImageHandlerInterface::class);
        $this->processor = new ImageProcessor([
            IMAGETYPE_JPEG => $this->mockHandler
        ]);
    }

    public function testStripExifDataWithNonExistentFile(): void
    {
        $this->expectException(ImageProcessingException::class);
        $this->expectExceptionMessage('File not found');
        
        $this->processor->stripExifData('/non/existent/file.jpg');
    }

    public function testStripExifDataWithUnsupportedType(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'not an image');

        $this->expectException(ImageProcessingException::class);
        $this->expectExceptionMessage('Failed to detect image type');
        
        try {
            $this->processor->stripExifData($tempFile);
        } finally {
            unlink($tempFile);
        }
    }

    public function testStripExifDataSuccess(): void
    {
        // Create a test JPEG file
        $image = imagecreatetruecolor(100, 100);
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.jpg';
        imagejpeg($image, $tempFile);
        imagedestroy($image);

        $this->mockHandler
            ->expects($this->once())
            ->method('saveWithoutMetadata')
            ->with(
                $this->isInstanceOf(\GdImage::class),
                $this->equalTo($tempFile)
            );

        try {
            $this->processor->stripExifData($tempFile);
            $this->assertFileExists($tempFile);
        } finally {
            unlink($tempFile);
        }
    }
}
