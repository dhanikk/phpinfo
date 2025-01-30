<?php
    use ips\Phpinfo\Http\Controllers\PHPServerController;
    use Illuminate\Support\Facades\Route;
    Route::get('/php-info', [PHPServerController::class, 'index']);
?>