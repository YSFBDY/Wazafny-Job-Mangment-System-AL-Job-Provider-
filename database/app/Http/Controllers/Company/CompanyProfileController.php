<?php

namespace App\Http\Controllers\Company;
use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\User;
use App\Models\Jobpost;
use App\Models\Follower;

use Illuminate\Http\Request;
use App\Http\Requests\CompanyProfile\UpdatePersonalinfoRequest;
use App\Http\Requests\CompanyProfile\UpdateExtraInfoRequest;


class CompanyProfileController extends Controller
{

    public function updatepersonalinfo(UpdatePersonalinfoRequest $request){
        $company = Company::where('company_id', $request->company_id)->first();
        $user = User::where('user_id', $company->user_id)->first();
        $user->update(['headline' => $request->headline]);
        $company->update([
            'company_name' => $request->company_name,
            'company_email' => $request->company_email,
            'company_website_link' => $request->company_website_link
        ]);

        return response()->json(['message' => 'Profile Info updated successfully']);

    }

    

    public function showpersonalinfo($company_id) {
        $company = Company::where('company_id', $company_id)->select( 'company_id','company_name', 'company_email' , 'company_website_link',"user_id")->first();
        $user = User::where('user_id', $company->user_id)->select('user_id', 'headline')->first();
        return response()->json([
            'company_id' => $company->company_id,
            'company_name' => $company->company_name,
            'company_email' => $company->company_email,
            'company_website_link' => $company->company_website_link  ,
            'headline' => $user->headline]);
    }



    public function updateextrainfo(UpdateExtraInfoRequest $request){
        $company = Company::where('company_id', $request->company_id)->first();
        $user = User::where('user_id', $company->user_id)->first();
        $user->update(['about' => $request->about]);
        $company->update([
            'company_industry' => $request->company_industry,
            'company_size' => $request->company_size,
            'company_heads' => $request->company_heads,
            'company_country' => $request->company_country,
            'company_city' => $request->company_city,
            'company_founded' => $request->company_founded

        ]);

        return response()->json(['message' => 'Profile Extra Info updated successfully']);
    }


    public function showextrainfo($company_id){
        $company = Company::where('company_id', $company_id)->select( 'company_id','company_industry', 'company_size' , 'company_heads', 'company_country', 'company_city', 'company_founded',"user_id")->first();
        $user = User::where('user_id', $company->user_id)->select('user_id', 'about')->first();
        return response()->json([
            'company_id' => $company->company_id,
            'company_industry' => $company->company_industry,
            'company_size' => $company->company_size,
            'company_heads' => $company->company_heads,
            'company_country' => $company->company_country,
            'company_city' => $company->company_city,
            'company_founded' => $company->company_founded,
             'about' => $user->about]);
    }



    public function showcompanyprofile($company_id){
        $company = Company::where('company_id', $company_id)->first();
        $user = User::where('user_id', $company->user_id)->select('user_id', 'about', 'headline', 'profile_img', 'cover_img')->first();
        $jobs = Jobpost::where('company_id', $company->company_id)->count();
        $followers = Follower::where('company_id', $company->company_id)->count();
        return response()->json([
            'company_id' => $company->company_id,
            'company_name' => $company->company_name,
            'company_email' => $company->company_email,
            'company_website_link' => $company->company_website_link,
            'company_industry' => $company->company_industry,
            'company_size' => $company->company_size,
            'company_heads' => $company->company_heads,
            'company_country' => $company->company_country,
            'company_city' => $company->company_city,
            'company_founded' => $company->company_founded,
            'about' => $user->about,
            'headline' => $user->headline,
            'profile_img' => $user->profile_img,
            'cover_img' => $user->cover_img,
            'jobs_count' => $jobs,
            'followers_count' => $followers
        ]);
    }


















}
