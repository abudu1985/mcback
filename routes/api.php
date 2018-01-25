<?php

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Route;

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

Route::post('/quote', [
    'uses' => 'QuoteController@postQuote',
    'middleware' => 'auth.jwt'
]);
Route::get('/quotes', [
    'uses' => 'QuoteController@getQuotes'
]);
Route::put('/quote/{id}', [
    'uses' => 'QuoteController@putQuote',
    'middleware' => 'auth.jwt'
]);
Route::delete('/quote/{id}', [
    'uses' => 'QuoteController@deleteQuote',
    'middleware' => 'auth.jwt'
]);
Route::post('/user/signin', [
    'uses' => 'UserController@signin'
]);
Route::post('/users', [
    //'uses' => 'UserController@signUp'
    'uses' => 'AuthController@register'
]);
Route::post('/recover', [
    //'uses' => 'UserController@signUp'
    'uses' => 'AuthController@recover'
]);
Route::get('/users/{email}', [
    'uses' => 'UserController@findEmail'
]);
Route::post('/events', [
    'uses' => 'EventController@postEvent'
   // 'middleware' => 'auth.jwt'
]);
Route::get('/events', [
    'uses' => 'EventController@getEvents'
]);
Route::get('/events/{id}', [
    'uses' => 'EventController@getEventById'
]);
Route::get('/categories', [
    'uses' => 'CategoryController@getCategories',
    //'middleware' => 'auth.jwt'
]);
Route::post('/categories', [
    'uses' => 'CategoryController@postCategory'
]);
Route::put('/categories/{id}', [
    'uses' => 'CategoryController@putCategory',
    //'middleware' => 'auth.jwt'
]);
Route::get('/categories/{id}', [
    'uses' => 'CategoryController@getCategoryById',
    //'middleware' => 'auth.jwt'
]);
Route::get('/bill', [
    'uses' => 'BillController@getBill'
]);

Route::get('/bill-rates', [
    'uses' => 'BillController@getRateFromFinance'
]);

Route::put('/bill', [
    'uses' => 'BillController@putBill',
    //'middleware' => 'auth.jwt'
]);


Route::post('register', 'AuthController@register');

Route::post('login', 'AuthController@login');

Route::post('recover', 'AuthController@recover');

Route::post('resetpas', 'AuthController@resetpas');




Route::group(['middleware' => ['auth.jwt']], function() {

    Route::get('logout', 'AuthController@logout');



    Route::get('test', function(){

        return response()->json(['foo'=>'bar']);

    });

});