<?php

namespace MetaStrip\Image\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use MetaStrip\Image\Exception\BaseException;

/**
 * Dedicated logger for MetaStrip Image
 */
class MetaStripLogger implements LoggerInterface
{
    /** @var array Log handlers */
    private array $handlers = [];

    /** @var array Log entries */
    private array $entries = [];

    /** @var int Maximum number of entries to keep */
    private int $maxEntries;

    /** @var array Log levels */
    private const LOG_LEVELS = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7,
    ];

    /**
     * Create new logger
     * 
     * @param int $maxEntries Maximum number of entries to keep
     */
    public function __construct(int $maxEntries = 1000)
    {
        $this->maxEntries = $maxEntries;
    }

    /**
     * Add log handler
     * 
     * @param LogHandlerInterface $handler Handler to add
     * @return self
     */
    public function addHandler(LogHandlerInterface $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }

    /**
     * System is unusable
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Warning conditions
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information
     * 
     * @param string $message Log message
     * @param array $context Log context
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Log a message
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Log context
     */
    public function log($level, $message, array $context = []): void
    {
        // Create log entry
        $entry = [
            'timestamp' => microtime(true),
            'datetime' => (new \DateTime())->format('Y-m-d H:i:s.u'),
            'level' => $level,
            'message' => $this->interpolate($message, $context),
            'context' => $this->formatContext($context)
        ];

        // Add entry to memory
        $this->entries[] = $entry;

        // Trim entries if needed
        if (count($this->entries) > $this->maxEntries) {
            array_shift($this->entries);
        }

        // Process handlers
        foreach ($this->handlers as $handler) {
            if ($handler->handles($level)) {
                $handler->handle($entry);
            }
        }
    }

    /**
     * Get all log entries
     * 
     * @return array
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * Get entries by level
     * 
     * @param string $level Log level
     * @return array
     */
    public function getEntriesByLevel(string $level): array
    {
        return array_filter($this->entries, function ($entry) use ($level) {
            return $entry['level'] === $level;
        });
    }

    /**
     * Get entries since timestamp
     * 
     * @param float $timestamp Unix timestamp with microseconds
     * @return array
     */
    public function getEntriesSince(float $timestamp): array
    {
        return array_filter($this->entries, function ($entry) use ($timestamp) {
            return $entry['timestamp'] >= $timestamp;
        });
    }

    /**
     * Clear all entries
     */
    public function clear(): void
    {
        $this->entries = [];
    }

    /**
     * Interpolate message with context
     * 
     * @param string $message Message with placeholders
     * @param array $context Context values
     * @return string
     */
    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_string($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * Format context for logging
     * 
     * @param array $context Context to format
     * @return array
     */
    private function formatContext(array $context): array
    {
        $formatted = [];
        foreach ($context as $key => $value) {
            if ($value instanceof BaseException) {
                $formatted[$key] = $value->toArray();
            } elseif ($value instanceof \Throwable) {
                $formatted[$key] = [
                    'class' => get_class($value),
                    'message' => $value->getMessage(),
                    'code' => $value->getCode(),
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                    'trace' => $value->getTraceAsString()
                ];
            } elseif (is_object($value)) {
                $formatted[$key] = [
                    'class' => get_class($value),
                    'string' => method_exists($value, '__toString') ? (string)$value : null
                ];
            } elseif (is_resource($value)) {
                $formatted[$key] = 'resource(' . get_resource_type($value) . ')';
            } else {
                $formatted[$key] = $value;
            }
        }
        return $formatted;
    }
}
