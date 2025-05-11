<?php

namespace App\Http\Controllers\Seeker;
use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\User;
use App\Models\Jobpost;
use App\Models\Follower;

use Illuminate\Http\Request;

class CompainesController extends Controller
{
    public function showCompaines(){
        $companies = Company::all();
        $userIds = $companies->pluck('user_id')->filter();
        $companiesIds = $companies->pluck('company_id')->filter();

        $users = User::whereIn('user_id', $userIds)
            ->select('user_id', 'profile_img',"about")
            ->get()
            ->keyBy('user_id'); // Store by user_id for quick lookup
        
        $jobs = Jobpost::whereIn('company_id', $companiesIds)->get();
        $followers = Follower::whereIn('company_id', $companiesIds)->get();

        $formattedCompanies = $companies->map(function ($company) use ($users, $jobs, $followers) {
            $companyJobs = $jobs->where('company_id', $company->company_id)->count();
            $companyfollowers = $followers->where('company_id', $company->company_id)->count();
            $user = $users->get($company->user_id);

            return [
                'company_id' => $company->company_id,
                'company_name' => $company->company_name,
                'company_country' => $company->company_country,
                'company_city' => $company->company_city,
                'profile_img' => $user->profile_img,
                'about' =>  $user->about,
                'jobs_count' => $companyJobs,
                'followers_count' => $companyfollowers
            ];
        });

        return response()->json([
            'companies' => $formattedCompanies,
        ]);

        

    }



}
