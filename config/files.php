<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the file upload module
    |
    */

    /**
     * Maximum file size in kilobytes
     */
    'max_file_size' => env('FILE_MAX_SIZE', 10240), // 10MB default

    /**
     * Allowed file types and their MIME types
     */
    'allowed_types' => [
        'p12' => ['application/x-pkcs12'],
        'pfx' => ['application/x-pkcs12'],
        'pdf' => ['application/pdf'],
        'xml' => ['application/xml', 'text/xml'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'xls' => ['application/vnd.ms-excel'],
        'csv' => ['text/csv', 'text/plain'],
        'txt' => ['text/plain'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'zip' => ['application/zip'],
        'rar' => ['application/x-rar-compressed'],
    ],

    /**
     * Default expiration times for different file types (in minutes)
     */
    'default_expiration' => [
        'p12' => 120,  // 2 hours - certificados digitales
        'pfx' => 120,  // 2 hours - certificados digitales
        'pdf' => 1440, // 24 hours - documentos
        'xml' => 720,  // 12 hours - facturas electrónicas
        'default' => 1440, // 24 hours - otros archivos
    ],

    /**
     * Maximum expiration time allowed (in minutes)
     */
    'max_expiration' => env('FILE_MAX_EXPIRATION', 1440), // 24 hours

    /**
     * Storage disk to use for uploaded files
     */
    'storage_disk' => env('FILE_STORAGE_DISK', 'local'),

    /**
     * Base upload directory
     */
    'upload_directory' => 'uploads',

    /**
     * Enable automatic cleanup of expired files
     */
    'auto_cleanup_enabled' => env('FILE_AUTO_CLEANUP', true),

    /**
     * Probability (in percentage) of automatic cleanup on each request
     * Set to 0 to disable. Recommended: 10 (10% chance)
     */
    'cleanup_probability' => env('FILE_CLEANUP_PROBABILITY', 10),

    /**
     * Enable encryption for sensitive files (P12, PFX)
     * Requires Laravel encryption to be configured
     */
    'encrypt_sensitive_files' => env('FILE_ENCRYPT_SENSITIVE', false),

    /**
     * Sensitive file types that should be encrypted if encryption is enabled
     */
    'sensitive_file_types' => ['p12', 'pfx'],

    /**
     * Enable file scanning (requires ClamAV or similar)
     */
    'enable_virus_scan' => env('FILE_ENABLE_VIRUS_SCAN', false),

    /**
     * Organize files by user ID
     */
    'organize_by_user' => true,

    /**
     * Organize files by type
     */
    'organize_by_type' => true,
];
