<?php
/**
 * Environment Variables Handler
 * Simple class to load and manage environment variables from .env file
 */

class EnvLoader {
    private static $loaded = false;
    private static $variables = [];

    /**
     * Load environment variables from .env file
     */
    public static function load($path = '.env') {
        if (self::$loaded) {
            return;
        }

        if (!file_exists($path)) {
            // Try to load from .env.example if .env doesn't exist
            if (file_exists('.env.example')) {
                error_log("Warning: .env file not found. Please copy .env.example to .env and configure your values.");
            }
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE format
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                self::$variables[$key] = $value;
                
                // Also set as environment variable if not already set
                if (!getenv($key)) {
                    putenv("$key=$value");
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable
     */
    public static function get($key, $default = null) {
        self::load();
        
        // First check our loaded variables
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }
        
        // Then check system environment
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }

    /**
     * Check if environment variable exists
     */
    public static function has($key) {
        self::load();
        return isset(self::$variables[$key]) || getenv($key) !== false;
    }

    /**
     * Get all loaded variables
     */
    public static function all() {
        self::load();
        return self::$variables;
    }
}

// Auto-load .env file when this file is included
EnvLoader::load();
?>
