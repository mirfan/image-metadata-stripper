<?php

namespace Hmis\ImageMetadataStripper\ImageHandler;

/**
 * Handler for JPEG image format
 */
class JpegHandler implements ImageHandlerInterface
{
    // JPEG Markers
    private const SOI = 0xD8;  // Start of Image
    private const EOI = 0xD9;  // End of Image
    private const SOS = 0xDA;  // Start of Scan
    private const APP1 = 0xE1;  // EXIF Marker
    private const APP13 = 0xED; // IPTC Marker

    public function saveWithoutMetadata(\GdImage $image, string $filePath): void
    {
        // Save image with maximum quality
        imagejpeg($image, $filePath, 100);
        
        // Read the image binary
        $data = file_get_contents($filePath);
        
        // Remove EXIF and IPTC markers
        $newData = $this->removeSegments($data, [self::APP1, self::APP13]);
        
        // Write the cleaned image back
        file_put_contents($filePath, $newData);
    }

    private function removeSegments(string $data, array $segmentMarkers): string
    {
        $pos = 0;
        $len = strlen($data);
        $newData = '';
        
        // Validate JPEG header
        if (ord($data[0]) != 0xFF || ord($data[1]) != self::SOI) {
            return $data;
        }
        
        $newData .= "\xFF\xD8";
        $pos = 2;
        
        while ($pos < $len) {
            if (ord($data[$pos]) != 0xFF) {
                return $data;
            }
            
            $marker = ord($data[$pos + 1]);
            
            if (!in_array($marker, $segmentMarkers)) {
                $newData .= $data[$pos];
                $newData .= $data[$pos + 1];
                
                if ($marker != self::EOI && $marker != self::SOI && !($marker >= 0xD0 && $marker <= 0xD7)) {
                    $segmentLength = (ord($data[$pos + 2]) << 8) + ord($data[$pos + 3]);
                    $newData .= $data[$pos + 2];
                    $newData .= $data[$pos + 3];
                    $newData .= substr($data, $pos + 4, $segmentLength - 2);
                    $pos += $segmentLength + 2;
                } else {
                    $pos += 2;
                }
            } else {
                $segmentLength = (ord($data[$pos + 2]) << 8) + ord($data[$pos + 3]);
                $pos += $segmentLength + 2;
            }
        }
        
        return $newData;
    }
}
