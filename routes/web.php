<?php
    use Illuminate\Support\Facades\Route;
    use Itpathsolutions\Phpinfo\Http\Controllers\PHPServerController;

    Route::get('/php-info', [PHPServerController::class, 'index']);
?>