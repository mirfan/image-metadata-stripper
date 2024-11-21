<?php

namespace Hmis\ImageMetadataStripper;

use Hmis\ImageMetadataStripper\Exception\ImageProcessingException;
use Hmis\ImageMetadataStripper\ImageHandler\ImageHandlerInterface;

/**
 * Image processor utility for removing metadata from images
 */
class ImageProcessor
{
    /** @var array<int, ImageHandlerInterface> */
    private array $handlers;

    /**
     * @param array<int, ImageHandlerInterface> $handlers Array of image handlers indexed by IMAGETYPE_* constants
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * Strip EXIF and other metadata from an image file
     *
     * @param string $filePath Path to the image file
     * @throws ImageProcessingException If image processing fails
     */
    public function stripExifData(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new ImageProcessingException("File not found: $filePath");
        }

        $imageType = $this->detectImageType($filePath);
        if (!isset($this->handlers[$imageType])) {
            throw new ImageProcessingException('Unsupported image type');
        }

        $image = $this->createImageFromFile($filePath, $imageType);
        $this->handlers[$imageType]->saveWithoutMetadata($image, $filePath);
        imagedestroy($image);
    }

    /**
     * Detect image type from file
     *
     * @param string $filePath Path to the image file
     * @return int Image type constant
     * @throws ImageProcessingException If image type cannot be detected
     */
    private function detectImageType(string $filePath): int
    {
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo === false) {
            throw new ImageProcessingException('Failed to detect image type');
        }
        return $imageInfo[2];
    }

    /**
     * Create GD image resource from file
     *
     * @param string $filePath Path to the image file
     * @param int $imageType Image type constant
     * @return \GdImage GD image resource
     * @throws ImageProcessingException If image creation fails
     */
    private function createImageFromFile(string $filePath, int $imageType): \GdImage
    {
        $image = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
            IMAGETYPE_PNG => @imagecreatefrompng($filePath),
            IMAGETYPE_GIF => @imagecreatefromgif($filePath),
            default => throw new ImageProcessingException('Unsupported image type')
        };

        if ($image === false) {
            throw new ImageProcessingException('Failed to create image resource');
        }

        return $image;
    }
}
