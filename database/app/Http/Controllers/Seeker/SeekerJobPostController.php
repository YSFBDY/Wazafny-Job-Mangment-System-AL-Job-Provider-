<?php

namespace App\Http\Controllers\Seeker;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\Jobpost;
use App\Models\Company;
use App\Models\User;
use App\Models\JobpostSkill;
use App\Models\Skill;
use App\Models\SeekerSkill;
use App\Models\JobSection;
use App\Models\Jobseeker;
use App\Traits\TimeAgo;

class SeekerJobPostController extends Controller
{
    use TimeAgo;
    
    public function showjobpost($job_id) {
        $jobpost = Jobpost::select('job_id', 'job_title', 'job_about', 'job_time', 'job_type', 'job_country', 'job_city', 'company_id', 'created_at')
            ->where('job_id', $job_id)
            ->firstOrFail();
    
        $company = Company::select('company_name', 'user_id')
            ->where('company_id', $jobpost->company_id)
            ->first();

        $user = User::where('user_id', $company->user_id)->first(['profile_img']);

        $skills = JobpostSkill::where('job_id', $jobpost->job_id)
            ->join('skills', 'jobpostskills.skill_id', '=', 'skills.skill_id')
            ->select('skills.skill_id', 'skills.skill')
            ->get();
    
        $sections = JobSection::where('job_id', $jobpost->job_id)
            ->select('section_id', 'section_name', 'section_description')
            ->get();
    
        return response()->json([
            'profile_img' => $user->profile_img, // Use accessor
            'jobpost' => $jobpost,
            'company' => $company,
            'skills' => $skills,
            'sections' => $sections,
            'time_ago' => $this->getTimeAgo($jobpost->created_at),
        ]);
    }




    public function showjobpostsprofile($company_id) {
        $jobposts = Jobpost::select('job_id', 'job_title','job_type','job_country','job_city', 'job_status', 'company_id', 'created_at')
        ->where('company_id', $company_id)
        ->orderBy('created_at', 'desc')
        ->get()
        ->keyBy('job_id');

        $companyIds = $jobposts->pluck('company_id')->unique()->filter();

        $companies = Company::whereIn('company_id', $companyIds)
            ->select('company_id', 'company_name', 'user_id')
            ->get()
            ->keyBy('company_id'); // Store by company_id for quick lookup

        $userIds = $companies->pluck('user_id')->unique()->filter();

        $users = User::whereIn('user_id', $userIds)
            ->select('user_id', 'profile_img')
            ->get()
            ->keyBy('user_id'); // Store by user_id for quick lookup

        $formattedJobposts = $jobposts->map(function ($jobpost) use ($companies, $users) {
            $company = $companies->get($jobpost->company_id); // Get company if exists
            $user = $company ? $users->get($company->user_id) : null; // Get user if company exists

            return [
                'job_id' => $jobpost->job_id,
                'job_title' => $jobpost->job_title,
                'job_status' => $jobpost->job_status,
                'job_type' => $jobpost->job_type,
                'job_country' => $jobpost->job_country,
                'job_city' => $jobpost->job_city,
                'company_name' => $company->company_name,    
                'profile_img' => $user->profile_img, // Use accessor
                'time_ago' => $this->getTimeAgo($jobpost->created_at),
            ];
        });

        return response()->json(['jobposts' => $formattedJobposts]);
    }

        



    public function jobsearch()
    {
    
        $jobs = Jobpost::select("job_id", "job_title", "job_about", "job_type", "job_country", "job_city", "company_id","job_status", "created_at")
            ->get()
            ->filter(fn($job) => $job->job_status === "Active");
            
    
        $companyIds = $jobs->pluck('company_id')->unique()->filter();
    
        $companies = Company::whereIn('company_id', $companyIds)
            ->select('company_id', 'company_name', 'user_id')
            ->get()
            ->keyBy('company_id'); // Store by company_id for quick lookup
    
        $userIds = $companies->pluck('user_id')->unique()->filter();
    
        $users = User::whereIn('user_id', $userIds)
            ->select('user_id', 'profile_img')
            ->get()
            ->keyBy('user_id'); // Store by user_id for quick lookup
    
        $formattedJobs = $jobs->map(function ($job) use ($companies, $users) {
            $company = $companies->get($job->company_id); // Get company if exists
            $user = $company ? $users->get($company->user_id) : null; // Get user if company exists
    
            return [
                'job_id' => $job->job_id,
                'job_title' => $job->job_title,
                'job_about' => $job->job_about,
                'job_type' => $job->job_type,
                'job_country' => $job->job_country,
                'job_city' => $job->job_city,
                'time_ago' => $this->getTimeAgo($job->created_at),
                'company' => $company ? [
                    'company_id' => $company->company_id,
                    'company_name' => $company->company_name,
                    'user' => $user ? [
                        'user_id' => $user->user_id,
                        'profile_img' => $user->profile_img,
                    ] : null
                ] : null
            ];
        });
    
        return response()->json([
            'jobs' => $formattedJobs
        ]);
    }





    public function recommendedjobsposts(Request $request){

        $seeker = Jobseeker::where('seeker_id', $request->seeker_id)->first();
        $user = User::where('user_id', $seeker->user_id)->first();

        $seekerSkillsIds = SeekerSkill::where('seeker_id', $seeker->seeker_id)
                                    ->pluck('skill_id'); 

        $seekerSkillsDetails = Skill::whereIn('skill_id', $seekerSkillsIds)
                                    ->pluck('skill') 
                                    ->toArray(); 

        $jobs = JobPost::all()->map(function ($job) {

            $jobSkillIds = JobPostSkill::where('job_id', $job->job_id)
                                        ->pluck('skill_id'); 
            $jobSkills = Skill::whereIn('skill_id', $jobSkillIds)
                            ->pluck('skill') 
                            ->toArray(); 

            return [
                "job_id" => $job->job_id,
                "title" => $job->job_title,
                "about" => $job->job_about,
                'country' => $job->job_country,
                "skills" => $jobSkills
            ];
        });

       
        $data = [
            "seeker_headline" => $user->headline,
            "seeker_skills" => $seekerSkillsDetails, 
            "seeker_country" => $seeker->country,
            "jobs" => $jobs
        ];


        $response = Http::post('http://127.0.0.2:8001/recommend_jobs', $data);


        if ($response->failed()) {
            return response()->json(["error" => "Failed to connect to recommendation system"], 500);
        }


        $recommendedJobs = $response->json();
        $jobIds = array_column($recommendedJobs, 'job_id');

        foreach ($jobIds as $jobId) {
            // Fetch the job post details based on job_id
            $job = JobPost::firstWhere('job_id', $jobId);
        
            // If job is found, fetch additional details
            if ($job) {
                // Fetch the company name using the company_id
                $company = Company::where('company_id', $job->company_id)->first();
                $user = User::where('user_id', $company->user_id)->first();
        
                // Fetch the job skills related to the job
                $skillsIds = JobPostSkill::where('job_id', $jobId)->pluck('skill_id');
                $skills = Skill::whereIn('skill_id', $skillsIds)->pluck('skill')->toArray();
        
                // Structure the job details
                $recommendedJobsDetails[$jobId] = [
                    "job_id" => $job->job_id,
                    "title" => $job->job_title,
                    "job_about" => $job->job_about,
                    "job_type" => $job->job_type,
                    "job_country" => $job->job_country,
                    "job_city" => $job->job_city,
                    "time_ago" => $this->getTimeAgo($job->created_at),
                    "score" => $recommendedJobs[array_search($jobId, $jobIds)]['score'], // Add the score from recommended jobs
                    "company" => [
                        "company_id" => $company->company_id,
                        "company_name" => $company->company_name,
                        "profile_img" => $user->profile_img,
                    ],
                    "skills" => $skills
                ];
            }
        }
        
        // After the loop ends, return the response with all job details
        return response()->json($recommendedJobsDetails);
        



        
    }


}


