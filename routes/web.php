<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'StaticPagesController@home')->name('home');
Route::get('/help', 'StaticPagesController@help')->name('help');
Route::get('/about', 'StaticPagesController@about')->name('about');

// 用户注册
Route::get('signup', 'UsersController@create')->name('signup');
// 用户资源路由
Route::resource('users', 'UsersController');
// 用户注册时候激活
Route::get('signup/confirm/{token}', 'UsersController@confirmEmail')->name('confirm_email');
// 关注者列表
Route::get('/users/{user}/followings', 'UsersController@followings')->name('users.followings');
// 粉丝列表
Route::get('/users/{user}/followers', 'UsersController@followers')->name('users.followers');

// 用户登陆
Route::get('login', 'SessionsController@create')->name('login');
Route::post('login', 'SessionsController@store')->name('login');
// 用户退出
Route::delete('logout', 'SessionsController@destroy')->name('logout');

// 显示重置密码的邮箱发送页面
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
// 邮箱发送重设链接
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
// 密码更新页面
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
// 	执行密码更新操作
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

// 发布和删除微博
Route::resource('statuses', 'StatusesController', ['only' => ['store', 'destroy']]);

// 关注用户
Route::post('/users/followers/{user}', 'FollowersController@store')->name('followers.store');
// 取消关注用户
Route::delete('/users/followers/{user}', 'FollowersController@destroy')->name('followers.destroy');