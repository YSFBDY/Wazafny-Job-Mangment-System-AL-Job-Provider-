<?php

namespace App\Http\Controllers\Company;
use App\Http\Controllers\Controller;


use App\Models\Skill;
use App\Models\Jobpost;
use App\Models\JobpostSkill;
use App\Models\Jobsection;
use App\Models\Question;
use App\Models\Company;
use App\Models\Follower;
use App\Models\Notification;



use Illuminate\Http\Request;
use App\Http\Requests\JobPost\CreateJobPostRequest;
use App\Http\Requests\JobPost\UpdateJobPostInfoRequest;

use App\Traits\TimeAgo;

use Carbon\Carbon;

class CompanyJobPostController extends Controller
{
    use TimeAgo;
    public function createjobpost(CreateJobPostRequest $request) {
        $company = Company::find($request->company_id);
        if ($company && collect($company->toArray())->contains(fn($value) => is_null($value) || $value === '')) {
            return response()->json(['message' => 'Complete Your Profile First'], 400);
        }

        $jobpost = Jobpost::create([
            "job_title" => $request->job_title,
            "job_about" => $request->job_about,
            "job_time" => $request->job_time,
            "job_type" => $request->job_type,
            "job_country" => $request->job_country,
            "job_city" => $request->job_city,
            "job_status" => "Active",
            "company_id" => $request->company_id
        ]);


        foreach ($request->input('skills', []) as $skillName) {
            $skill = Skill::firstOrCreate(['skill' => $skillName]);
            JobpostSkill::create([
                'job_id' => $jobpost->job_id,
                'skill_id' => $skill->skill_id
            ]);
        }

        foreach ($request->input('questions', []) as $questionText) {
             Question::create([
                'question' => $questionText,
                'job_id' => $jobpost->job_id

            ]);
        }

        foreach ($request->input('sections', []) as $section) {
             JobSection::create([
                'section_name' => $section['name'],
                'section_description' => $section['description'],
                'job_id' => $jobpost->job_id
            ]);
        }

        $followers = Follower::where('company_id', $request->company_id)->pluck('seeker_id');

        foreach ($followers as $seeker_id) {
            Notification::create([
                'seeker_id' => $seeker_id,
                'message' => "{$company->company_name} posted a new job for : {$request->job_title}",
                'job_id' => $jobpost->job_id,
            ]);
        }

        return response()->json(['message' => 'Job post created successfully']);
    

    }






     
    public function showJobPostInfo($job_id) {
        $jobpost = Jobpost::select('job_id', 'job_title', 'job_about', 'job_time', 'job_type', 'job_country', 'job_city')
            ->where('job_id', $job_id)
            ->firstOrFail();
        return response()->json($jobpost);
    }
    
    public function showJobPostSkills($job_id) {
        $skillIds = JobpostSkill::where('job_id', $job_id)->pluck('skill_id');
        $skills = Skill::select('skill_id', 'skill')->whereIn('skill_id', $skillIds)->select( 'skill')->get();
        return response()->json($skills);
    }

    public function showJobPostSections($job_id) {
        $sections = JobSection::select('section_id', 'section_name', 'section_description')->where('job_id', $job_id)->get();
        return response()->json($sections);
    }

    public function showJobPostQuestions($job_id) {
        $questions = Question::select('question_id', 'question')->where('job_id', $job_id)->get();
        return response()->json($questions);
    }


    public function showjobposts($company_id) {
        $jobposts = Jobpost::select('job_id', 'job_title', 'job_status', 'created_at')
        ->where('company_id', $company_id)
        ->get()
        ->map(function ($job) {
            return [
                'job_id' => $job->job_id,
                'job_title' => $job->job_title,
                'job_status' => $job->job_status,
                'created_at' => Carbon::parse($job->created_at)->format('d/m/Y'),
            ];
        });

    return response()->json(['jobposts' => $jobposts]);

    }





    public function updateJobPostInfo(UpdateJobPostInfoRequest $request, $job_id) {
        $jobpost = Jobpost::findOrFail($job_id);
        if($jobpost->job_status == "Closed") {
            return response()->json(['message' => 'Job post is already closed']);
        }

        $jobpost->update($request->only(['job_title', 'job_about', 'job_time', 'job_type', 'job_country', 'job_city']));

        JobpostSkill::where('job_id', $job_id)->delete(); 
        foreach ($request->input('skills', []) as $skillName) {
            $skill = Skill::firstOrCreate(['skill' => $skillName]);
            JobpostSkill::create([  
                'job_id' => $job_id,
                'skill_id' => $skill->skill_id
            ]);
        }

        foreach ($request->input('sections', []) as $section) {
            JobSection::updateOrCreate(
                [
                'job_id' => $job_id,
                'section_id' => $section['section_id'] ?? null], // Match by job_id and existing section ID
                [
                    'section_name' => $section['name'],
                    'section_description' => $section['description']
                        ]
            );
        }

        foreach ($request->input('questions', []) as $questionText) {
            Question::updateOrCreate([
                'job_id' => $job_id,
                'question_id' => $questionText['question_id'] ?? null],
                ['question' => $questionText['question']]
        );
        }

        return response()->json(['message' => 'Job post updated successfully']);
    }







    public function deleteJobSkill($job_id, $skill_id) {
        JobpostSkill::where('job_id', $job_id)->where('skill_id', $skill_id)->delete();
        return response()->json(['message' => 'Skill removed from job post successfully']);
    }
    
    public function deleteJobSection($section_id) {
        JobSection::where('section_id', $section_id)->delete();
        return response()->json(['message' => 'Job section deleted successfully']);
    }
    
    public function deleteJobQuestion($question_id) {
        Question::where('question_id', $question_id)->delete();
        return response()->json(['message' => 'Job question deleted successfully']);
    }





    public function closejobpost($job_id) {
        $jobpost = Jobpost::findOrFail($job_id);
        $jobpost->update(['job_status' => 'Closed']);
        return response()->json(['message' => 'Job post closed successfully']);
    }   
    
    



    public function deletejobpost($job_id) {
        // Find the job post
        $jobpost = Jobpost::findOrFail($job_id);
    
        // Delete related records manually (since we are not using relationships)
        JobpostSkill::where('job_id', $job_id)->delete();
        JobSection::where('job_id', $job_id)->delete();
        Question::where('job_id', $job_id)->delete();
    
        // Delete the job post
        $jobpost->delete();
    
        return response()->json(['message' => 'Job post deleted successfully']);
    }


    public function lastestJobPosts($company_id) {
        $jobposts = Jobpost::select('job_id', 'job_title',"job_type","job_country","job_city", 'created_at')
            ->where('company_id', $company_id)
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json($jobposts->map(function ($job) {
                return [
                    'job_id' => $job->job_id,
                    'job_title' => $job->job_title,
                    'job_type' => $job->job_type,
                    'job_country' => $job->job_country,
                    'job_city' => $job->job_city,
                    'time_ago' => $this->getTimeAgo($job->created_at),
                ];
            }));        
        
    }






}
