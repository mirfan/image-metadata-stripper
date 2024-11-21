<?php

namespace Hmis\ImageMetadataStripper;

use Hmis\ImageMetadataStripper\ImageHandler\JpegHandler;
use Hmis\ImageMetadataStripper\ImageHandler\PngHandler;
use Hmis\ImageMetadataStripper\ImageHandler\GifHandler;

class ImageProcessorFactory
{
    public static function create(): ImageProcessor
    {
        return new ImageProcessor([
            IMAGETYPE_JPEG => new JpegHandler(),
            IMAGETYPE_PNG => new PngHandler(),
            IMAGETYPE_GIF => new GifHandler(),
        ]);
    }
}
