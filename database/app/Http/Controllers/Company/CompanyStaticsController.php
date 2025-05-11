<?php

namespace App\Http\Controllers\Company;
use App\Http\Controllers\Controller;

use App\Models\Company;
use App\Models\Jobpost;
use App\Models\Follower;
use App\Models\Application;


use Illuminate\Http\Request;

class CompanyStaticsController extends Controller
{
    public function showStatics($company_id) {
        $jobs = Jobpost::where('company_id', $company_id)->count();
        $jobs = Jobpost::where('company_id', $company_id)->where('job_status', 'Active')->count();

        $followers = Follower::where('company_id', $company_id)->count();

        $jobIds = Jobpost::where('company_id', $company_id)->pluck('job_id');
        $applications = Application::whereIn('job_id', $jobIds)->count();


        return response()->json([
            'jobs_count' => $jobs,
            'active_jobs_count' => $jobs,
            'followers_count' => $followers,
            'applications_count' => $applications
        ]);
    }
}
