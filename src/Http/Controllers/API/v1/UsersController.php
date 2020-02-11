<?php

namespace Devrahul\Signinupapi\Http\Controllers\API\v1;


use Symfony\Component\HttpFoundation\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Devrahul\Signinupapi\Models\User;
use Devrahul\Signinupapi\Models\UserAddresses;
use Devrahul\Signinupapi\Models\VenderService;
use App\Mail\Activate;
use App\Mail\ForgetPassword;
use App\Mail\UpdateEmail;
use Illuminate\Support\Facades\Auth;
use Devrahul\Signinupapi\Models\User_activation as Activation;
use Lcobucci\JWT\Parser;
use Devrahul\Signinupapi\Models\DeviceDetails;
use Devrahul\Signinupapi\Models\Role;
use Devrahul\Signinupapi\Resources\User as UserResource;
use App\Http\Resources\RolesResourceCollection;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Support\Facades\Input;
use App\Rules\PhoneNumber;
use App\Services\PushNotification;
use App\Services\ScheduleService;
use Devrahul\Signinupapi\Models\phoneOtp;
use Aloha;
use Illuminate\Support\Facades\DB;
use Devrahul\Signinupapi\Models\slot;
use Devrahul\Signinupapi\Models\Notification;
use Devrahul\Signinupapi\Requests\RegisterUser;
use Devrahul\Signinupapi\Requests\Login;
use Devrahul\Signinupapi\Requests\UpdatePassword;
use Devrahul\Signinupapi\Requests\ForgetPasswordReq;
use Devrahul\Signinupapi\Requests\CheckPasswordOtp;

use Devrahul\Signinupapi\Requests\UpdateForgetPass;
use Devrahul\Signinupapi\Requests\CheckOtpPhone;
use Devrahul\Signinupapi\Requests\SocialRequest;
use Devrahul\Signinupapi\Requests\SendOtp;
use Devrahul\Signinupapi\Resources\UserProfile;
use Devrahul\Signinupapi\Requests\ProfileRequest;
use Hash;
use App;

use Devrahul\Signinupapi\Traits\ApiUserTrait;

class UsersController extends Controller {

    use ApiUserTrait;

    protected $response = [
        'status' => 0,
        'message' => '',
    ];

    public function __construct() {
        $this->response['data'] = new \stdClass();
    }

    private function generate_random_string() {
        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
                . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                . '0123456789'); // and any other characters
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, 5) as $k)
            $rand .= $seed[$k];

        return $rand;
    }

    /**
     * @SWG\Post(
     *     path="/sendotp",
     *     tags={"Otp"},
     *     summary="Send Otp to verify phone number",
     *     description="Send Otp to verify phone number while Register process",
     *     operationId="sendOtp",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Send Otp Object",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="phone_country_code",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="phone_number",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="email",
     *              type="string"
     *             )
     *         )    
     *     ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */
    public function sendOtp(SendOtp $request) {

        try{
            //Twilio Integration and OTP Flow starts here
            $otp = rand(1000, 9999);
            $phone = $request['phone_country_code'] . $request['phone_number'];
         
            $twilio = new Aloha\Twilio\Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_SMS_FROM_NUMBER'));
            
            if ($twilio->message($phone, 'Otp is ' . $otp)) {
                $user_otp = phoneOtp::where('phone_no', $request['phone_number'])->first();
                
                if ($user_otp) {
                    $user_otp->update(['otp' => $otp]);
                    $id = $user_otp->id;
                } else {
                    $phoneOtp = phoneOtp::create(['phone_no' => $request['phone_number'], 'otp' => $otp,'phone_country_code' =>  $request['phone_country_code']]);                  $id = $phoneOtp->id;  
                }

                return response()->json([
                    'status' => 1,
                    'message' => "Otp successfully sent to phone number.",
                    'data' => [
                        'id' => (int) $id
                    ]
                ]);
            }
            
            return response()->json([
                'status' => 0,
                'message' => "Something issue with Twilio Server."
            ]);

        }
        catch (\Exception $ex) {
                
            $this->response['message'] = $ex->getMessage();
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Post(
     *     path="/user",
     *     tags={"Users"},
     *     summary="Create user",
     *     description="Register new user here",
     *     operationId="createUser",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Create User object",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="firstname",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="email",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="password",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="password_confirmation",
     *              type="string"
     *             ),  
     *             @SWG\Property(
     *              property="phone_country_code",
     *              type="string"
     *             ),   
     *             @SWG\Property(
     *              property="phone_number",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="otp",
     *              type="string"
     *             )
     *         )    
     *     ),
     *     @SWG\Parameter(
     *            name="device-token",
     *            in="header",
     *            description="Device Token",
     *            type="string"
     *     ),  
     *     @SWG\Parameter(
     *            name="device-id",
     *            in="header",
     *            description="Device Id",
     *            type="string"
     *     ),  
     *     @SWG\Parameter(
     *            name="build-version",
     *            in="header",
     *            description="Build Version",
     *            type="string"
     *     ),  
     *     @SWG\Parameter(
     *            name="platform",
     *            in="header",
     *            description="Platform",
     *            type="string"
     *     ), 
     *     @SWG\Parameter(
     *            name="build",
     *            in="header",
     *            description="Build",
     *            type="string"
     *     ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */

    public function register(RegisterUser $request) {
        
        try {
            
            //Check phone otp before register new user to database
            $phone_otp = phoneOtp::where('phone_no', $request['phone_number'])->first();

            if($phone_otp->otp != $request['otp']){ 
                return response()->json([
                    'status' => 0,
                    'message' => "Please provide valid OTP."
                ],422);
            }

            $request['status'] = User::active;
            $user = User::create($request->all());       
            // $user->accessRole(1);
            
            $token = $user->createToken('Api access token')->accessToken;
           
            $this->insertDeviceDetails($token, $user->id);
                        
            return (new UserResource($user, $token))->additional([
                        'status' => 1,
                        'message' => trans('api/user.registered_successfully')
            ]);

        } catch (\Exception $ex) {
            
            $this->response['message'] = $ex->getMessage();
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Post(
     *     path="/login",
     *     tags={"Users"},
     *     summary="Login user",
     *     description="Validate and login user using API's",
     *     operationId="loginUser",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Login User object",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="email",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="password",
     *              type="string"
     *             )
     *         )
     *     ),
     *     @SWG\Parameter(
     *            name="device-token",
     *            in="header",
     *            description="Device Token",
     *            type="string"
     *     ),  
     *     @SWG\Parameter(
     *            name="device-id",
     *            in="header",
     *            description="Device Id",
     *            type="string"
     *     ),  
     *     @SWG\Parameter(
     *            name="build-version",
     *            in="header",
     *            description="Build Version",
     *            type="string"
     *     ),  
     *     @SWG\Parameter(
     *            name="platform",
     *            in="header",
     *            description="Platform",
     *            type="string"
     *     ), 
     *     @SWG\Parameter(
     *            name="build",
     *            in="header",
     *            description="Build",
     *            type="string"
     *     ),   
     *     @SWG\Parameter(
     *            name="language",
     *            in="header",
     *            description="Pass country code",
     *            type="string"
     *     ),  
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */

    public function login(Login $request) {
        
        try {
                      
            $isValidatePhone = User::where('phone_number',$request['email'])->first(); 
            
            $email = ($isValidatePhone) ? $isValidatePhone->email : $request['email'];
            
            if (Auth::attempt(['email' => $email, 'password' => $request['password']],true)) {
                
                $user = Auth::user();
                //Password matched successfully
                
                
                $token = Auth::user()->createToken('Api access token')->accessToken;
               
                $this->insertDeviceDetails($token, $user->id);
                
                if ($user->status == User::inActive) {

                    return (new UserResource($user, $token))->additional([
                                'status' => 0,
                                'message' => trans('api/user.activate_account_first')
                    ]);
                }

                return (new UserResource($user, $token))->additional([
                            'status' => 1,
                            'message' => trans('api/user.loggedin_successfully')
                ]);

            } else {

                /// Password didn't match
                
                $this->response['message'] = trans('api/user.password_match');
                return response($this->response, 422);
            }
        } catch (\Exception $ex) {
            
            // $this->response['message'] = trans('api/user.something_wrong');
            dd( $ex->getMessage());
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Put(
     *     path="/updatepassword",
     *     tags={"Users"},
     *     summary="Update password",
     *     description="Update user password using API's",
     *     operationId="updatePassword",
     *      @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         type="string"
     *      ),
     *      @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Login User Update Password object",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="password",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="old_password",
     *              type="string"
     *             )
     *         )
     *      ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token And Unauthenticated"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */

    public function updatePassword(UpdatePassword $request) {

        $usr = Auth::User();
        try {
            $user = User::where('id', $usr->id)->first();
            if (!$user) {
                $this->response['message'] = trans('api/user.user_not_found');
                return response($this->response, 422);
            }


            $old_password = $request['old_password'];
            if ($user) {
                if (!Hash::check($old_password, $user->password)) {
                    $this->response['message'] = trans('api/user.old_password_not_matched');
                    return response($this->response, 422);
                }
            }
            $user->update(['password' => $request['password']]);
            $this->response['message'] = trans('api/user.password_updated');
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Get(
     *     path="/user",
     *     tags={"Users"},
     *     summary="Get login user detail",
     *     description="Get user detail by id using API's",
     *     operationId="getUserDetails",
     *      @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         type="string"
     *      ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token And Unauthenticated"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */
    public function getUsers(Request $request){
        
        try {
            $this->response['status'] = 1;
            $this->response['message'] = "User details";
            return (new UserProfile(Auth::User()))->additional([
                'status' => 1,
                'message' => trans('api/user.user_updated')
            ]);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }

    }
    /**
     * @SWG\Get(
     *     path="/logout",
     *     tags={"Users"},
     *     summary="Logout User",
     *     description="Logout user using API's",
     *     operationId="logout",
     *      @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         type="string"
     *     ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token And Unauthenticated"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */

    public function logout(Request $request) {
        try {
            $user = Auth::user();
            $user->update(['online' => '0']);
            $value = $request->bearerToken();
            $id = (new Parser())->parse($value)->getHeader('jti');
            $deviceDetails = DeviceDetails::where('access_token_id', $id)->first();
            if($deviceDetails){
                $deviceDetails->delete();
            }
            $token = $request->user()->tokens->find($id);
            $token->revoke();
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.loggedout_successfully');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Post(
     *     path="/forgetpassword",
     *     tags={"Users"},
     *     summary="Forget Password",
     *     description="Forget Password request for user",
     *     operationId="forgetPassword",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Forget Password object",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="phone_no",
     *              type="string"
     *             )
     *         )
     *     ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */

    public function forgetPassword(ForgetPasswordReq $request) {
        
        try {
            $user = User::where('phone_number', $request['phone_no'])->first();
            $user['password_otp'] = rand(1000, 9999);
            $user->update(['password_otp' => $user['password_otp']]);
            $phone_no = $user->phone_country_code.$request['phone_no'];
            
            $twilio = new Aloha\Twilio\Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_SMS_FROM_NUMBER'));
            if ($user['password_otp']) {
                if ($twilio->message($phone_no, 'Otp is ' . $user['password_otp'])) {
                    $this->response['message'] = trans('api/user.otp_sent');
                    $this->response['data'] = (object) array('id' => $user->id);
                    $this->response['status'] = 1;
                    return response($this->response, 200);
                   
                }
            }
        } catch (\Exception $ex) {
            $this->response['message'] = $ex->getMessage();
            //$this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Post(
     *     path="/checkotp",
     *     tags={"Users"},
     *     summary="Check Password Otp",
     *     description="Check Password OTP for user",
     *     operationId="checkPasswordOtp",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Check Password Otp Object",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="otp",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="id",
     *              type="integer"
     *             )
     *         )
     *     ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */

    public function checkOtp(CheckPasswordOtp $request) {
        dd("dfd");
        try {
            
            $user = User::where('password_otp', $request['otp'])->where('id', $request['id'])->first();
            
            if (!$user) {
                $this->response['message'] = trans('api/user.invalid_otp');
                return response($this->response, 422);
            }

            $this->response['message'] = trans('api/user.otp_matched');
            $this->response['data'] = (object) array('id' => $user->id);
            $this->response['status'] = 1;
            return response($this->response, 200);
            
        } catch (\Exception $ex) {

            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Put(
     *     path="/user",
     *     tags={"Users"},
     *     summary="Update user details",
     *     description="Update user details using API's",
     *     operationId="editUser",
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required = true,
     *         description="Authorization Token",
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Login User Update Password object",
     *         required = true,
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="name",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="phone_country_code",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="phone_number",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="gender",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="blood_group",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="otp",
     *              type="string"
     *             )
     *         )
     *     ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token And Unauthenticated"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */
    public function editUser(EditUserProfile $request) {
        try{
            $user = Auth::user();
            $user->firstname = Input::get('name');
            if(Input::get('otp')){
                $user->phone_country_code = Input::get('phone_country_code');
                $user->phone_number = Input::get('phone_number');
            }
            $user->gender = Input::get('gender');
            $user->blood_group = Input::get('blood_group');
            $user->save();
            return (new UserResource($user))->additional([
                        'status' => 1,
                        'message' => trans('api/user.user_updated')
            ]);

        }
        catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            //$this->response['message'] = $ex->getMessage();
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Put(
     *     path="/updateforgetpassword",
     *     tags={"Users"},
     *     summary="Update forget password",
     *     description="Update forget password using API's",
     *     operationId="forgetPasswordUser",
     *      @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Update forget password object",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="user_id",
     *              type="integer"
     *             ),
     *             @SWG\Property(
     *              property="password",
     *              type="string"
     *             )
     *         )
     *     ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token And Unauthenticated"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */
    public function updateForgetPassword(UpdateForgetPass $request) {

        try {
            $user = User::find($request['user_id']);
            $user->update(['password' => $request['password']]);
            $this->response['message'] = trans('api/user.password_updated');
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Post(
     *     path="/checkPhoneOtp",
     *     tags={"Otp"},
     *     summary="Check otp after verify api call",
     *     description="Check Otp while edit profile using API's",
     *     operationId="checkOtpEdit",
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="check otp object",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="otp",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="id",
     *              type="integer"
     *             )
     *         )
     *     ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token And Unauthenticated"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */
    public function checkPhoneOtp(CheckOtpPhone $request) {
        try{
            $confirmOtp = phoneOtp::where(['id' => $request['id'], 'otp' => $request['otp']])->first();
            
            if (!$confirmOtp) {
                $this->response['message'] = trans('api/user.otp_phone_not_match');
                return response()->json($this->response, 404);
            }

            $confirmOtp->is_verified = 1;
            $confirmOtp->save();
            $filename = 'api_datalogger_' . date('d-m-y') . '.log';
            
            $this->response['message'] = trans('api/user.otp_matched');
            $this->response['status'] = 1;
            return response()->json($this->response, 200);

        }
        catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            //$this->response['message'] = $ex->getMessage();
            return response($this->response, 500);
        }
            
    }

    /**
     * @SWG\Post(
     *     path="/sociallogin",
     *     tags={"Users"},
     *     summary="Social login user",
     *     description="Validate and login user by facebook and google using API's",
     *     operationId="loginSocialUser",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Social login user object",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="email",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="type",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="social_id",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="firstname",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="phone_country_code",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="phone_number",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="social_image",
     *              type="string"
     *             )
     *         )
     *     ),
     *     @SWG\Parameter(
     *            name="device-token",
     *            in="header",
     *            description="Device Token",
     *            type="string"
     *     ),  
     *     @SWG\Parameter(
     *            name="device-id",
     *            in="header",
     *            description="Device Id",
     *            type="string"
     *     ),  
     *     @SWG\Parameter(
     *            name="build-version",
     *            in="header",
     *            description="Build Version",
     *            type="string"
     *     ),  
     *     @SWG\Parameter(
     *            name="platform",
     *            in="header",
     *            description="Platform",
     *            type="string"
     *     ), 
     *     @SWG\Parameter(
     *            name="build",
     *            in="header",
     *            description="Build",
     *            type="string"
     *     ),   
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */
    public function socialLogin(SocialRequest $request){
        try{
            $email = Input::get('email');
            $type = Input::get('type');
            $id = Input::get('social_id');
            $firstname = Input::get('firstname');
            $phone_country_code = Input::get('phone_country_code');
            $phone_number = Input::get('phone_number');
            $image_social = Input::get('social_image');

            //Login and Register by facebook id
            if($type == 'facebook'){
                $user = User::where('fb_id',$id)->first();
                if($user){
                    $token = $user->createToken('Api access token')->accessToken;
                    $user->social_image = $image_social;
                    $user->update();
                    $this->insertDeviceDetails($token, $user->id); 
                    return (new UserResource($user, $token))->additional([
                        'status' => 1,
                        'message' => trans('api/user.loggedin_successfully')
                    ]); 
                }
                else{
                    $user = User::where('email',$email)->first();
                    if($user){
                        $user->fb_id = $id;
                        $user->social_image = $image_social;
                        $user->update();
                        $token = $user->createToken('Api access token')->accessToken;
                        $this->insertDeviceDetails($token, $user->id); 
                        return (new UserResource($user, $token))->additional([
                            'status' => 1,
                            'message' => trans('api/user.loggedin_successfully')
                        ]); 
                    }
                    else{
                        $user = User::create([
                            'email' => $email,
                            'fb_id' => $id,
                            'firstname' => $firstname,
                            'phone_country_code' => $phone_country_code,
                            'phone_number' => $phone_number,
                            'social_image' => $image_social,
                            'status' => User::active
                        ]);

                        $token = $user->createToken('Api access token')->accessToken;
                        $this->insertDeviceDetails($token, $user->id); 
                        return (new UserResource($user, $token))->additional([
                            'status' => 1,
                            'message' => trans('api/user.loggedin_successfully')
                        ]); 
                    }
                }

            }

            //Login and Register by google id
            if($type == 'google'){
                $user = User::where('google_id',$id)->first();
                if($user){
                    $token = $user->createToken('Api access token')->accessToken;
                    $this->insertDeviceDetails($token, $user->id); 
                    $user->social_image = $image_social;
                    $user->update();
                    return (new UserResource($user, $token))->additional([
                        'status' => 1,
                        'message' => trans('api/user.loggedin_successfully')
                    ]); 
                }
                else{
                    $user = User::where('email',$email)->first();
                    if($user){
                        $user->google_id = $id;
                        $user->social_image = $image_social;
                        $user->update();
                        $token = $user->createToken('Api access token')->accessToken;
                        $this->insertDeviceDetails($token, $user->id); 
                        return (new UserResource($user, $token))->additional([
                            'status' => 1,
                            'message' => trans('api/user.loggedin_successfully')
                        ]); 
                    }
                    else{
                        $user = User::create([
                            'email' => $email,
                            'google_id' => $id,
                            'firstname' => $firstname,
                            'phone_country_code' => $phone_country_code,
                            'phone_number' => $phone_number,
                            'social_image' => $image_social,
                            'status' => User::active
                        ]);

                        $token = $user->createToken('Api access token')->accessToken;
                        $this->insertDeviceDetails($token, $user->id); 
                        return (new UserResource($user, $token))->additional([
                            'status' => 1,
                            'message' => trans('api/user.loggedin_successfully')
                        ]); 
                    }
                }
            }
        }
        catch (\Exception $ex) {
            //$this->response['message'] = trans('api/user.something_wrong');
            $this->response['message'] = $ex->getMessage();
            return response($this->response, 500);
        }
    }
    
    /**
     * @SWG\Post(
     *     path="/uploadImage",
     *     tags={"Users"},
     *     summary="Upload Media file for profile avatar",
     *     description="Upload media file for profile avatar",
     *     operationId="uploadProfile",
     *     @SWG\Parameter(
     *         name="profile_image",
     *         in="formData",
     *         type="file",
     *         required = true,
     *         description="Upload file"    
     *     ),
     *     @SWG\Parameter(
     *         name="Authorization",
     *         required = true,
     *         in="header",
     *         description="Authorization Token",
     *         type="string"
     *     ),
     *     @SWG\Response(response=200, description="Successful operation"),
     *     @SWG\Response(response=422, description="Validation Error and  Unprocessable Entity")*      ,
     *     @SWG\Response(response=401, description="Invalid Token"),
     *     @SWG\Response(response=500, description="Internal serve error")
     * )
     */

    public function uploadImage(ProfileRequest $request) {

        $user = Auth::user();
        
        try {
            $image = $request->file('profile_image');
            $extension = $image->getClientOriginalExtension();
            $filename = md5($user->id . time() . $user->id) . '.' . $extension;
            $image->move('images/avatars/', $filename);
            $user->update(['image' => $filename]);
            //$img = $user->image ? url('images/avatars/' . $user->image) : '';
            //$this->response['data']->profile_image = $img;
            $this->response['data']->profile_image  = $user->image;
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.image_uploaded');
            return response($this->response, 200);
            /* return (new UserResource($user))->additional([
              'status' => 1,
              'message' => trans('api/user.image_uploaded')
              ]); */
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function uploadVenderImage(Request $request) {
        $validator = Validator::make($request->all(), [
                    'profile_image' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {
            $image = $request->file('profile_image');
            $extension = $image->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $image->move('images/avatars/', $filename);
            $img = $filename ? url('images/avatars/' . $filename) : '';
            $this->response['data']->profile_image = $img;
            $this->response['data']->image_name = $filename;
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.image_uploaded');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function uploadVenderDoc(Request $request) {
        
        $validator = Validator::make($request->all(), [
                    'vender_doc' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {
            
            $image = $request->file('vender_doc');
            $extension = $image->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $image->move('images/doc/', $filename);
            $doc = $filename ? url('images/doc/' . $filename) : '';
            $this->response['data']->vender_doc = $doc;
            $this->response['data']->doc_name = $filename;
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.doc_uploaded');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    /**
     * Check for user Activation Code
     * @param  array $data
     * @return User
    **/
    public function userActivation(Request $request) {

        $validator = Validator::make($request->all(), [
                    'otp' => 'required|string|max:45',
                    'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {
            $token = $check = Activation::where(['token' => $request['otp'], 'id_user' => $request['user_id']])->first();

            if (!is_null($check)) {
                $user = User::find($check->id_user);
                $user->update(['status' => '1']);
                $user->update(['email' => $token->email]);
                $token->delete();
                $message = "Account activated successfully";
                return (new UserResource($user))->additional([
                            'status' => 1,
                            'message' => $message
                ]);
            }
            $this->response['message'] = "Invalid OTP";
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = "Something went wrong";
            return response($this->response, 500);
        }
    }

    public function resendOtp(Request $request) {


        $validator = Validator::make($request->all(), [
                    'email' => 'required',
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {

            $checkEmailalreadyexist = User::where('email', '=', $request['email'])->first();
            if ($checkEmailalreadyexist) {
                $this->response['message'] = trans('api/user.email_already_exist');
                return response($this->response, 403);
            }

            $user = User::where('email', $request['email'])->first() ? User::where('email', $request['email'])->first() : Auth::User();
            $user['token'] = rand(10000, 99999);
            $check_otp_already_sent = '';
            if ($user->email == $request['email']) {
                $check_otp_already_sent = Activation::where('email', $user->email)->where('id_user', $user->id)->first();
            }
            if ($check_otp_already_sent) {
                $check_otp_already_sent->update(['token' => $user['token']]);
                $check_otp_already_sent->save();
            } else {
                $check_otp_already_sent = Activation::create(['id_user' => $user->id, 'token' => $user['token'], 'email' => $request['email']]);
            }
            Mail::to($check_otp_already_sent->email)->send(new Activate($user));
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.email_otp_sent');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function verifyEmailOtp(Request $request) {


        $validator = Validator::make($request->all(), [
                    'otp' => 'required',
                    'id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {

            $user = Activation::where('token', $request['otp'])->where('id_user', $request['id'])->first();
            if (!$user) {
                $this->response['message'] = trans('api/user.invalid_otp');
                return response($this->response, 200);
            }
            DB::table('users')->where('id', $user->id_user)->update(['email' => $user->email]);
            $user = User::find($user->id_user);
            $token = $user->createToken('Api access token')->accessToken;
            $this->insertDeviceDetails($token, $request['id']);
            return (new UserResource($user, $token))->additional([
                        'status' => 1,
                        'message' => trans('api/user.otp_matched')
            ]);
        } catch (\Exception $ex) {

            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function updateEmail(Request $request) {

        $validator = Validator::make($request->all(), [
                    'email' => ['required', 'email', ValidationRule::unique('users')->ignore(Auth::user()->id)],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        $user = Auth::user();
        try {
            $user = User::where('email', $user->email)->first();
            if (!$user) {
                $this->response['status'] = 1;
                $this->response['message'] = trans('api/user.email_not_exist');
                return response($this->response, 200);
            }

            $user['token'] = rand(10000, 99999);
            $check_otp_already_sent = Activation::where('email', $request['email'])->where('id_user', $user->id)->first();

            if ($check_otp_already_sent) {
                $check_otp_already_sent->update(['token' => $user['token']]);
                $check_otp_already_sent->save();
            } else {
                Activation::create(['id_user' => $user->id, 'token' => $user['token'], 'email' => $request['email']]);
            }
            Mail::to($request['email'])->send(new UpdateEmail($user));
            return (new UserResource($user))->additional([
                        'status' => 1,
                        'message' => trans('api/user.otp_sent_on_email')
            ]);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function getRoles(PushNotification $pushNotification, ScheduleService $scheduleService) {
        $roles = Role::where('level', 4)->get();
        return (new RolesResourceCollection($roles))->additional([
                    'status' => 1,
                    'message' => "Get record succesfully"
        ]);
    }

    public function phoneOtp(Request $request) {

        $validator = Validator::make($request->all(), [
                    // 'country_code' => 'required',    
                    'phone_no' => ['required'],
        ]);
        if ($validator->fails()) {

            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        try {
            $user_otp = phoneOtp::where('phone_no', $request['phone_no'])->first();

            if (isset($user_otp->is_verified) && $user_otp->is_verified != 0) {
                $this->response['message'] = trans('api/user.phone_already_registered');
                return response()->json($this->response, 409);
            }
            $otp = rand(1000, 9999);
            $phone_no = $request['phone_no'];

            $twilio = new Aloha\Twilio\Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_SMS_FROM_NUMBER'));
            if ($user_otp) {
                if ($twilio->message($phone_no, 'Otp is ' . $otp)) {
                    $user_otp->update(['otp' => $otp]);
                    $this->response['message'] = trans('api/user.otp_sent');
                    $this->response['status'] = 1;
                    return response()->json($this->response, 200);
                } else {
                    $this->response['message'] = trans('api/user.otp_not_sent');
                    $this->response['status'] = 1;
                    return response()->json($this->response, 401);
                }
            }
            if ($twilio->message($phone_no, 'Otp is ' . $otp)) {
                $user = phoneOtp::create([
                            //'phone_country_code' => $request['phone_country_code'],
                            'phone_no' => $request['phone_no'],
                            'otp' => $otp,
                ]);
                if (!$user) {
                    $this->response['message'] = trans('api/user.otp_not_sent');
                    return response()->json($this->response, 401);
                }
                $this->response['message'] = trans('api/user.otp_sent');
                $this->response['status'] = 1;
                return response()->json($this->response, 200);
            }
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.not_verified_number');
            return response()->json($this->response, 401);
        }
    }

    public function changeVendorOnlineStatus(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                        'status' => ['required'],
            ]);
            if ($validator->fails()) {

                $this->response['message'] = $validator->errors()->first();
                return response()->json($this->response, 401);
            }
            $user = Auth::user();
            if (!$user) {
                $this->response['message'] = trans('api/user.user_not_exist');
                $this->response['status'] = 0;
                return response()->json($this->response, 404);
            }
            if (!$user->status) {
                $this->response['message'] = trans('api/user.user_is_inactive');
                $this->response['status'] = 0;
                return response()->json($this->response, 404);
            }
            $status = $request['status'];
            $user->update(['online' => $status]);
            $msg = trans('api/user.you_are_offline');
            if ($status == '1') {
                $msg = trans('api/user.you_are_online');
            }
            $this->response['message'] = $msg;

            $this->response['status'] = 1;
            $this->response['data']->status = (int) $status;

            return response()->json($this->response, 200);
            /* return (new UserResource($user))->additional([
              'status' => 1,
              'message' => trans('api/user.online_status')
              ]); */
        } catch (Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function testNotification() {
        $user = Auth::User();
        $notificationMessage = 'this is test msg';
        if (Notification::createNotification(12, 'hiiiii', 'suraj', $notificationMessage, $user->id)) {
            echo 'sent';
        } else {
            echo 'not sent';
        }
    }

}