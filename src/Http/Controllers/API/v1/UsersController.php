<?php

namespace App\Http\Controllers\API\v1;



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

use Devrahul\Signinupapi\Models\Setting;


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
use Devrahul\Signinupapi\Requests\EditUserProfile;
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
use Response;
use DateTime;

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

    protected function setData($complexObject)
    {
        $json = json_encode($complexObject);
        $encodedString = preg_replace('/null/', '" "' , $json);
        $this->response['data'] = json_decode($encodedString);
        return $this->response['data'];
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
     *              type="integer"
     *             ),
     *             @SWG\Property(
     *              property="phone_number",
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
    
    public function sendOtp(SendOtp $request) {

        try{
            //checking whether the phone no doesn't contain the string
            if(!is_numeric($request['phone_number'])) {
                return response()->json([
                    'message' => "Please enter a valid phone no"
                ]);
            }
            

            //Twilio Integration and OTP Flow starts here
            $otp = rand(1000, 9999);


            // always add + in front of phone no
            $phone = "+". $request['phone_country_code'] . $request['phone_number'];
            
            $twilio = new Aloha\Twilio\Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_SMS_FROM_NUMBER'));
        
            if ($twilio->message($phone, 'Otp is ' . $otp)) {
                $user_otp = phoneOtp::where('phone_no', $request['phone_number'])->first();
                
                if ($user_otp) {
                    $user_otp->update(['otp' => $otp,'no_of_attempts'=> 0,'is_verified'=>0]);
                    $id = $user_otp->id;

                } else {
                    $phoneOtp = phoneOtp::create(['phone_no' => $request['phone_number'], 'otp' => $otp,'phone_country_code' =>  $request['phone_country_code']]);                  $id = $phoneOtp->id;  
                }

                return response()->json([
                    'message' => "Otp successfully sent to phone number.",
                    'data' => [
                        'id' => (int) $id
                    ]
                ]);
            }
            
            return response()->json([
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
     *              property="lastname",
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
     *              property="refferal_code",
     *              type="string"
     *             ),  
     *             @SWG\Property(
     *              property="phone_country_code",
     *              type="integer"
     *             ),   
     *             @SWG\Property(
     *              property="phone_number",
     *              type="integer"
     *             ),
     *             @SWG\Property(
     *              property="otp",
     *              type="integer"
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
            
            $validPhone_ccode=phoneOtp::whereRaw('phone_no = ? and phone_country_code = ?',[ $request['phone_number'], $request['phone_country_code']])->first();
            if(!$validPhone_ccode)
            {
                return response()->json([
                        'status' => 0,
                        'message' => "Please provide valid Combination of Phone No and Country Code"
                ],422);
            }
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

            $user->attachRole(1);
            
            $token = $user->createToken('Api access token')->accessToken;
            $user->is_verified=1;
            $user->save();
            $this->insertDeviceDetails($token, $user->id);
            
            return (new UserResource($user, $token,"","",""))->additional([
                        'status' => 1,
                        'message' => trans('registered_successfully')
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
     *              @SWG\Property(
     *              property="phone_country_code",
     *              type="integer"
     *             ),
     *             @SWG\Property(
     *              property="phone_number",
     *              type="integer"
     *             ),
     *             @SWG\Property(
     *              property="password",
     *              type="string"
     *             ),
     *          
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
            // if email is not set then it will go inside the if case
            if(!isset($request['email']))
            {
                        //checking whether the phone no doesn't contain the string
                    if(!is_numeric($request['phone_number'])) {
                        return response()->json([
                            'status' => 0,
                            'message' => "Please enter a valid phone no"
                        ]);
                    }

                    // validating both phone country code and phone no
                    $validPhone_ccode=User::whereRaw('phone_number = ? and phone_country_code = ?',[ $request['phone_number'], $request['phone_country_code']])->first();
                    if(!$validPhone_ccode)
                    {
                        return response()->json([
                                'status' => 0,
                                'message' => "User Not Found!! "
                        ],422);
                    }
            }
            
            // $sug_price_value->sug_price_value will fetch price value from settings table so, that
            // when ever admin changes base price value for suggested price per km then it can be used to fetch data
            $sug_price_value = Setting::select('sug_price_value')->latest()->first();
            $isValidatePhone = User::where('phone_number',$request['email'])->first(); 
            
            $email = ($isValidatePhone) ? $isValidatePhone->email : $request['email'];
            
            if (Auth::attempt(['phone_number' => $request['phone_number'], 'password' => $request['password']],true)||Auth::attempt(['email' => $email, 'password' => $request['password']],true)) {
                
                $user = Auth::user();
                //Password matched successfully
                
                
                $token = Auth::user()->createToken('Api access token')->accessToken;
                
                $this->insertDeviceDetails($token, $user->id);
                
                if ($user->status == User::inActive) {

                    return (new UserResource($user, $token,"","", $sug_price_value->sug_price_value))->additional([
                                'status' => 0,
                                'message' => trans('Activate Account First')
                    ]);
                }

                $is_vehicle_added =  DB::table('ride_vehicle')
                                    ->where('user_id',Auth::user()->id)
                                    ->get()
                                    ->count();

                // vehicle details and bank details fetch
                if($is_vehicle_added>0) {$is_vehicle_added=1;} else{ $is_vehicle_added=0;}
                $is_bank_detail_added = DB::table('user_bank_details')
                                        ->where('user_id',Auth::user()->id)
                                        ->get()
                                        ->count();
                if($is_bank_detail_added>0) {$is_bank_detail_added=1;} else {$is_bank_detail_added=0;}
                return (new UserResource($user, $token,$is_vehicle_added,$is_bank_detail_added, $sug_price_value->sug_price_value))->additional([
                            'status' => 1,
                            'message' => trans('User Logged In Successfully')
                ]);

            } else {

                /// Password didn't match
                
                $this->response['message'] = trans('password_incorrect');
                return response($this->response, 422);
            }
        } catch (\Exception $ex) {
            
            $this->response['message'] = $ex->getMessage();
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
     *              @SWG\Property(
     *              property="confirm_password",
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
                $this->response['message'] = trans('User No Found');
                return response($this->response, 422);
            }

            
            $old_password = $request['old_password'];
            if ($user) {
                if (!Hash::check($old_password, $user->password)) {
                    $this->response['message'] = trans('Old Password Not Found');
                    return response($this->response, 422);
                }
            }
            $user->update(['password' => $request['password']]);
            $this->response['message'] = trans('Password Updated');
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('Something wrong');
            $this->response['message'] = $ex->getMessage();
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Get(
     *     path="/user",
     *     tags={"Users"},
     *     summary="Get login user detail",
     *     description="Get user detail using API's",
     *     operationId="getUserDetails",
     *     @SWG\Parameter(
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
            // $sug_price_value->sug_price_value will fetch price value from settings table so, that
            // when ever admin changes base price value for suggested price per km then it can be used to fetch data
            $sug_price_value = Setting::select('sug_price_value')->latest()->first();
            
            $is_vehicle_added =  DB::table('ride_vehicle')
                                    ->where('user_id',Auth::user()->id)
                                    ->get()
                                    ->count();

            if($is_vehicle_added>0) {$is_vehicle_added=1;} else{ $is_vehicle_added=0;}
            

            $is_bank_detail_added = DB::table('user_bank_details')
                                    ->where('user_id',Auth::user()->id)
                                    ->get()
                                    ->count();
            if($is_bank_detail_added>0) {$is_bank_detail_added=1;} else {$is_bank_detail_added=0;}

            $datareturned = (new UserProfile(Auth::User(), $is_vehicle_added, $is_bank_detail_added ,$sug_price_value->sug_price_value))->additional([
                'status' => 1,
                // 'is_vehicle_added'=> $is_vehicle_added,
                // 'is_bank_detail_added'  => $is_bank_detail_added,
                'message' => trans('User Details Found')
            ]);
            // $datareturned = json_encode($datareturned);
            // $datareturned['is_vehicle_added']= $is_vehicle_added;
            // $datareturned = json_decode($datareturned);

            return $datareturned;
        } catch (\Exception $ex) {
            $this->response['message'] =$ex->getMessage();
            return response($this->response, 500);
        }

    }

    /**
     * @SWG\Get(
     *     path="/getUserDetailsByID",
     *     tags={"Users"},
     *     summary="Get user details by id",
     *     description="Get user detail by id using API's",
     *     operationId="getUserDetailsById",
     *      @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Get User Details by ID",
     *         @SWG\Schema(
     *             type="object",
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
    public function getUserDetails(Request $request){

        try {
            if($request->id == null){
                $this->response['message'] ="Please pass the id to fetch data";
                return response($this->response, 200);
            }
            
            // $user = User::where('id',$request->id)
            //                 ->whereRaw(' inner join vehic')
            //                 ->get()->first();
            
            $user = DB::table("users")
                        ->select("users.id","firstname","lastname","bio","date_of_birth","image","social_image","review.rating","ride_vehicle.car_manufacture","ride_vehicle.car_model","ride_vehicle.car_type","ride_vehicle.car_color","user_addresses.city","user_addresses.full_address","user_addresses.country","ride_vehicle.register_year")
                        ->leftjoin('review','users.id','=','review.user_id')
                        ->leftjoin('ride_vehicle','users.id','=','ride_vehicle.user_id')
                        ->leftjoin('user_addresses','users.id','=','user_addresses.user_id')
                        
                        ->where('users.id',$request->id)
                        ->orderBy('ride_vehicle.created_at','desc')
                        // ->tosql();
                        ->get()->first();
                    
            
            // $user = DB::table("users")
            //             ->select("user_amenities.amenities_name")
            //             ->leftjoin('user_amenities','users.id','=','user_amenities.user_id')
            //             ->join('amenities','users.id','=','user_amenities.user_id')
            //             ->where('users.id',$request->id);

            //calculating date of birth
            if($user== null){
                $this->response['message'] = "No User Found with this ID";
                return response($this->response, 200);
            }
            if($user->date_of_birth != null){
                $d1 = new DateTime(now());
                $d2 = new DateTime($user->date_of_birth);
                $age = $d2->diff($d1);
            }
            
            
            
            if($user!=null)
            {
                //calculating the rides offered and rides taken
                $noofridesoffered = \App\Models\Rides::where('user_id', $user->id)->count();
                $noofridestaken = \App\Models\RideAccepted::where('user_id', $user->id)->count();

                $data = [
                    'message' => 'User Details Found',
                    'data' => [
                        'No_of_rides_offered' => $noofridesoffered,
                        'No_of_rides_taken' => $noofridestaken ,
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'bio' => $user->bio != null ? $user->bio : "",
                        'social_image' => $user->social_image != null ? $user->social_image : "",
                        'image' => $user->image != null ? $user->image : "",
                        'age' => $user->date_of_birth != null ? $age->y : "",
                        'rating' => $user->rating != null ? $user->rating : "0",
                        'car_manufacture' =>  $user->car_manufacture != null ? $user->car_manufacture : "",
                        'car_model' => $user->car_model != null ? $user->car_model : "",
                        'car_type' => $user->car_type != null ? $user->car_type : "",
                        'car_color' => $user->car_color != null ? $user->car_color : "",
                        'register_year' => $user->register_year != null ? $user->register_year : "",
                        'city' => $user->city != null ? $user->city : "",
                        'full_address' => $user->full_address != null ? $user->full_address : "",
                        'country' => $user->country != null ? $user->country : "",
                        
                    ]
                ];
                
                // $this->response->message = "User details";
                // $this->response->data->firstname = $user->first_name;
                // $this->response->data->lastname = $user->last_name;
                // $this->response->data->bio = $user->bio;
                // $this->response->data->date_of_birth = $user->date_of_birth;
                $this->setData([$data]);
                return response()->json($data);
                return response($this->response, 200);
                
            }
            
            $this->response['message'] = "No User Found with this ID";
            return response($this->response, 200);

        } catch (\Exception $ex) {
            $this->response['message'] = $ex->getMessage();
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
            $this->response['message'] = trans('Logged Out Successfully');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('something_wrong');
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
     *     
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Forget Password object",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *              property="phone_no",
     *              type="string"
     *             ),
     *              @SWG\Property(
     *              property="phone_country_code",
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

    public function forgetPassword(ForgetPasswordReq $request) {
        
        try {
            //checking whether the phone no doesn't contain the string
            if(!is_numeric($request['phone_no'])) {
                return response()->json([
                    'status' => 0,
                    'message' => "Please enter a valid phone no"
                ]);
              }
           
            $user = User::where('phone_number', $request['phone_no'])->where('phone_country_code', $request['phone_country_code'])->first();
            if(!isset($user))
            {
                return $this->response['message'] = "No such combination of phone no and phone country code";
            }
            $user['password_otp'] = rand(1000, 9999);
            $user->is_verified = 0;
            $user->save();

            $user->update(['password_otp' => $user['password_otp']]);
            // add + in front of phone no
            $phone_no = "+". $user->phone_country_code.$request['phone_no'];
            $twilio = new Aloha\Twilio\Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_SMS_FROM_NUMBER'));
            if ($user['password_otp']) {
                if ($twilio->message($phone_no, 'Otp is ' . $user['password_otp'])) {
                    $this->response['message'] = trans('OTP Sent Successfully');
                    $this->response['data'] = (object) array('id' => $user->id);
                    $this->response['status'] = 1;
                    //saving data to phoneOtp
                    $otp =$user['password_otp'];
                    $user_otp = phoneOtp::where('phone_no', $request['phone_no'])->first();  
                    if ($user_otp) {
                        $user_otp->update(['otp' => $otp]);
                        $id = $user_otp->id;
                    } else {
                        $phoneOtp = phoneOtp::create(['phone_no' => $request['phone_no'], 'otp' => $otp,'phone_country_code' =>  $request['phone_country_code']]);                  $id = $phoneOtp->id;  
                    }
                    
                    
                    return response($this->response, 200);
                   
                }
            }
        } catch (\Exception $ex) {
            $this->response['message'] = $ex->getMessage();
            //$this->response['message'] = trans('something_wrong');
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
        try {
            
            $user = User::where('password_otp', $request['otp'])->where('id', $request['id'])->first();
            
            if (!$user) {
                $this->response['message'] = trans('Invalid OTP');
                return response($this->response, 422);
            }

            $this->response['message'] = trans('OTP Matched Successfully');
            $this->response['data'] = (object) array('id' => $user->id);
            $this->response['status'] = 1;
            return response($this->response, 200);
            
        } catch (\Exception $ex) {

            $this->response['message'] = trans('something_wrong');
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
     *              property="first_name",
     *              type="string"
     *             ),
     * *           @SWG\Property(
     *              property="last_name",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="phone_country_code",
     *              type="integer"
     *             ),
     *             @SWG\Property(
     *              property="phone_number",
     *              type="integer"
     *             ),
     *             @SWG\Property(
     *              property="gender",
     *              type="string"
     *             ),
     *             @SWG\Property(
     *              property="otp",
     *              type="string"
     *             ),
     *              @SWG\Property(
     *              property="date_of_birth",
     *              type="string"
     *             ),
     *              @SWG\Property(
     *              property="bio",
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
            $user->firstname = Input::get('first_name');
            $user->lastname = Input::get('last_name');
            $user->date_of_birth = Input::get('date_of_birth');
            $user->bio = Input::get('bio');
            $user->gender = Input::get('gender'); 
            $user->is_verified=1;
            $user->no_of_attempts =0;
            if(Input::get('otp')){
                $phonenocheck = User::where(["phone_number" => Input::get('phone_number')],["phone_country_code" => Input::get('phone_country_code')])->first();
                if($user->id != $phonenocheck->id)
                {
                    //means phone no is already present with the another user
                    $this->response['message'] = trans('Phone No is already in use.');
                    return response($this->response, 500);

                }
                $user->phone_country_code = Input::get('phone_country_code');
                $user->phone_number = Input::get('phone_number');
            }
            
            $user->save();
            return (new UserResource($user,"","","",""))->additional([
                        'status' => 1,
                        'message' => trans('User Details updated successfully')
            ]);

        }
        catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            //$this->response['message'] = $ex->getMessage();
            return response($this->response, 500);
        }
    }
    
    ///////////////////////////////////////////////////////////////////////
    //Edit User Profile API edited by rahul
    //////////////////////////////////////////////////////////////////////

    // public function editUser(EditUserProfile $request) {
    //     try{
    //         $user = Auth::user();
    //         $userdata=phoneOtp::where(['phone_no'=> $user->phone_number, 'phone_country_code'=> $user->phone_country_code])->first();
    //         if($userdata==null)
    //         {
    //             $this->response['message'] = trans('User Not Found');
    //             return response($this->response, 422);
    //         }
    //         //----
    //         // check if previous phone no is equal to the new phone no or not
    //         //----
    //         if($user->phone_number != $request['phone_number']){
                
    //             // here the phone no is updated by the user
    //             $newphoneotp = phoneOtp::where(['phone_no'=>$request['phone_number'],'phone_country_code'=>$request['phone_country_code']])->first();
    //             if($newphoneotp == null)
    //             {
    //                 // means till now the otp is not sent to the new phone no 
    //                 $this->response['message'] = trans('Please Send OTP to verify new Phone No');
    //                 return response($this->response, 422);
    //             }
    //             else{
    //                 //if already sent then check whether if this otp is already verified or not
    //                 if($newphoneotp->is_verified == 1)
    //                 {
    //                     $this->response['message'] = trans('Please Resend OTP, Already Used');
    //                     return response($this->response, 422);
    //                 }
    //                 else{
    //                     //if this otp is not already verified then update the data
    //                     if($request['otp'] == $newphoneotp->otp){
    //                         if($newphoneotp->is_verified ==0)
    //                         {
    //                             $user->firstname = Input::get('first_name');
    //                             $user->lastname = Input::get('last_name');
    //                             $user->date_of_birth = Input::get('date_of_birth');
    //                             $user->bio = Input::get('bio');
    //                             $user->phone_country_code = Input::get('phone_country_code');
    //                             $user->phone_number = Input::get('phone_number');   
    //                             $user->gender = Input::get('gender'); 
    //                             $user->is_verified=1;
    //                             $user->no_of_attempts =0;
    //                             $user->save();
    //                             $newphoneotp->is_verified = 1;
    //                             $newphoneotp->no_of_attempts = 0;
    //                             $newphoneotp->save();
    //                             $this->setData($user);
    //                         }
    //                         else{
    //                             $this->response['message'] = trans('OTP Already Verified. Please send again an OTP request.');
    //                             return response($this->response, 422);
    //                         }
    //                     }
    //                     else{
    //                         if($newphoneotp->no_of_attempts>=3)
    //                         {
    //                             $this->response['message'] = trans('No of attempts exceeded. Try after sending new OTP');
    //                             return response($this->response, 422);
    //                         }
    //                         $newphoneotp->no_of_attempts = $newphoneotp->no_of_attempts +1;
    //                         $newphoneotp->save();
    //                         $this->response['message'] = trans('OTP Not Matched');
    //                         return response($this->response, 422);
    //                     }
    //                 }
                    
    //             }
    //         }
    //         else
    //         {
    //              //if new no is same as old no then update the details
    //              $user->firstname = Input::get('first_name');
    //              $user->lastname = Input::get('last_name');
    //              $user->date_of_birth = Input::get('date_of_birth');
    //              $user->bio = Input::get('bio');
    //              $user->gender = Input::get('gender'); 
    //              $user->is_verified=1;
    //              $user->password_otp=0;
    //              $user->no_of_attempts =0;
    //              $user->save();
    //              $this->setData($user);
    //         }

    //         return (new UserResource($user))->additional([
    //                     'status' => 1,
    //                     'message' => trans('User Updated Successfully')
    //         ]);

    //     }
    //     catch (\Exception $ex) {
    //         $this->response['message'] = trans('something_wrong');
    //         $this->response['message'] = $ex->getMessage();
    //         return response($this->response, 500);
    //     }
    // }

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
     *              property="otp",
     *              type="string"
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
    public function updateForgetPassword(Request $request) {

        try {
            //checking phone otp
            if(Input::get('user_id')==null)
            {
                $this->response['message'] = trans('User ID Field is Required');
                $this->response['status'] = 1;
                return response()->json($this->response, 422);
            }
            if(Input::get('otp')==null)
            {
                $this->response['message'] = trans('OTP Field is Required');
                $this->response['status'] = 1;
                return response()->json($this->response, 422);
            }
            if(Input::get('password')==null)
            {
                $this->response['message'] = trans('Password Field is required');
                $this->response['status'] = 1;
                return response()->json($this->response, 422);
            }

            $phoneOtpData = User::where([['id','=',$request['user_id']],['password_otp','=',$request['otp']]])->first();
            if($phoneOtpData == null)
            {
                $this->response['message'] = trans('Please enter a user id or OTP or try sending again a new OTP');
                $this->response['status'] = 1;
                return response()->json($this->response, 422);
            }
            if($phoneOtpData->is_verified == 0)
            {
                if($phoneOtpData->password_otp != 0)
                    { // if no is not verified then enter the condition
                        if($phoneOtpData->no_of_attempts < 3)
                        {// if no of attempts is less then the 3 
                            $phoneOtpData->no_of_attempts = $phoneOtpData->no_of_attempts + 1;
                            $phoneOtpData->save();
                        }
                        else{
                            $phoneOtpData->password_otp = 0;
                            $phoneOtpData->no_of_attempts=0;
                            $phoneOtpData->save();
                            $this->response['message'] = "No of attempts exceeded";
                            $this->response['status'] = 1;
                            return response()->json($this->response, 422);
                        }
                    }
                    else{
                        $this->response['message'] = "Please send OTP first";
                        $this->response['status'] = 1;
                        return response()->json($this->response, 422);
                    }
            
            }
            else
            {
                $this->response['message'] = "This OTP is already Verified";
                $this->response['status'] = 1;
                return response()->json($this->response, 422);
            }

            // updating data
            $user = User::where(['id' => $request['user_id'] ,'password_otp' => $request['otp']])->first();
            $user->password=$request['password'];
            $user->password_otp = 0;
            $user->no_of_attempts =0;
            $user->is_verified =1;
            $user->save();
            $this->response['message'] = trans('Password Updated Successfully');
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('something_wrong');
            return response($this->response, 500);
        }
    }

    /**
     * @SWG\Post(
     *     path="/editcheckPhoneOtp",
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
     *              type="integer"
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
                $this->response['message'] = trans('OTP Phone Not Matched');
                return response()->json($this->response, 404);
            }

            $confirmOtp->is_verified = 1;
            $confirmOtp->save();
            $filename = 'api_datalogger_' . date('d-m-y') . '.log';
            
            $this->response['message'] = trans('OTP Matched Successfully');
            $this->response['status'] = 1;
            return response()->json($this->response, 200);

        }
        catch (\Exception $ex) {
            $this->response['message'] = trans('something_wrong');
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
     *              type="integer"
     *             ),
     *             @SWG\Property(
     *              property="phone_number",
     *              type="integer"
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
                    return (new UserResource($user, $token,"","",""))->additional([
                        'status' => 1,
                        'message' => trans('Logged In Successfully')
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
                        return (new UserResource($user, $token,"","",""))->additional([
                            'status' => 1,
                            'message' => trans('Logged In Successfully')
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
                        return (new UserResource($user, $token,"","",""))->additional([
                            'status' => 1,
                            'message' => trans('Logged In Successfully')
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
                    return (new UserResource($user, $token,"","",""))->additional([
                        'status' => 1,
                        'message' => trans('Logged In Successfully')
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
                        return (new UserResource($user, $token,"","",""))->additional([
                            'status' => 1,
                            'message' => trans('Logged In Successfully')
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
                        return (new UserResource($user, $token,"","",""))->additional([
                            'status' => 1,
                            'message' => trans('Logged In Successfully')
                        ]); 
                    }
                }
            }
        }
        catch (\Exception $ex) {
            //$this->response['message'] = trans('something_wrong');
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
            $this->response['message'] = trans('Image Uploaded Successfully');
            return response($this->response, 200);
            /* return (new UserResource($user,"","","",""))->additional([
              'status' => 1,
              'message' => trans('image_uploaded')
              ]); */
        } catch (\Exception $ex) {
            $this->response['message'] = trans('something_wrong');
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
            $this->response['message'] = trans('Image Uploaded Successfully');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('something_wrong');
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
            $this->response['message'] = trans('Doc Uploaded Successfully');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('something_wrong');
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
                return (new UserResource($user,"","","",""))->additional([
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
                $this->response['message'] = trans('Email Already Exist');
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
            $this->response['message'] = trans('Email Otp Sent');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('something_wrong');
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
                $this->response['message'] = trans('Invalid OTP');
                return response($this->response, 200);
            }
            DB::table('users')->where('id', $user->id_user)->update(['email' => $user->email]);
            $user = User::find($user->id_user);
            $token = $user->createToken('Api access token')->accessToken;
            $this->insertDeviceDetails($token, $request['id']);
            return (new UserResource($user, $token,"","",""))->additional([
                        'status' => 1,
                        'message' => trans('OTP Matched Successfully')
            ]);
        } catch (\Exception $ex) {

            $this->response['message'] = trans('something_wrong');
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
                $this->response['message'] = trans('E-Mail does not exists');
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
            return (new UserResource($user,"","","",""))->additional([
                        'status' => 1,
                        'message' => trans('OTP sent to the E-Mail')
            ]);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('something_wrong');
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
                $this->response['message'] = trans('Phone Already Registered');
                return response()->json($this->response, 409);
            }
            $otp = rand(1000, 9999);
            $phone_no = $request['phone_no'];

            $twilio = new Aloha\Twilio\Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_SMS_FROM_NUMBER'));
            if ($user_otp) {
                if ($twilio->message($phone_no, 'Otp is ' . $otp)) {
                    $user_otp->update(['otp' => $otp]);
                    $this->response['message'] = trans('otp_sent');
                    $this->response['status'] = 1;
                    return response()->json($this->response, 200);
                } else {
                    $this->response['message'] = trans('otp_not_sent');
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
                    $this->response['message'] = trans('otp_not_sent');
                    return response()->json($this->response, 401);
                }
                $this->response['message'] = trans('otp_sent');
                $this->response['status'] = 1;
                return response()->json($this->response, 200);
            }
        } catch (\Exception $ex) {
            $this->response['message'] = trans('not_verified_number');
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
                $this->response['message'] = trans('user_not_exist');
                $this->response['status'] = 0;
                return response()->json($this->response, 404);
            }
            if (!$user->status) {
                $this->response['message'] = trans('user_is_inactive');
                $this->response['status'] = 0;
                return response()->json($this->response, 404);
            }
            $status = $request['status'];
            $user->update(['online' => $status]);
            $msg = trans('you_are_offline');
            if ($status == '1') {
                $msg = trans('you_are_online');
            }
            $this->response['message'] = $msg;

            $this->response['status'] = 1;
            $this->response['data']->status = (int) $status;

            return response()->json($this->response, 200);
            /* return (new UserResource($user))->additional([
              'status' => 1,
              'message' => trans('online_status')
              ]); */
        } catch (Exception $ex) {
            $this->response['message'] = trans('something_wrong');
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