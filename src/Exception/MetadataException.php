<?php

namespace MetaStrip\Image\Exception;

/**
 * Exception thrown when metadata-related issues occur
 */
class MetadataException extends BaseException
{
    /** @var string Type of metadata that caused the error */
    private string $metadataType;

    /** @var string|null Raw metadata value that caused the error */
    private ?string $rawValue;

    /**
     * Create new metadata exception
     * 
     * @param string $type Type of metadata (EXIF, IPTC, XMP, etc.)
     * @param string|null $raw Raw metadata value
     * @param string $message Error message
     * @param int $code Error code
     */
    public function __construct(
        string $type,
        ?string $raw = null,
        string $message = "",
        int $code = 0
    ) {
        parent::__construct($message ?: "Metadata error: $type", $code);
        $this->metadataType = $type;
        $this->rawValue = $raw;
        $this->context = [
            'metadata_type' => $type,
            'raw_value' => $raw
        ];
    }

    /**
     * Get metadata type
     * 
     * @return string
     */
    public function getMetadataType(): string
    {
        return $this->metadataType;
    }

    /**
     * Get raw metadata value
     * 
     * @return string|null
     */
    public function getRawValue(): ?string
    {
        return $this->rawValue;
    }
}
