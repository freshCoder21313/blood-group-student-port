<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Demo Dashboard Route
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Auth Routes (Placeholder redirect for Demo)
Route::get('/login', function () { return redirect('/dashboard'); })->name('login');
Route::get('/register', function () { return redirect('/dashboard'); })->name('register');
