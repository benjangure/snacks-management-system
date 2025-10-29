<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test API route in web.php
Route::get('/test-web-api', function () {
    return response()->json(['message' => 'Web API route works', 'timestamp' => now()]);
});

// Test if web routes work at all
Route::get('/simple-test', function () {
    return 'Simple web route works!';
});