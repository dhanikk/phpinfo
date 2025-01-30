<?php

namespace Itpathsolutions\Phpinfo\Http\Controllers;

use App\Http\Controllers\Controller;
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

        $collations = DB::table('information_schema.collations')
        ->select('*')
        ->orderBy('CHARACTER_SET_NAME')
        ->get();
        $metrics = DB::select("
            SHOW GLOBAL STATUS 
            WHERE Variable_name IN (
                'Bytes_received',
                'Bytes_sent',
                'Connections',
                'Aborted_connects',
                'Max_used_connections',
                'Connection_errors_internal',
                'Connection_errors_max_connections',
                'Connection_errors_peer_address',
                'Connection_errors_select',
                'Connection_errors_tcpwrap'
            )
        ");

        // Parse the results
        $metricsData = collect($metrics)->mapWithKeys(function ($item) {
            return [$item->Variable_name => $item->Value];
        });

        // Calculate total traffic
        $bytesReceived = $metricsData['Bytes_received'] ?? 0;
        $bytesSent = $metricsData['Bytes_sent'] ?? 0;
        $totalTraffic = $bytesReceived + $bytesSent;

        $uptime = DB::selectOne("SHOW GLOBAL STATUS WHERE Variable_name = 'Uptime'");

        $uptimeSeconds = $uptime->Value ?? 0;
        $startTime = now()->subSeconds($uptimeSeconds);
        

        $trafficStats = [
            'received' => $this->formatBytes($metricsData['Bytes_received'] ?? 0),
            'sent' => $this->formatBytes($metricsData['Bytes_sent'] ?? 0),
            'received_per_hour' => $this->formatBytes(($metricsData['Bytes_received'] ?? 0) / ($uptimeSeconds / 3600)),
            'sent_per_hour' => $this->formatBytes(($metricsData['Bytes_sent'] ?? 0) / ($uptimeSeconds / 3600)),
            'total_traffic' => $this->formatBytes(($metricsData['Bytes_received'] ?? 0) + ($metricsData['Bytes_sent'] ?? 0)),
            'total_per_hour' => $this->formatBytes((($metricsData['Bytes_received'] ?? 0) + ($metricsData['Bytes_sent'] ?? 0)) / ($uptimeSeconds / 3600)),
            'connections' => $metricsData['Connections'] ?? 0,
            'connections_per_hour' => round(($metricsData['Connections'] ?? 0) / ($uptimeSeconds / 3600), 2),
            'failed_attempts' => $metricsData['Aborted_connects'] ?? 0,
            'failed_per_hour' => round(($metricsData['Aborted_connects'] ?? 0) / ($uptimeSeconds / 3600), 2),
            'max_concurrent_connections' => $metricsData['Max_used_connections'] ?? 0,
            'uptime' => $uptimeSeconds,
            'server_started_at' => $startTime->toDateTimeString(),
        ];
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        $protocol_version = DB::select("SHOW VARIABLES LIKE 'protocol_version'");
        $databaseName = DB::getDatabaseName(); // Get the current database name
        $tables = DB::table('information_schema.tables as t')
        ->selectRaw('
            t.table_name AS `table_name`,
            ROUND(t.data_length / 1024 / 1024, 2) AS `data_size_mb`,
            ROUND(t.index_length / 1024 / 1024, 2) AS `index_size_mb`,
            ROUND((t.data_length + t.index_length) / 1024 / 1024, 2) AS `total_size_mb`,
            t.table_rows AS `total_rows`,
            t.TABLE_TYPE AS `table_type`,
            t.TABLE_COLLATION AS `collation`,
            COUNT(DISTINCT tr.TRIGGER_NAME) AS `trigger_count`,
            COUNT(DISTINCT s.INDEX_NAME) AS `index_count`,
            COUNT(DISTINCT r.ROUTINE_NAME) AS `procedure_count`
        ')
        ->leftJoin('information_schema.triggers as tr', function ($join) use ($databaseName) {
            $join->on('t.table_name', '=', 'tr.EVENT_OBJECT_TABLE')
                ->where('tr.TRIGGER_SCHEMA', '=', $databaseName);
        })
        ->leftJoin('information_schema.statistics as s', function ($join) use ($databaseName) {
            $join->on('t.table_name', '=', 's.TABLE_NAME')
                ->where('s.TABLE_SCHEMA', '=', $databaseName);
        })
        ->leftJoin('information_schema.routines as r', function ($join) use ($databaseName) {
            $join->where('r.ROUTINE_TYPE', '=', 'PROCEDURE')
                ->where('r.ROUTINE_SCHEMA', '=', $databaseName);
        })
        ->where('t.table_schema', $databaseName)
        ->groupBy('t.table_name', 't.TABLE_TYPE', 't.TABLE_COLLATION', 't.table_rows')
        ->orderByRaw('`total_size_mb` DESC')
        ->get();
        $indexes = DB::table('information_schema.statistics as s')
                    ->selectRaw('
                        s.TABLE_NAME as table_name,
                        s.INDEX_NAME as index_name,
                        s.NON_UNIQUE,
                        s.SEQ_IN_INDEX,
                        s.COLUMN_NAME,
                        ROUND(SUM(t.index_length) / COUNT(DISTINCT s.INDEX_NAME) / 1024 / 1024, 2) as index_size_mb
                    ')
                    ->join('information_schema.tables as t', function ($join) use ($databaseName) {
                        $join->on('s.TABLE_SCHEMA', '=', 't.TABLE_SCHEMA')
                            ->on('s.TABLE_NAME', '=', 't.TABLE_NAME')
                            ->where('t.TABLE_SCHEMA', '=', $databaseName);
                    })
                    ->where('s.TABLE_SCHEMA', $databaseName)
                    ->groupBy('s.TABLE_NAME', 's.INDEX_NAME')
                    ->get();
        $collation = DB::select("SELECT @@collation_database AS collation")[0]->collation;
        $totalSize = DB::select('SELECT SUM(data_length + index_length) AS total_size FROM information_schema.tables WHERE table_schema = ?', [$databaseName]);
        $engine = DB::select('SELECT engine FROM information_schema.tables WHERE table_schema = ? LIMIT 1', [$databaseName]);

        $stats = DB::select('
        SELECT 
            (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?) AS table_count,
            (SELECT SUM(table_rows) FROM information_schema.tables WHERE table_schema = ?) AS row_count,
            (SELECT SUM(data_length) / (1024 * 1024) FROM information_schema.tables WHERE table_schema = ?) AS data_size,
            (SELECT SUM(index_length) / (1024 * 1024) FROM information_schema.tables WHERE table_schema = ?) AS index_size,
            (SELECT SUM(data_free) FROM information_schema.tables WHERE table_schema = ?) AS overhead
    ', [$databaseName, $databaseName, $databaseName, $databaseName, $databaseName]);

        // To get the size in bytes
        $totalSizeInBytes = $totalSize[0]->total_size;

        // To convert the size to a more readable format
        $sizeInMB = number_format($totalSizeInBytes / 1024 / 1024, 2);
        $sizeInGB = number_format($totalSizeInBytes / 1024 / 1024 / 1024, 2);

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
            'size' => ['MB' => $sizeInMB, 'GB' => $sizeInGB],
            'engine' => (isset($engine) && is_array($engine)) ? $engine[0]->ENGINE : '',
            'stats' => $stats[0],
            'indexes' => $indexes,
            'trafficStats' => $trafficStats,
            'collations' => $collations
        ];
        return view('phpinfo::database', compact('dbinfo'));
 
    }

    public function showprocesslist(){
        // load processlist
        $processList = DB::select('SHOW PROCESSLIST');
        return response()->json([
            'success' => true,
            'data' => $processList
        ]);
    }

}
