<?php

namespace MetaStrip\Image\Exception;

/**
 * Base exception class for MetaStrip Image library
 */
class BaseException extends \Exception
{
    /** @var array Additional context for the error */
    protected array $context = [];

    /**
     * Set additional error context
     * 
     * @param array $context Context data
     * @return self
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get error context
     * 
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get error as array with context
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->context,
            'trace' => $this->getTraceAsString()
        ];
    }
}
