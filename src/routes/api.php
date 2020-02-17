<?php


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

use Illuminate\Http\Request;

Route::prefix('v1')->group(function () {
    Route::post('user','API\v1\UsersController@register'); 
    Route::post('login', 'API\v1\UsersController@login'); 
    Route::post('sociallogin', 'API\v1\UsersController@socialLogin');
    Route::post('checkotp', 'API\v1\UsersController@checkOtp');
    Route::post('sendotp', 'API\v1\UsersController@sendOtp'); 
    Route::post('checkPhoneOtp', 'API\v1\UsersController@checkPhoneOtp');
    Route::post('forgetpassword', 'API\v1\UsersController@forgetPassword');     
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:api']], function() {
    Route::prefix('v1')->group(function () {
        Route::put('updateforgetpassword', 'API\v1\UsersController@updateForgetPassword');
        Route::put('updatepassword', 'API\v1\UsersController@updatePassword');
        Route::get('logout', 'API\v1\UsersController@logout');
        Route::put('user', 'API\v1\UsersController@editUser');
        Route::get('user', 'API\v1\UsersController@getUsers');
        Route::post('uploadImage', 'API\v1\UsersController@uploadImage');    
    });
   
});



