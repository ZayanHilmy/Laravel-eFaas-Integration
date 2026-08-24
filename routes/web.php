<?php

use App\Http\Controllers\AuthenticatedSessionController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});




require __DIR__.'/settings.php';

#eFaas login
#Route::get('login', function () {
#    return Socialite::driver('efaas')->enablePKCE()->redirect();
#})->name('login');

#Route::post('/efaas_login/callback', function () {
#    
#    $efaas_user = Socialite::driver('efaas')->enablePKCE()->user();
# 
#    $id_token = $efaas_user->id_token;
#    $sid = $efaas_user->sid;
#
#    session()->put('efaas_id_token', $id_token);
#    session()->put('efaas_sid', $sid);
#    
#    return redirect('welcome');
#});

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/efaas_login/callback', [AuthenticatedSessionController::class, 'callback']);

Route::get('dashboard', function () {

    return view('dashboard');

})->name('dashboard');