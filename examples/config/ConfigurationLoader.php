<?php

namespace MetaStrip\Image\Config;

/**
 * Configuration Loader for MetaStrip Image
 * 
 * This class handles loading and validating configuration options for the MetaStrip Image library.
 * It supports loading from PHP files, JSON, or array configurations.
 */
class ConfigurationLoader
{
    /** @var array */
    private $config;

    /** @var array */
    private $defaultConfig;

    /**
     * Constructor
     * 
     * @param array|string|null $config Configuration array or path to config file
     * @throws \InvalidArgumentException If configuration is invalid
     */
    public function __construct($config = null)
    {
        $this->defaultConfig = require __DIR__ . '/metastrip.php';
        $this->loadConfiguration($config);
    }

    /**
     * Load configuration from various sources
     * 
     * @param array|string|null $config Configuration source
     * @return void
     * @throws \InvalidArgumentException If configuration source is invalid
     */
    private function loadConfiguration($config): void
    {
        if (is_null($config)) {
            $this->config = $this->defaultConfig;
            return;
        }

        if (is_string($config)) {
            // Load from file
            if (!file_exists($config)) {
                throw new \InvalidArgumentException("Configuration file not found: $config");
            }

            $extension = pathinfo($config, PATHINFO_EXTENSION);
            switch ($extension) {
                case 'php':
                    $this->config = require $config;
                    break;
                case 'json':
                    $this->config = json_decode(file_get_contents($config), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \InvalidArgumentException('Invalid JSON configuration: ' . json_last_error_msg());
                    }
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported configuration file type: $extension");
            }
        } elseif (is_array($config)) {
            $this->config = $config;
        } else {
            throw new \InvalidArgumentException('Configuration must be an array or file path');
        }

        // Merge with defaults
        $this->config = array_replace_recursive($this->defaultConfig, $this->config);
        
        // Validate configuration
        $this->validateConfiguration();
    }

    /**
     * Validate the loaded configuration
     * 
     * @throws \InvalidArgumentException If configuration is invalid
     */
    private function validateConfiguration(): void
    {
        // Validate JPEG quality
        if (isset($this->config['defaults']['jpeg_quality'])) {
            $quality = $this->config['defaults']['jpeg_quality'];
            if (!is_int($quality) || $quality < 0 || $quality > 100) {
                throw new \InvalidArgumentException('JPEG quality must be between 0 and 100');
            }
        }

        // Validate PNG compression
        if (isset($this->config['defaults']['png_compression'])) {
            $compression = $this->config['defaults']['png_compression'];
            if (!is_int($compression) || $compression < 0 || $compression > 9) {
                throw new \InvalidArgumentException('PNG compression must be between 0 and 9');
            }
        }

        // Validate handlers
        foreach ($this->config['handlers'] as $type => $handler) {
            if (!isset($handler['enabled'])) {
                throw new \InvalidArgumentException("Handler '$type' must have 'enabled' option");
            }
            if (!isset($handler['extensions']) || !is_array($handler['extensions'])) {
                throw new \InvalidArgumentException("Handler '$type' must have 'extensions' array");
            }
            if (!isset($handler['mime_types']) || !is_array($handler['mime_types'])) {
                throw new \InvalidArgumentException("Handler '$type' must have 'mime_types' array");
            }
        }

        // Validate memory limit
        if (isset($this->config['processing']['memory_limit'])) {
            $limit = $this->config['processing']['memory_limit'];
            if (!is_null($limit) && (!is_int($limit) || $limit < 0)) {
                throw new \InvalidArgumentException('Memory limit must be null or a positive integer');
            }
        }
    }

    /**
     * Get the entire configuration array
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get a specific configuration value using dot notation
     * 
     * @param string $key Configuration key in dot notation (e.g., 'defaults.jpeg_quality')
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $array = $this->config;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    /**
     * Check if a configuration key exists
     * 
     * @param string $key Configuration key in dot notation
     * @return bool
     */
    public function has(string $key): bool
    {
        return !is_null($this->get($key));
    }
}
