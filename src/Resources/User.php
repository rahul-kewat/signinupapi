<?php

namespace Devrahul\Signinupapi\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Devrahul\Signinupapi\Models\UserAddresses;
use App\Http\Resources\AddressCollection;
use App\Http\Resources\VenderServiceCollection;
use Illuminate\Support\Facades\Auth;
use App\Models\RideAccepted;
use App\Models\Rides;
use DB;
use App\Models\Transaction;
use App\Setting;

class User extends Resource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private $token;
    private $sug_price_value;
    private $is_vehicle_added;
    private $is_bank_detail_added;
    private $referral_code_text;
    protected $address=array();
    protected $is_pending_payment = array();
   
    public function getPaymentDetail($user){
        // check the ride owner of the ride_id is same as auth user
        $resultQuery = Rides::where('id',$user->ride_id);
        $data = $resultQuery->first();
        $value =$user->ride_id;
        $resultQuery = Rides::with([
            'rideAccepted',
        ])
        ->whereIn('status',[0,1]);
        $data = $resultQuery->get();            
        $noofseats_accepted = 0;
       
        // If the query count is greater then 0 then it will proceed further
        $noofseats_invehicle = $data[0]['max_no_seats'];
        foreach($data[0]['rideAccepted'] as $data1){
            $noofseats_accepted = $noofseats_accepted+ $data1["consume_seats"];
        }
        $availableseats = $noofseats_invehicle - $noofseats_accepted;
        if(count($data) > 0){
                if($availableseats>0)
                {   
                    //set available seats to 1 as only 1 seat is booked by the passenger
                    $availableseats=1; // comment this line when you want to book more then one seats 

                    //Counting the records of referral code of the user which are not used
                    $records = DB::table("referral_record")->select('*')
                                ->whereNotIn('code',function($query){
                                                $query->select('referral_code')->from('transactions');
                                })
                                ->where(function ($query) use($user){
                                    $query->where('user_id_two','=',$user->user_id)
                                            ->orWhere('user_id_one','=',$user->user_id);
                                })
                                ->get();
                    $inviteCodeDiscount=0;
                    if(count($records)>0){
                        $inviteCodeDiscount = Setting::pluck('referral_code_discount')->first();
                    }
                    $payableamount_withoutdiscount = $data[0]['price_per_seat']*$availableseats + $data[0]['total_toll_tax'];    
                    $discountapplied = ($payableamount_withoutdiscount * $inviteCodeDiscount)/100;
                    if(count($records)>0){
                        $referral_Code_To_Use = $records[0]->code;
                    }
                    else{
                        $referral_Code_To_Use = "";
                    }
                    
                    $payableamount_withoutdiscount = number_format(round($payableamount_withoutdiscount,2),2);
                    $discountapplied = number_format(round($discountapplied,2),2);
                    $with_Discount_Payable =number_format(round($payableamount_withoutdiscount - $discountapplied,2),2);                               
                }
        }
        $dataPush = [
            'ride_id' => $user->ride_id,
            'referral_code' => $referral_Code_To_Use ,
            'original_amount' =>$payableamount_withoutdiscount,
            'discount' => $discountapplied,
            'amount' => $with_Discount_Payable,
        ];
        return $dataPush;
    }

    public function __construct($resource,$token,$is_vehicle_added,$is_bank_detail_added,$sug_price_value,$referral_code_text) {
        parent::__construct($resource);
        $this->token = $token;
        $this->sug_price_value = $sug_price_value;
        $this->is_vehicle_added = $is_vehicle_added;
        $this->is_bank_detail_added = $is_bank_detail_added;
        $this->referral_code_text = $referral_code_text;
        $this->address = UserAddresses::where('user_id',Auth::user()->id)->select("id","user_id","latitude","longitude","address_type","name","phone","country","city","pincode","full_address","house_no","landmark")->get();
        $user = RideAccepted::where(['ride_accepted.user_id'=> Auth::user()->id , 'ride_accepted.status' => 0, 'is_accepted_by_driver' => 1 ])->join('ride','ride.id','ride_id')->orderBy('ride_accepted.updated_at')->select('ride_accepted.ride_id','ride_accepted.user_id','ride_accepted.status','ride_accepted.is_accepted_by_driver','ride_accepted.updated_at','ride.id','ride.user_id','ride.status as rideStatus','ride.max_no_seats')->first();
        if($user != null)
            $this->is_pending_payment = $this->getPaymentDetail($user);
        else
        $this->is_pending_payment = "";

    }


    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname ? $this->firstname : '',
            'lastname' => $this->lastname ? $this->lastname : '',
            'date_of_birth' => $this->date_of_birth ? $this->date_of_birth : '',
            'email' => $this->email ? $this->email : '',
            'phone_number' => $this->phone_number,
            'phone_country_code' => $this->phone_country_code ? $this->phone_country_code : '',
            'gender' => $this->gender==0 ? '0' : '1' ,
            'referral_code'=> $this->referral_code ? $this->referral_code : '',
            'sug_price_value' => $this->sug_price_value ? $this->sug_price_value : '0.00',
            'is_notification' => $this->is_notification != null ? $this->is_notification:'',
            'image' => $this->image ? $this->image : '',
            'bio' => $this->bio ? $this->bio : '',
            'token' => $this->token ? $this->token : '',
            'is_vehicle_added' => $this->is_vehicle_added,
            'is_bank_detail_added' => $this->is_bank_detail_added ,
            'referral_code_text' => $this->referral_code_text != null ? $this->referral_code_text : '',
            'address' => $this->address != null ? $this->address : '',
            'payment_pending_ride' => $this->is_pending_payment != null ? $this->is_pending_payment : "",
        ];
    }
}
