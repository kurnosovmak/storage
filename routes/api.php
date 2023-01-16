<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExplorerController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\FolderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\TodoListController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::name('customer.')->group(function () {

    Route::prefix('auth/')->name('auth.')->group(function () {

        Route::middleware('guest')
            ->post('login', [AuthController::class, 'login'])->name('login');
        Route::middleware('guest')
            ->post('register', [AuthController::class, 'register'])->name('register');
        Route::middleware('auth:sanctum')
            ->post('logout', [AuthController::class, 'logout'])->name('logout');

    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('me', [ProfileController::class, 'me'])->name('me');

        Route::post('file', [FileController::class, 'store'])->name('file.store');
        Route::get('file/{file}/download', [FileController::class, 'download'])->name('file.download');
        Route::post('file/{file}/public', [FileController::class, 'public'])->name('file.public');
        Route::post('file/{file}/private', [FileController::class, 'private'])->name('file.private');
        Route::put('file/{file}', [FileController::class, 'update'])->name('file.update');
        Route::delete('file/{file}', [FileController::class, 'delete'])->name('file.delete');

        Route::get('explorer/', [ExplorerController::class, 'index'])->name('explorer.show');
        Route::get('explorer/{folder}', [ExplorerController::class, 'show'])->name('explorer.show');

        Route::post('folder', [FolderController::class, 'store'])->name('folder.store');
        Route::get('folder', [FolderController::class, 'showRoot'])->name('folder.showRoot');
        Route::get('folder/{folder}', [FolderController::class, 'show'])->name('folder.show');
        Route::put('folder/{folder}', [FolderController::class, 'update'])->name('folder.update');
        Route::delete('folder/{folder}', [FolderController::class, 'delete'])->name('folder.delete');

    });


    Route::get('file/link/{token}', [FileController::class, 'publicDownload'])->name('file.publicDownload');

});
