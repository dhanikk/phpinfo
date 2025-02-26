<?php

namespace Itpathsolutions\Phpinfo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PHPServerController extends Controller
{
    public function index(): View
    {
        try {
            $osName = php_uname('s'); // Operating system name (e.g., Windows, Linux)
            $osVersion = php_uname('r');
            $extensionDir = ini_get('extension_dir');

            // Get a list of all files in the extension directory
            $allExtensions = !empty($extensionDir) && is_dir($extensionDir) ? scandir($extensionDir) : [];

            // Filter the files to include only `.so` or `.dll` files
            $availableExtensions = is_array($allExtensions) ? array_filter($allExtensions, function ($file): bool { 
                                        return preg_match('/\.(so|dll)$/', $file) === 1; 
                                    }) : [];

            $database_version = DB::selectOne('SELECT VERSION() as version');

            $phpinfo = [
                // Basic PHP Information
                'php_version' => phpversion(),
                'zend_version' => zend_version(),
                'php_api' => php_sapi_name(),
                'php_ini' => php_ini_loaded_file() ?: 'N/A',
                'php_ini_scanned_files' => php_ini_scanned_files() ?: 'N/A',
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
                'server_os' => $osName . ' ' . $osVersion,
                'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
                'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',

                // PHP Configuration Limits
                'max_execution_time' => ini_get('max_execution_time') . ' Seconds',
                'max_input_time' => ini_get('max_input_time'),
                'memory_limit' => ini_get('memory_limit'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'max_input_vars' => ini_get('max_input_vars'),
                'max_file_uploads' => ini_get('max_file_uploads'),
                'default_socket_timeout' => ini_get('default_socket_timeout'),

                // PHP Directives
                'default_timezone' => date_default_timezone_get(),
                'display_errors' => ini_get('display_errors') ? 'On' : 'Off',
                'log_errors' => ini_get('log_errors') ? 'On' : 'Off',
                'error_reporting_level' => error_reporting(),
                'error_log' => ini_get('error_log') ?: 'N/A',

                // PHP Session Information
                'session_save_path' => ini_get('session.save_path'),
                'session_timeout' => ini_get('session.gc_maxlifetime') . ' seconds',

                // PHP Extensions & Libraries
                'loaded_extensions' => get_loaded_extensions(),
                'curl_version' => function_exists('curl_version') && is_array(curl_version()) ? curl_version()['version'] : 'Not Installed',
                'openssl_version' => OPENSSL_VERSION_TEXT,
                'gd_info' => function_exists('gd_info') ? gd_info() : 'Not Installed',
                'mbstring_version' => extension_loaded('mbstring') ? mb_get_info() : 'Not Installed',
                'ioncube_version' => extension_loaded('ionCube Loader') && function_exists('ioncube_loader_version') ? ioncube_loader_version() : 'Not Installed',

                // Disk Information
                'available_disk_space' => $this->formatBytes((int) (disk_free_space("/") ?: 0)),
                'total_disk_space' => $this->formatBytes((int) (disk_total_space("/") ?: 0)),

                // Database Information
                'database_connection' => config('database.default'),
                'database_version' => is_object($database_version) && property_exists($database_version, 'version') ? $database_version->version : 'N/A',

                // Open Basedir Restriction
                'open_basedir' => ini_get('open_basedir') ?: 'Not Set',

                // PHP Constants
                'PHP_OS' => PHP_OS,
                'PHP_OS_FAMILY' => PHP_OS_FAMILY,
                'PHP_INT_SIZE' => PHP_INT_SIZE,
                'PHP_INT_MAX' => PHP_INT_MAX,
                'PHP_INT_MIN' => PHP_INT_MIN,
                'PHP_SAPI' => php_sapi_name(),
                'PHP_EOL' => PHP_EOL,

                // Server Information
                'apache_version' => function_exists('apache_get_version') ? apache_get_version() : 'Not Installed',
                'apache_modules' => function_exists('apache_get_modules') ? apache_get_modules() : 'Not Available',

                // Other Information
                'zend_extensions' => array_filter(get_loaded_extensions(), function ($ext) {
                    return strpos($ext, 'zend') === 0;
                }),
                'php_error_reporting' => $this->isErrorReportingEnabled() ? "On" : "Off",
                'php_timezone' => date_default_timezone_get(),
                'date_default_timezone' => ini_get('date.timezone'),
                'php_modules' => implode(', ', get_loaded_extensions()),
                'all_available_extensions' => preg_replace('/\.dll$/', '', preg_replace('/^php_/', '', $availableExtensions)) 
            ];
            return view('phpinfo::home', compact('phpinfo'));
        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage();
        
            // Custom error messages
            if (strpos($errorMessage, 'scandir()') !== false) {
                $errorMessage = "Unable to retrieve PHP extensions. Please check if PHP installation and extension directory are correctly configured.";
            } elseif (strpos($errorMessage, 'SQLSTATE[HY000]') !== false) {
                $errorMessage = "Database connection failed! Please check your database credentials.";
            } elseif (strpos($errorMessage, 'Call to undefined function curl_version()') !== false) {
                $errorMessage = "cURL extension is missing. Please enable it in the PHP configuration.";
            } elseif (strpos($errorMessage, 'Call to undefined function gd_info()') !== false) {
                $errorMessage = "GD extension is not installed. Please enable it in the PHP configuration.";
            } elseif (strpos($errorMessage, 'disk_free_space()') !== false) {
                $errorMessage = "Disk space information is restricted due to server security settings. Contact your administrator to adjust the `open_basedir` setting.";
            } elseif (strpos($errorMessage, 'session_start()') !== false) {
                $errorMessage = "Session storage path is misconfigured. Verify that the session save path exists and is writable.";
            } elseif (strpos($errorMessage, 'ini_get()') !== false) {
                $errorMessage = "Some PHP settings are restricted by server security settings. Contact your administrator to adjust configurations.";
            } elseif (strpos($errorMessage, 'Undefined index') !== false) {
                $errorMessage = "Unable to fetch server details. Some required environment variables are missing.";
            } else if ($th instanceof \PDOException) {
                $errorMessage = 'Database connection failed! Please check your database credentials.';
            } else {
                $errorMessage = "An unexpected error occurred: " . $errorMessage;
            }
            return view('phpinfo::home')->withErrors(['error' => $errorMessage]);

        }
        
    }

    public function formatBytes(int $bytes, int $precision = 2) : string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1); // Ensure power doesn't exceed the units array
        $value = $bytes / pow(1024, $power);
    
        return round($value, $precision) . ' ' . $units[$power];
    }

    public function isErrorReportingEnabled(): bool
    {
        $errorReporting = error_reporting();
        $displayErrors = ini_get('display_errors');
        $displayErrors = is_string($displayErrors) ? strtolower($displayErrors) : '0';
        // If error_reporting is not 0 and display_errors is "1" or "On"
        return $errorReporting !== 0 && ($displayErrors === '1' || $displayErrors === 'on');
    }

    public function showprocesslist(): JsonResponse
    {
        // load processlist
        $processList = DB::select('SHOW PROCESSLIST');
        return response()->json(['success' => true,'data' => $processList]);
    }
}
