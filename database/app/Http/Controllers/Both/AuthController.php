<?php

namespace App\Http\Controllers\Both;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Jobseeker;
use App\Models\Company;
use App\Models\Otp;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPassRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\GenerateOtpRequest;
use App\Mail\ResetPassword;   

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;


use Illuminate\Http\Request;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function Register(RegisterRequest $request,$role) {

        switch ($role) {
            case "Seeker":
                $user = User::create([
                    "email" => $request->email,
                    "password" => bcrypt($request->password),
                    "role" => "Seeker"
                ]);
    
                $seeker = Jobseeker::create([
                    "user_id" => $user->user_id,
                    "first_name" => $request->first_name,
                    "last_name" => $request->last_name
                ]);
    
                $role_id = $seeker->seeker_id;
                break;
    
            case "Company":
                $user = User::create([
                    "email" => $request->email,
                    "password" => bcrypt($request->password),
                    "role" => "Company"
                ]);
    
                $company = Company::create([
                    "user_id" => $user->user_id,
                    "company_name" => $request->company_name
                ]);
    
                $role_id = $company->company_id;
                break;
    
            default:
                throw new \Exception("Invalid role");
        }
    
        // Create a new token for the user
        $token = $user->createToken($user->email)->plainTextToken;
    
        return response()->json([
            "token" => $token,
            "user_id" => $user->user_id,
            "role" => $user->role,
            "role_id" => $role_id
        ], 201);
    }





    public function Login(LoginRequest $request) {

        $user = User::where("email", $request->email)->first();

        // Check if user exists and the password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([ 
                "message" => "Incorrect data"
            ]); // 401 Unauthorized
        }

        $role_id = null;
        if ($user->role === "Seeker") {
            $role_id = Jobseeker::where("user_id", $user->user_id)->value("seeker_id");
        } elseif ($user->role === "Company") {
            $role_id = Company::where("user_id", $user->user_id)->value("company_id");
        }
    
        // Create a new token
        $token = $user->createToken($user->email)->plainTextToken;
    
        return response()->json([
            "token" => $token,
            "user_id" => $user->user_id,
            "role" => $user->role,
            "role_id" => $role_id
        ]);
        
    }





    public function Logout(Request $request) {
        $request->user()->tokens()->delete();
        return [
            "message" => "logged out successfully",
    
        ];
    }





    public function GenerateOtp(GenerateOtpRequest $request) {
       
        $otp = rand(100000, 999999);

        // Store OTP in database
        Otp::updateOrCreate(
            ['email' => $request->email], // If OTP already exists, update it
            ['otp' => $otp, 'expires_at' => Carbon::now()->addMinutes(30)]
        );

        Mail::to($request->email)->send(new ResetPassword($otp));

        return response()->json(["message" => "OTP sent to your email."]);
       
        
    }



    public function verifyOtp(VerifyOtpRequest $request) {

        $otpRecord = Otp::where('email', $request->email)
                        ->where('otp', $request->otp)
                        ->where('expires_at', '>', now())
                        ->first();
    
        if (!$otpRecord) {
            return response()->json(["message" => "Invalid or expired OTP"], 400);
        }
    
        // OTP is valid, delete it after use
        $otpRecord->delete();
    
        return response()->json(["message" => "OTP verified successfully"]);
    }




    public function ResetPassword(ResetPassRequest $request) {

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);
    
        return response()->json(["message" => "Password updated successfully"]);

    }
    



}
