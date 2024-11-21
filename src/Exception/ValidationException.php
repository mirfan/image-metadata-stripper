<?php

namespace MetaStrip\Image\Exception;

/**
 * Exception thrown when validation fails
 */
class ValidationException extends BaseException
{
    /** @var array Validation errors */
    private array $errors;

    /** @var mixed Invalid value */
    private $invalidValue;

    /** @var array Validation constraints that failed */
    private array $constraints;

    /**
     * Create new validation exception
     * 
     * @param array $errors Validation errors
     * @param mixed $value Invalid value
     * @param array $constraints Failed constraints
     * @param string $message Error message
     * @param int $code Error code
     */
    public function __construct(
        array $errors,
        $value,
        array $constraints = [],
        string $message = "",
        int $code = 0
    ) {
        parent::__construct($message ?: "Validation failed: " . implode(", ", $errors), $code);
        $this->errors = $errors;
        $this->invalidValue = $value;
        $this->constraints = $constraints;
        $this->context = [
            'errors' => $errors,
            'value' => $this->formatValue($value),
            'constraints' => $constraints
        ];
    }

    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get invalid value
     * 
     * @return mixed
     */
    public function getInvalidValue()
    {
        return $this->invalidValue;
    }

    /**
     * Get failed constraints
     * 
     * @return array
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * Format value for context
     * 
     * @param mixed $value Value to format
     * @return string|array|null
     */
    private function formatValue($value)
    {
        if (is_resource($value)) {
            return 'resource(' . get_resource_type($value) . ')';
        }
        if (is_object($value)) {
            return [
                'class' => get_class($value),
                'string' => method_exists($value, '__toString') ? (string)$value : null
            ];
        }
        return $value;
    }
}
