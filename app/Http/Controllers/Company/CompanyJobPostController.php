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
use App\Models\Application;
use App\Traits\TimeAgo;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\Job;

class CompanyJobPostController extends Controller
{
    use TimeAgo;
    public function createjobpost(CreateJobPostRequest $request) {
        $company = Company::find($request->company_id);
        if(!$company) {
            return response()->json(['message' => 'Invalid Company'], 404);
        }

        if ($company->isIncompleteProfile()) {
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
                'message' => "posted a new job for : {$request->job_title}",
                'job_id' => $jobpost->job_id,
            ]);
        }

        return response()->json(['message' => 'Job post created successfully']);
    

    }






     
    public function showJobPostInfo($job_id) {
        if(Jobpost::where('job_id', $job_id)->doesntExist() || !$job_id) {
            return response()->json(['message' => 'Job post not found or does not exist'], 404);
        }
        $jobpost = Jobpost::select('job_id', 'job_title', 'job_about', 'job_time', 'job_type', 'job_country', 'job_city')
            ->where('job_id', $job_id)
            ->firstOrFail();
        if(!$jobpost) {
            return response()->json(['message' => 'Job post Info not found'], 404);
        }
        return response()->json($jobpost);
    }
    
    public function showJobPostSkills($job_id) {
        if(Jobpost::where('job_id', $job_id)->doesntExist() || !$job_id) {
            return response()->json(['message' => 'Job post not found or does not exist'], 404);
        }
        $skillIds = JobpostSkill::where('job_id', $job_id)->pluck('skill_id');
        if(!$skillIds) {
            return response()->json(status:204);
        }
        $skills = Skill::select('skill_id', 'skill')->whereIn('skill_id', $skillIds)->select( 'skill')->get();
        return response()->json($skills);
    }

    public function showJobPostSections($job_id) {
        if(Jobpost::where('job_id', $job_id)->doesntExist() || !$job_id) {
            return response()->json(['message' => 'Job post not found or does not exist'], 404);
        }
        $sections = JobSection::select('section_id', 'section_name', 'section_description')->where('job_id', $job_id)->get();
        if(!$sections) {
            return response()->json(status:204);
        }
        return response()->json($sections);
    }

    public function showJobPostQuestions($job_id) {
        if(Jobpost::where('job_id', $job_id)->doesntExist() || !$job_id) {
            return response()->json(['message' => 'Job post not found or does not exist'], 404);
        }
        $questions = Question::select('question_id', 'question')->where('job_id', $job_id)->get();
        if(!$questions) {
            return response()->json(status:204);
        }
        return response()->json($questions);
    }


    public function showjobposts($company_id) {
        if(Company::where('company_id', $company_id)->doesntExist() || !$company_id) {
            return response()->json(['message' => 'Company not found or does not exist'], 404);
        }
        $jobposts = Jobpost::select('job_id', 'job_title', 'job_status', 'created_at');
        if(!$jobposts) {
            return response()->json(status:204);
        }
        $jobposts = $jobposts
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
        if(Jobpost::where('job_id', $job_id)->doesntExist() || !$job_id) {
            return response()->json(['message' => 'Job post not found or does not exist'], 404);
        }
        $jobpost = Jobpost::findOrFail($job_id);

        if($jobpost->job_status == "Closed") {
            return response()->json(['message' => 'Job post is already closed'], 400);
        }

        JobSection::where('job_id', $job_id)->delete();
        Question::where('job_id', $job_id)->delete();

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
        if(Jobpost::where('job_id', $job_id)->doesntExist() || !$job_id || !$skill_id) {
            return response()->json(['message' => 'Job post not found or does not exist'], 404);
        }
        if(Skill::where('skill_id', $skill_id)->doesntExist() || !$skill_id) {
            return response()->json(['message' => 'Skill not found or does not exist'], 404);
        }
        JobpostSkill::where('job_id', $job_id)->where('skill_id', $skill_id)->delete();
        return response()->json(['message' => 'Skill removed from job post successfully']);
    }
    
    public function deleteJobSection($section_id) {
        if(JobSection::where('section_id', $section_id)->doesntExist() || !$section_id) {
            return response()->json(['message' => 'Job section not found or does not exist'], 404);
        }
        JobSection::where('section_id', $section_id)->delete();
        return response()->json(['message' => 'Job section deleted successfully']);
    }
    
    public function deleteJobQuestion($question_id) {
        if(Question::where('question_id', $question_id)->doesntExist() || !$question_id) {
            return response()->json(['message' => 'Job question not found or does not exist'], 404);
        }
        Question::where('question_id', $question_id)->delete();
        return response()->json(['message' => 'Job question deleted successfully']);
    }





    public function closejobpost($job_id) {
        if(Jobpost::where('job_id', $job_id)->doesntExist() || !$job_id) {
            return response()->json(['message' => 'Job post not found or does not exist'], 404);
        }
        $jobpost = Jobpost::where('job_id', $job_id)->first();

        if($jobpost->job_status == "Closed") {
            return response()->json(['message' => 'Job post is already closed'], 400);
        }
        $jobpost->update(['job_status' => 'Closed']);
        return response()->json(['message' => 'Job post closed successfully']);
    }   
    
    



    public function deletejobpost($job_id) {
        if(Jobpost::where('job_id', $job_id)->doesntExist() || !$job_id) {
            return response()->json(['message' => 'Job post not found or does not exist'], 404);
        }
        $jobpost = Jobpost::findOrFail($job_id);
    
        JobpostSkill::where('job_id', $job_id)->delete();
        JobSection::where('job_id', $job_id)->delete();
        Question::where('job_id', $job_id)->delete();
    
        $jobpost->delete();
    
        return response()->json(['message' => 'Job post deleted successfully']);
    }


    public function lastestJobPosts($company_id) {
        if(Company::where('company_id', $company_id)->doesntExist() || !$company_id) {
            return response()->json(['message' => 'Invalid Company'], 404);
        }
        $jobposts = Jobpost::select('job_id', 'job_title',"job_type","job_country","job_city", 'created_at')
            ->where('company_id', $company_id)
            ->orderBy('created_at', 'desc')
            ->get();

            if (!$jobposts) {
                return response()->json(status:204);
            }

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





    public function ActivateJobPost($job_id) {

        $jobpost = Jobpost::where('job_id', $job_id)->first();

        if(! $jobpost || !$job_id) {

            return response()->json(['message' => 'Job post not found or does not exist'], 404);

        }

        if($jobpost->job_status == 'Active') {

            return response()->json(['message' => 'Job post is already active'], 400);

        }

        $jobpost->forceFill([
            'job_status' => 'Active',
            'created_at' => now(),
        ])->save();

        Application::where('job_id', $job_id)->delete();

        return response()->json(['message' => 'Job post activated successfully']);


    }



}
