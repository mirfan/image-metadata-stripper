<?php

namespace Hmis\ImageMetadataStripper\ImageHandler;

/**
 * Handler for PNG image format
 */
class PngHandler implements ImageHandlerInterface
{
    // PNG Chunk Types
    private const METADATA_CHUNKS = ['tEXt', 'iTXt', 'zTXt'];

    public function saveWithoutMetadata(\GdImage $image, string $filePath): void
    {
        // Save image with maximum quality
        imagepng($image, $filePath, 0);
        
        // Read the image binary
        $data = file_get_contents($filePath);
        
        // Remove metadata chunks
        $newData = $this->removeMetadataChunks($data);
        
        // Write the cleaned image back
        file_put_contents($filePath, $newData);
    }

    private function removeMetadataChunks(string $data): string
    {
        $pos = 8;  // Skip PNG signature
        $len = strlen($data);
        $newData = substr($data, 0, 8);  // Keep PNG signature
        
        while ($pos < $len) {
            $chunkLength = unpack('N', substr($data, $pos, 4))[1];
            $chunkType = substr($data, $pos + 4, 4);
            
            if (!in_array($chunkType, self::METADATA_CHUNKS)) {
                // Keep non-metadata chunks
                $chunk = substr($data, $pos, $chunkLength + 12);  // length(4) + type(4) + data + crc(4)
                $newData .= $chunk;
            }
            
            $pos += $chunkLength + 12;
        }
        
        return $newData;
    }
}
