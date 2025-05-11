<?php

namespace App\Http\Controllers\Company;
use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\User;
use App\Models\Jobpost;
use App\Models\Follower;

use App\Traits\TimeAgo;


use Illuminate\Http\Request;
use App\Http\Requests\CompanyProfile\UpdatePersonalinfoRequest;
use App\Http\Requests\CompanyProfile\UpdateExtraInfoRequest;
use App\Models\Jobseeker;
use Illuminate\Contracts\Queue\Job;

class CompanyProfileController extends Controller
{
    use TimeAgo;


    public function updatepersonalinfo(UpdatePersonalinfoRequest $request){
        $company = Company::where('company_id', $request->company_id)->first();
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }
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
        if (!$company_id || !Company::where('company_id', $company_id)->exists()) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        $company = Company::where('company_id', $company_id)->select( 'company_id','company_name', 'company_email' , 'company_website_link',"user_id")->first();

        $user = User::where('user_id', $company->user_id)->select('user_id', 'headline')->first();

        if ($company && $user) {
            if (empty($company->company_name) && empty($company->company_email) && empty($company->company_website_link) && empty($user->headline)) {
                return response()->json(status: 204);
            }
        }

        return response()->json([
            'company_id' => $company->company_id,
            'company_name' => $company->company_name,
            'company_email' => $company->company_email,
            'company_website_link' => $company->company_website_link  ,
            'headline' => $user->headline]);
            
    }



    public function updateextrainfo(UpdateExtraInfoRequest $request){
        $company = Company::where('company_id', $request->company_id)->first();
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }
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

        if (!$company_id || !Company::where('company_id', $company_id)->exists()) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        $company = Company::where('company_id', $company_id)->select( 'company_id','company_industry', 'company_size' , 'company_heads', 'company_country', 'company_city', 'company_founded',"user_id")->first();
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        $user = User::where('user_id', $company->user_id)->select('user_id', 'about')->first();

        $companyFieldsEmpty = empty($company->company_industry) &&
                      empty($company->company_size) &&
                      empty($company->company_heads) &&
                      empty($company->company_country) &&
                      empty($company->company_city) &&
                      empty($company->company_founded);

        $userFieldEmpty = !$user || empty($user->about);

        if ($companyFieldsEmpty && $userFieldEmpty) {
            return response()->json(status: 204);
        }
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



    public function showcompanyprofile($company_id , $seeker_id = null){
        $followstatus = null;
        $formattedJobposts = null;
        $company = Company::where('company_id', $company_id)->first();
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        if(!$seeker_id == null ) {
         
            if(!Jobseeker::where('seeker_id', $seeker_id)->first()) {
                return response()->json(['message' => 'Seeker not found'], 404);
            }
                $follow = Follower::where('seeker_id', $seeker_id)->where('company_id', $company_id)->first();

                if ($follow) {
                    $followstatus = true;
                } else {
                    $followstatus = false;
                }
            
                $jobposts = Jobpost::select('job_id', 'job_title','job_type','job_country','job_city', 'job_status', 'company_id', 'created_at')
                ->where('company_id', $company_id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->keyBy('job_id');


                $formattedJobposts = $jobposts->map(function ($jobpost) {
                    return [
                        'job_id' => $jobpost->job_id,
                        'job_title' => $jobpost->job_title,
                        'job_status' => $jobpost->job_status,
                        'job_type' => $jobpost->job_type,
                        'job_country' => $jobpost->job_country,
                        'job_city' => $jobpost->job_city,
                        'time_ago' => $this->getTimeAgo($jobpost->created_at),
                    ];
                })->values();


             
         }


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
            'followers_count' => $followers,
            'followstatus' => $followstatus,
            'jobposts' => $formattedJobposts
        ]);
    }


















}
