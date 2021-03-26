<?php

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

//user section
Route::group(['prefix' => 'user','namespace' => 'User'],function() {

    Route::post('/login', 'UserController@login');
    Route::post('/register', 'UserController@register');
    Route::get('/profile-details/{userid?}', 'UserController@profileDetails');
    Route::post('/contact-add', 'UserController@addContact');
    Route::get('/contact-view/{contactid?}', 'UserController@viewContact');
    Route::get('/contact-all/{userid?}', 'UserController@allContact');
    Route::post('/contact-edit', 'UserController@editContact');
    Route::get('/contact-delete/{contactid?}', 'UserController@deleteContact');
    Route::get('/logout/{userid}', 'UserController@logout');

});

//Admin section
Route::group(['prefix' => 'admin','namespace' => 'Admin'],function() {

    Route::post('/login', 'AdminController@login');
    Route::post('/user-add', 'AdminController@addUser');
    Route::get('/user-view/{adminid?}/{userid?}', 'AdminController@viewUser');
    Route::get('/user-all/{adminid?}', 'AdminController@allUsers');
    Route::post('/user-edit', 'AdminController@editUser');
    Route::get('/user-delete/{adminid?}/{userid?}', 'AdminController@deleteUser');

    Route::post('/user-contact-add', 'AdminController@addUserContact');
    Route::get('/user-contact-all/{adminid?}/{userid?}', 'AdminController@viewUserAllContact');
    Route::get('/user-contact-view/{adminid?}/{contactid?}', 'AdminController@viewUserContact');
    Route::post('/user-contact-edit', 'AdminController@editUserContact');
    Route::get('/user-contact-delete/{adminid?}/{contactid?}', 'AdminController@deleteUserContact');
    Route::get('/dashboard/{adminid?}', 'AdminController@dashboard');
    Route::get('/logout/{adminid}', 'AdminController@logout');

});
