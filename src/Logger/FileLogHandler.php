<?php

namespace MetaStrip\Image\Logger;

use Psr\Log\LogLevel;

/**
 * File-based log handler
 */
class FileLogHandler implements LogHandlerInterface
{
    /** @var string Log file path */
    private string $file;

    /** @var array Handled log levels */
    private array $levels;

    /** @var string Log format */
    private string $format;

    /**
     * Create new file log handler
     * 
     * @param string $file Log file path
     * @param array $levels Log levels to handle
     * @param string $format Log format
     */
    public function __construct(
        string $file,
        array $levels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING
        ],
        string $format = "[{datetime}] {level}: {message} {context}\n"
    ) {
        $this->file = $file;
        $this->levels = $levels;
        $this->format = $format;

        // Ensure log directory exists
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Check if handler handles log level
     * 
     * @param string $level Log level
     * @return bool
     */
    public function handles(string $level): bool
    {
        return in_array($level, $this->levels);
    }

    /**
     * Handle log entry
     * 
     * @param array $entry Log entry
     */
    public function handle(array $entry): void
    {
        $message = $this->format;
        foreach ($entry as $key => $value) {
            $message = str_replace(
                '{' . $key . '}',
                is_array($value) ? json_encode($value) : $value,
                $message
            );
        }

        file_put_contents($this->file, $message, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get log file path
     * 
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Get handled log levels
     * 
     * @return array
     */
    public function getLevels(): array
    {
        return $this->levels;
    }

    /**
     * Get log format
     * 
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Clear log file
     */
    public function clear(): void
    {
        file_put_contents($this->file, '', LOCK_EX);
    }
}
