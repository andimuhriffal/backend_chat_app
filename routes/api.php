<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rute untuk autentikasi
Route::post('login', [AuthController::class, 'login'])->name('login'); // Login user
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); // Logout user
Route::post('register', [AuthController::class, 'register']); // Registrasi user


// Rute yang memerlukan autentikasi
Route::middleware('auth:sanctum')->group(function () {
    // Profil pengguna

    Route::get('/user', function (Request $request) {
        return $request->user(); // Mengembalikan data user yang sedang login
    });
    Route::post('/update-profile', [ProfileController::class, 'updateProfile']); // Update profil

    // Fitur "Friends"
    Route::get('friends', [FriendsController::class, 'index']); // Daftar teman
    Route::post('send-message', [FriendsController::class, 'sendMessage']); // Kirim pesan
    Route::get('messages', [FriendsController::class, 'getMessages']); // Ambil pesan
    Route::get('/friend-info/{friendId}', [FriendsController::class, 'getFriendInfo']); // Info teman
});

