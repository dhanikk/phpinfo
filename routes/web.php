<?php
    use ips\Phpinfo\Http\Controllers\PHPServerController;
    use Illuminate\Support\Facades\Route;
    Route::get('/dashboard/php-info', [PHPServerController::class, 'index']);
    Route::get('/dashboard/database-info', [PHPServerController::class, 'dbinfo']);
?>