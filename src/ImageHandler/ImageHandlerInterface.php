<?php

namespace Hmis\ImageMetadataStripper\ImageHandler;

interface ImageHandlerInterface
{
    /**
     * Save image without metadata
     *
     * @param \GdImage $image GD image resource
     * @param string $filePath Path to save the image
     */
    public function saveWithoutMetadata(\GdImage $image, string $filePath): void;
}
