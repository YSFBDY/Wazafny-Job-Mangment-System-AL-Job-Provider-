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

use App\Mail\EmailVerificationMail;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function Register(RegisterRequest $request,$role) {
        if($role != "Seeker" && $role != "Company") {
            return response()->json(["message" => "Invalid role"], 405);
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json(["message" => "Email already exists"], 400);
        }

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

        $verificationUrl = route('verify.email', ['user_id' => $user->user_id]);
        Mail::to($user->email)->send(new EmailVerificationMail($verificationUrl));
    
        return response()->json([
            "token" => $token,
            "user_id" => $user->user_id,
            "role" => $user->role,
            "role_id" => $role_id
        ], 201);
    }




    public function verify($user_id)
    {
        $user = User::findOrFail($user_id);
        if ($user->verified) {
            return response('Email already verified.');
        }

        $user->verified = true;
        $user->save();

        return response('Email verified successfully. You can now log in.');
    }


    public function check($user_id)
    {
    $user = User::find($user_id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json([
        'verified' => $user->verified
    ]);
    }   

    public function Login(LoginRequest $request) {

        $user = User::where('email', $request->email)->where('verified', true)->first();
        // Check if user exists and the password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([ 
                "message" => "Incorrect Data"
            ], 401); // 401 Unauthorized
        }

        if($user->role != $request->role) {
            return response()->json(["message" => "Incorrect Data"], 401);
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
        $user_id = $request->input('user_id');
    
        if (!$user_id) {
            return response()->json(["message" => "Missing user_id"], 404);
        }
    
        $user = User::find($user_id);
    
        if (!$user) {
            return response()->json(["message" => "User not found"], 404);
        }
    
        $user->tokens()->delete(); // revoke all tokens
        return response()->json(["message" => "Logged out successfully"]);
    }





    public function GenerateOtp(GenerateOtpRequest $request) {

        if (User::where('email', $request->email)->first()) {
            
       
        $otp = rand(100000, 999999);

        // Store OTP in database
        Otp::updateOrCreate(
            ['email' => $request->email], // If OTP already exists, update it
            ['otp' => $otp, 'expires_at' => Carbon::now()->addMinutes(30)]
        );

        Mail::to($request->email)->send(new ResetPassword($otp));

        return response()->json(["message" => "OTP sent to your email."]);
     }

        return response()->json(["message" => "msh mawgoud yasta."]);
        
    }



    public function verifyOtp(VerifyOtpRequest $request) {


        $otpRecord = Otp::where('email', $request->email)
                        ->where('otp', $request->otp)
                        ->where('expires_at', '>', now())
                        ->first();
    
        if (!$otpRecord) {
            return response()->json(["message" => "Invalid or expired OTP"], 400);
        }
    
        $otpRecord->delete();
    
        return response()->json(["message" => "OTP verified successfully"]);
    }




    public function ResetPassword(ResetPassRequest $request) {

        if (User::where('email', $request->email)->doesntExist()) {
            return response()->json(["message" => "Invalid email"], 404);
        }
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);
    
        return response()->json(["message" => "Password updated successfully"]);

    }
    



}
