<?php

namespace MetaStrip\Image\Exception;

/**
 * Exception thrown when security-related issues occur
 */
class SecurityException extends BaseException
{
    /** @var string Type of security violation */
    private string $violationType;

    /** @var string|null Security policy that was violated */
    private ?string $policy;

    /** @var array Additional security context */
    private array $securityContext;

    /**
     * Create new security exception
     * 
     * @param string $type Type of violation (e.g., 'malicious_content', 'policy_violation')
     * @param string|null $policy Security policy that was violated
     * @param array $securityContext Additional security context
     * @param string $message Error message
     * @param int $code Error code
     */
    public function __construct(
        string $type,
        ?string $policy = null,
        array $securityContext = [],
        string $message = "",
        int $code = 0
    ) {
        parent::__construct($message ?: "Security violation: $type", $code);
        $this->violationType = $type;
        $this->policy = $policy;
        $this->securityContext = $securityContext;
        $this->context = [
            'violation_type' => $type,
            'policy' => $policy,
            'security_context' => $this->sanitizeContext($securityContext)
        ];
    }

    /**
     * Get violation type
     * 
     * @return string
     */
    public function getViolationType(): string
    {
        return $this->violationType;
    }

    /**
     * Get violated policy
     * 
     * @return string|null
     */
    public function getPolicy(): ?string
    {
        return $this->policy;
    }

    /**
     * Get security context
     * 
     * @return array
     */
    public function getSecurityContext(): array
    {
        return $this->securityContext;
    }

    /**
     * Sanitize security context to remove sensitive data
     * 
     * @param array $context Context to sanitize
     * @return array
     */
    private function sanitizeContext(array $context): array
    {
        $sensitiveKeys = ['password', 'key', 'token', 'secret', 'auth'];
        
        return array_map(function ($value) use ($sensitiveKeys) {
            if (is_array($value)) {
                return $this->sanitizeContext($value);
            }
            
            foreach ($sensitiveKeys as $key) {
                if (stripos((string)$value, $key) !== false) {
                    return '[REDACTED]';
                }
            }
            
            return $value;
        }, $context);
    }
}
