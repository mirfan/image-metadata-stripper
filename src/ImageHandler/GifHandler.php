<?php

namespace Hmis\ImageMetadataStripper\ImageHandler;

/**
 * Handler for GIF image format
 */
class GifHandler implements ImageHandlerInterface
{
    // GIF Block Types
    private const APPLICATION_EXTENSION = 0xFF;
    private const XMP_IDENTIFIER = "XMP Data";

    public function saveWithoutMetadata(\GdImage $image, string $filePath): void
    {
        // Save image
        imagegif($image, $filePath);
        
        // Read the image binary
        $data = file_get_contents($filePath);
        
        // Remove metadata blocks
        $newData = $this->removeMetadataBlocks($data);
        
        // Write the cleaned image back
        file_put_contents($filePath, $newData);
    }

    private function removeMetadataBlocks(string $data): string
    {
        $pos = 13;  // Skip GIF header and logical screen descriptor
        $len = strlen($data);
        $newData = substr($data, 0, 13);  // Keep GIF header
        
        while ($pos < $len) {
            $blockType = ord($data[$pos]);
            
            if ($blockType == self::APPLICATION_EXTENSION) {
                $blockSize = ord($data[$pos + 1]);
                $identifier = substr($data, $pos + 2, $blockSize);
                
                if (strpos($identifier, self::XMP_IDENTIFIER) !== false) {
                    // Skip XMP block
                    $subBlockSize = ord($data[$pos + 2 + $blockSize]);
                    $pos += 2 + $blockSize + $subBlockSize + 1;
                    continue;
                }
            }
            
            $newData .= $data[$pos];
            $pos++;
        }
        
        return $newData;
    }
}
