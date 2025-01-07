<?php

namespace Itpathsolutions\Phpinfo;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use File;

class QueryLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (config('app.debug')) { // Enable query logging only in debug mode
            \DB::listen(function ($query){
                static $slowQueryThreshold = 100;
                static $modelTables = null;

                if ($modelTables === null) {
                    $modelTables = collect(File::allFiles(app_path('Models')))
                        ->map(function ($file){
                            $namespace = 'App\\Models\\';
                            $class = $namespace . pathinfo($file->getFilename(), PATHINFO_FILENAME);

                            if (class_exists($class) && is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)) {
                                return (new $class)->getTable();
                            }

                            return null;
                        })
                        ->filter()
                        ->toArray();
                }

                // Extract the table name from the query
                $table = '';
                if (preg_match('/from `(\w+)`/i', $query->sql, $matches)) {
                    $table = $matches[1];
                } elseif (preg_match('/join `(\w+)`/i', $query->sql, $matches)) {
                    $table = $matches[1];
                }

                // Skip queries not related to model tables
                if (!in_array($table, $modelTables)) {
                    return;
                }
                // Log or process the query as needed
                $queryLogs = Cache::get('query_logs', []);

                // Format the SQL query with bindings
                $sqlWithBindings = vsprintf(str_replace('?', '%s', $query->sql), array_map(function ($binding) {
                    return is_numeric($binding) ? $binding : "'$binding'";
                }, $query->bindings));

                $queryLogs[] = [
                    'sql' => $sqlWithBindings,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                    'timestamp' => now(),
                    'threshold' => ($query->time > $slowQueryThreshold) ? 'slow' : null,
                ];

                // Limit logs to the last 100 queries
                if (count($queryLogs) > 100) {
                    array_shift($queryLogs);
                }

                // Store updated logs back in the cache
                \Cache::put('query_logs', $queryLogs, 3600); // Cache for 1 hour
            });
            
        }
    }
}
