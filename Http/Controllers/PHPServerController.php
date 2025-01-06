<?php

namespace ips\Phpinfo\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\User;

class PHPServerController extends Controller
{
    public function index()
    {
        $osName = php_uname('s'); // Operating system name (e.g., Windows, Linux)
        $osVersion = php_uname('r');
        $extensionDir = ini_get('extension_dir');

        // Get a list of all files in the extension directory
        $allExtensions = scandir($extensionDir);

        // Filter the files to include only `.so` or `.dll` files
        $availableExtensions = array_filter($allExtensions, function ($file) {
            return preg_match('/\.(so|dll)$/', $file);
        });
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
            'curl_version' => function_exists('curl_version') ? curl_version()['version'] : 'Not Installed',
            'openssl_version' => OPENSSL_VERSION_TEXT,
            'gd_info' => function_exists('gd_info') ? gd_info() : 'Not Installed',
            'mbstring_version' => extension_loaded('mbstring') ? mb_get_info() : 'Not Installed',
            'ioncube_version' => extension_loaded('ionCube Loader') ? ioncube_loader_version() : 'Not Installed',

            // Disk Information
            'available_disk_space' => $this->formatBytes(disk_free_space("/")),
            'total_disk_space' => $this->formatBytes(disk_total_space("/")),

            // Database Information
            'database_connection' => config('database.default'),
            'database_version' => DB::selectOne('SELECT VERSION() as version')->version ?? 'N/A',

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
    }

    public function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1); // Ensure power doesn't exceed the units array
        $value = $bytes / pow(1024, $power);
    
        return round($value, $precision) . ' ' . $units[$power];
    }

    public function isErrorReportingEnabled() {
        $errorReporting = error_reporting();
        $displayErrors = ini_get('display_errors');
    
        // If error_reporting is not 0 and display_errors is "1" or "On"
        return $errorReporting !== 0 && (strtolower($displayErrors) === '1' || strtolower($displayErrors) === 'on');
    }
    
    public function dbinfo() {
        $protocol_version = DB::select("SHOW VARIABLES LIKE 'protocol_version'");
        $databaseName = DB::getDatabaseName(); // Get the current database name
        $tables = DB::table('information_schema.tables')
            ->selectRaw('table_name AS `table_name`, ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS `size`')
            ->where('table_schema', $databaseName)
            ->groupBy('table_name')
            ->orderByRaw('`size` DESC')
            ->get();
        $collation = DB::select("SELECT @@collation_database AS collation")[0]->collation;
        $dbinfo = [
        // Database Information
            'database_connection' => config('database.default'),
            'database_name' => $databaseName,
            'database_version' => DB::selectOne('SELECT VERSION() as version')->version ?? 'N/A',
            'database_characterset' => DB::selectOne('SELECT @@character_set_database as charset'),
            'protocol_version' => (isset($protocol_version) && is_array($protocol_version)) ? $protocol_version[0]->Value : '',
            'database_host' => DB::connection()->getConfig('host'),
            'tables' => $tables,
            'collation' => $collation,
        ];
        return view('phpinfo::database', compact('dbinfo'));
 
    }

}
