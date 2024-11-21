<?php

namespace MetaStrip\Image\Exception;

/**
 * Exception thrown when memory-related issues occur
 */
class MemoryException extends BaseException
{
    /** @var int Required memory in MB */
    private int $requiredMemory;

    /** @var int Available memory in MB */
    private int $availableMemory;

    /**
     * Create new memory exception
     * 
     * @param int $required Required memory in MB
     * @param int $available Available memory in MB
     * @param string $message Error message
     * @param int $code Error code
     */
    public function __construct(
        int $required,
        int $available,
        string $message = "",
        int $code = 0
    ) {
        parent::__construct($message ?: "Insufficient memory", $code);
        $this->requiredMemory = $required;
        $this->availableMemory = $available;
        $this->context = [
            'required_mb' => $required,
            'available_mb' => $available,
            'deficit_mb' => $required - $available
        ];
    }

    /**
     * Get required memory in MB
     * 
     * @return int
     */
    public function getRequiredMemory(): int
    {
        return $this->requiredMemory;
    }

    /**
     * Get available memory in MB
     * 
     * @return int
     */
    public function getAvailableMemory(): int
    {
        return $this->availableMemory;
    }
}
