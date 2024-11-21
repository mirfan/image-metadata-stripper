<?php

/**
 * MetaStrip Image Configuration File
 * 
 * This file contains all configurable options for the MetaStrip Image library.
 * Copy this file to your project and modify as needed.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Image Processing Options
    |--------------------------------------------------------------------------
    |
    | These options will be used as defaults when processing images.
    | You can override them when calling specific methods.
    |
    */
    'defaults' => [
        // JPEG compression quality (0-100)
        'jpeg_quality' => 85,
        
        // PNG compression level (0-9)
        'png_compression' => 6,
        
        // Whether to preserve ICC color profiles
        'preserve_icc_profile' => false,
        
        // Whether to preserve image transparency
        'preserve_transparency' => true,
        
        // Maximum image dimensions (null for no limit)
        'max_dimension' => null,
        
        // Maximum file size in bytes (null for no limit)
        'max_file_size' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Handlers
    |--------------------------------------------------------------------------
    |
    | Configure which handlers to use for different image types.
    | You can disable specific handlers by setting them to false.
    |
    */
    'handlers' => [
        'jpeg' => [
            'enabled' => true,
            'extensions' => ['jpg', 'jpeg'],
            'mime_types' => ['image/jpeg'],
            'options' => [
                'strip_exif' => true,
                'strip_iptc' => true,
                'strip_xmp' => true,
                'preserve_copyright' => false,
            ],
        ],
        'png' => [
            'enabled' => true,
            'extensions' => ['png'],
            'mime_types' => ['image/png'],
            'options' => [
                'strip_text_chunks' => true,
                'optimize_compression' => true,
            ],
        ],
        'gif' => [
            'enabled' => true,
            'extensions' => ['gif'],
            'mime_types' => ['image/gif'],
            'options' => [
                'preserve_animations' => true,
                'strip_comments' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Behavior
    |--------------------------------------------------------------------------
    |
    | Configure how the library behaves when processing images.
    |
    */
    'processing' => [
        // Whether to process images in memory or use temporary files
        'use_temp_files' => true,
        
        // Directory for temporary files (null for system default)
        'temp_directory' => null,
        
        // Maximum memory limit for image processing (in MB, null for no limit)
        'memory_limit' => 128,
        
        // Timeout for processing a single image (in seconds, 0 for no limit)
        'timeout' => 30,
        
        // Whether to throw exceptions on non-critical errors
        'strict_mode' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging and Debugging
    |--------------------------------------------------------------------------
    |
    | Configure logging and debugging options.
    |
    */
    'logging' => [
        // Whether to enable logging
        'enabled' => true,
        
        // Minimum log level (debug, info, warning, error)
        'level' => 'warning',
        
        // Log file path (null for no file logging)
        'file' => null,
        
        // Whether to log detailed processing information
        'verbose' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Security-related configuration options.
    |
    */
    'security' => [
        // List of allowed image mime types
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
        ],
        
        // Maximum allowed file size (in bytes, null for no limit)
        'max_upload_size' => 10 * 1024 * 1024, // 10MB
        
        // Whether to validate image contents beyond extension
        'validate_contents' => true,
        
        // Whether to sanitize file names
        'sanitize_filenames' => true,
    ],
];
