<?php

use Illuminate\Support\Facades\Route;

Route::get('/accounts/{account}', 'AccountController@find');

Route::get('/accounts/{account}/transactions', 'AccountController@list');

Route::post('/accounts/{account}/transactions', 'AccountController@createTransaction');

Route::get('/currencies', 'CurrencyController@list');
