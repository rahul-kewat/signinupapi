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
        Route::post('user','API\v1\UsersController@register'); //working
        Route::post('login', 'API\v1\UsersController@login'); // tested  but dd used __ still token problem 
        Route::post('forgetpassword', 'API\v1\UsersController@forgetPassword'); // twilio problem
        Route::post('checkotp', 'API\v1\UsersController@checkOtp'); //working
        Route::post('sendotp', 'API\v1\UsersController@sendOtp'); 
        Route::put('updateforgetpassword', 'API\v1\UsersController@updateForgetPassword');//tested
        
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
        Route::get('category', 'API\v1\ServicesController@index');
        Route::post('addReview', 'API\v1\BookingController@addReview');
        Route::get('getServices', 'API\v1\ServicesController@get_all_services');
        Route::get('doctor/{id}', 'API\v1\ServicesController@getUserById');
        Route::post('checkPhoneOtp', 'API\v1\UsersController@checkPhoneOtp');
        Route::post('upload', 'API\v1\MediaController@uploadMedia');
        Route::post('appointment', 'API\v1\BookingController@bookService');
        Route::get('appointment', 'API\v1\BookingController@getAppointments');
        Route::get('reports', 'API\v1\BookingController@reportList');
        Route::get('slots', 'API\v1\venderSlotController@getSlots');
        Route::post('card', 'API\v1\CardController@addCard');
        Route::get('card', 'API\v1\CardController@getCard');
        Route::post('uploadImage', 'API\v1\UsersController@uploadImage');
        Route::get('mydoctors', 'API\v1\ServicesController@mydoctors');
        Route::get('faq', 'API\v1\FaqController@getFaq');
        Route::get('coupons', 'API\v1\CouponController@getCoupons');
        Route::post('validate_coupon', 'API\v1\CouponController@validateCoupon');
        Route::get('notification', 'API\v1\NotificationController@getNotifications');
        Route::patch('appointment', 'API\v1\BookingController@updateStatus');
        Route::get('doctor_reports', 'API\v1\BookingController@doctorReports');
        Route::get('doctor_reports_list', 'API\v1\BookingController@doctorReportsList');
        Route::patch('notification', 'API\v1\NotificationController@updateStatus');
    });
        
});

});
