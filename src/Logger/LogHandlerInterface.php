<?php

namespace MetaStrip\Image\Logger;

/**
 * Interface for log handlers
 */
interface LogHandlerInterface
{
    /**
     * Check if handler handles log level
     * 
     * @param string $level Log level
     * @return bool
     */
    public function handles(string $level): bool;

    /**
     * Handle log entry
     * 
     * @param array $entry Log entry
     */
    public function handle(array $entry): void;
}
