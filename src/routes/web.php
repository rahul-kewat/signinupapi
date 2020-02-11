<?php
use Illuminate\Http\Request;



Route::group(['namespace' => 'Devrahul\Signinupapi\Http\Controllers'],function(){

   

    

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

Route::group(['middleware' => ['api']], function() {
    //Api v1 version routes define inside it so we can able to manage the version
    Route::prefix('v1')->group(function () {
        Route::post('user','API\v1\UsersController@register'); //done
        Route::post('login', 'API\v1\UsersController@login'); // done
        Route::post('forgetpassword', 'API\v1\UsersController@forgetPassword'); // twilio problem
        Route::post('checkotp', 'API\v1\UsersController@checkOtp'); //working
        Route::post('sendotp', 'API\v1\UsersController@sendOtp'); 
        Route::put('updateforgetpassword', 'API\v1\UsersController@updateForgetPassword');//tested
        Route::post('sociallogin', 'API\v1\UsersController@socialLogin');
        
    });
   
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['api']], function() {
    //Api v1 version routes define inside it so we can able to manage the version.
    Route::prefix('v1')->group(function () {


       

        Route::put('updatepassword', 'API\v1\UsersController@updatePassword');
        Route::get('logout', 'API\v1\UsersController@logout');
        Route::put('user', 'API\v1\UsersController@editUser');
        Route::get('user', 'API\v1\UsersController@getUsers');
        Route::post('addReview', 'API\v1\BookingController@addReview');
        Route::post('checkPhoneOtp', 'API\v1\UsersController@checkPhoneOtp');
        Route::post('uploadImage', 'API\v1\UsersController@uploadImage');
        
    });
        
});

});
