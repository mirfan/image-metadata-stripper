<?php

namespace MetaStrip\Image\Validator;

use MetaStrip\Image\Exception\ValidationException;
use MetaStrip\Image\Exception\SecurityException;

/**
 * Validator for image files
 */
class ImageValidator
{
    /** @var array Supported image formats */
    private array $supportedFormats;

    /** @var array Security policies */
    private array $securityPolicies;

    /** @var int Maximum file size in bytes */
    private int $maxFileSize;

    /** @var array Validation rules */
    private array $rules;

    /**
     * Create new image validator
     * 
     * @param array $supportedFormats Supported image formats
     * @param array $securityPolicies Security policies
     * @param int $maxFileSize Maximum file size in bytes
     */
    public function __construct(
        array $supportedFormats = ['image/jpeg', 'image/png', 'image/gif'],
        array $securityPolicies = [],
        int $maxFileSize = 10485760 // 10MB
    ) {
        $this->supportedFormats = $supportedFormats;
        $this->securityPolicies = $securityPolicies;
        $this->maxFileSize = $maxFileSize;
        $this->rules = [];
    }

    /**
     * Add validation rule
     * 
     * @param callable $rule Validation rule
     * @param string $message Error message
     * @return self
     */
    public function addRule(callable $rule, string $message): self
    {
        $this->rules[] = [
            'rule' => $rule,
            'message' => $message
        ];
        return $this;
    }

    /**
     * Validate image file
     * 
     * @param string $file File path
     * @throws ValidationException If validation fails
     * @throws SecurityException If security check fails
     */
    public function validate(string $file): void
    {
        $errors = [];
        $constraints = [];

        // Basic file validation
        if (!file_exists($file)) {
            throw new ValidationException(
                ['File does not exist'],
                $file,
                ['exists' => true]
            );
        }

        if (!is_readable($file)) {
            throw new ValidationException(
                ['File is not readable'],
                $file,
                ['readable' => true]
            );
        }

        // File size validation
        $size = filesize($file);
        if ($size > $this->maxFileSize) {
            $errors[] = "File size exceeds maximum allowed size";
            $constraints['size'] = [
                'max' => $this->maxFileSize,
                'actual' => $size
            ];
        }

        // Format validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->supportedFormats)) {
            $errors[] = "Unsupported image format: $mimeType";
            $constraints['format'] = [
                'supported' => $this->supportedFormats,
                'actual' => $mimeType
            ];
        }

        // Image dimensions validation
        $imageInfo = @getimagesize($file);
        if ($imageInfo === false) {
            $errors[] = "Invalid image file";
            $constraints['valid_image'] = false;
        } else {
            // Check dimensions
            list($width, $height) = $imageInfo;
            if ($width <= 0 || $height <= 0) {
                $errors[] = "Invalid image dimensions";
                $constraints['dimensions'] = [
                    'width' => $width,
                    'height' => $height
                ];
            }
        }

        // Security validation
        $this->validateSecurity($file);

        // Custom rules validation
        foreach ($this->rules as $rule) {
            try {
                if (!$rule['rule']($file)) {
                    $errors[] = $rule['message'];
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        // Throw validation exception if there are errors
        if (!empty($errors)) {
            throw new ValidationException(
                $errors,
                $file,
                $constraints,
                "Image validation failed: " . implode(", ", $errors)
            );
        }
    }

    /**
     * Validate image security
     * 
     * @param string $file File path
     * @throws SecurityException If security check fails
     */
    private function validateSecurity(string $file): void
    {
        // Check for malicious content
        $content = file_get_contents($file);
        $suspiciousPatterns = [
            '/\<\?php/i',
            '/\<script/i',
            '/\<\%/i',
            '/eval\(/i'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new SecurityException(
                    'malicious_content',
                    'no_embedded_code',
                    ['pattern' => $pattern],
                    "Potentially malicious content detected"
                );
            }
        }

        // Apply security policies
        foreach ($this->securityPolicies as $policy => $check) {
            if (!$check($file)) {
                throw new SecurityException(
                    'policy_violation',
                    $policy,
                    ['file' => $file],
                    "Security policy violation: $policy"
                );
            }
        }
    }

    /**
     * Get supported formats
     * 
     * @return array
     */
    public function getSupportedFormats(): array
    {
        return $this->supportedFormats;
    }

    /**
     * Get security policies
     * 
     * @return array
     */
    public function getSecurityPolicies(): array
    {
        return $this->securityPolicies;
    }

    /**
     * Get maximum file size
     * 
     * @return int
     */
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * Get validation rules
     * 
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
