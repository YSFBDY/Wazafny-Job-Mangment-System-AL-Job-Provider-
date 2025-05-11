<?php

namespace App\Http\Controllers\Seeker;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Http\Requests\Application\CreateAppicationRequest;
use App\Http\Requests\Application\UpdateApplicationRequest;

use App\Models\Application;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Jobpost;
use App\Models\Jobseeker;
use App\Models\User;
use App\Models\Company;



use App\Traits\HandleFiles;
use App\Traits\TimeAgo;


class SeekerApplicationController extends Controller
{
    use TimeAgo;
    use HandleFiles;
    public function createappication(CreateAppicationRequest $request){
        if(!$request->seeker_id || Jobseeker::where('seeker_id', $request->seeker_id)->doesntExist()){
            return response()->json(['message' => 'Seeker not found'], 404);
            
        }

        $job=Jobpost::where('job_id', $request->job_id)->first();
        if($job->job_status=='Closed'){
            return response()->json(['message' => 'Job is closed'], 400);
        }

        $app = Application::where('seeker_id', $request->seeker_id)->where('job_id', $request->job_id)->first();
        if($app){
            return response()->json(['message' => 'You have already applied for this job'], 406);
        }


        if(!$request->job_id || Jobpost::where('job_id', $request->job_id)->doesntExist()){
            return response()->json(['message' => 'Job not found'], 404);
        }

            $this->deleteOldFile($request->resume, 'application_resumes');
            $resumeName = $this->handleFileUpload($request, 'resume', 'application_resumes');


        $application = Application::create([
            'status' => 'Pending',
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'resume' => $resumeName,
            'country' => $request->country,
            'city' => $request->city,
            'phone' => $request->phone,
            'seeker_id' => $request->seeker_id,
            'job_id' => $request->job_id
        ]);

        if ($request->has('answers')) {
            foreach ($request->answers as $question_id => $answer) {
                Answer::create([
                    'application_id' => $application->application_id,
                    'question_id' => $question_id,
                    'answer' => $answer,
                ]);
            }
        }

        $jobtitle = Jobpost::where('job_id', $request->job_id)->first()->job_title;
        $companyname = Company::where('company_id', Jobpost::where('job_id', $request->job_id)->first()->company_id)->first()->company_name;



        return response()->json([
            'message' => 'Application Submitted Successfully',
            'job_title' => $jobtitle,
            'company_name' => $companyname
        
        ]);

    }





    public function showapplicationsseeker($seeker_id, $status = null){
        if (!$seeker_id) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        
        $applications = Application::select("application_id","response","status","job_id","created_at")
            ->where('seeker_id', $seeker_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->keyBy('application_id');

        if (!$applications) {
            return response()->json(status: 204);
        }


            
        if ($status) {
            $applications = $applications->filter(fn($app) => $app->status === $status);
        }

        $jobs_id = $applications->pluck('job_id')->unique()->filter();

        $jobs = Jobpost::whereIn('job_id', $jobs_id)
            ->select('job_id', 'job_title', 'job_type','job_time', 'job_country', 'job_city', 'company_id')
            ->get()
            ->keyBy('job_id'); 

        $companyIds = $jobs->pluck('company_id')->unique()->filter();

        $companies = Company::whereIn('company_id', $companyIds)
            ->select('company_id', 'company_name', 'user_id')
            ->get()
            ->keyBy('company_id'); // Store by company_id for quick lookup

        $usercompanyIds = $companies->pluck('user_id')->unique()->filter();

        $users = User::whereIn('user_id', $usercompanyIds)
            ->select('user_id', 'profile_img')
            ->get()
            ->keyBy('user_id'); // Store by user_id for quick lookup


           $formattedApplications = $applications->map(function ($app) use ($jobs, $companies, $users) {
                $job = $jobs->get($app->job_id);
                $company = $job ? $companies->get($job->company_id) : null;
                $user = $company ? $users->get($company->user_id) : null;

                $applicationData = [
                    'application_id' => $app->application_id,
                    'status' => $app->status,
                    'time_ago' => $this->getTimeAgo($app->created_at),
                    'job' => $job ? [
                        'job_id' => $job->job_id,
                        'job_title' => $job->job_title,
                        'job_type' => $job->job_type,
                        'job_time' => $job->job_time,
                        'job_country' => $job->job_country,
                        'job_city' => $job->job_city,
                        'company' => $company ? [
                            'company_id' => $company->company_id,
                            'company_name' => $company->company_name,
                            'profile_img' => $user->profile_img,
                        ] : null,
                    ] : null,
                    'response' => $app->response
                ];



                return $applicationData;
            })->values(); ;

            return response()->json(['applications' => $formattedApplications]);
        
    }





    public function latestApplicationsSeeker($seeker_id) {
        if (!$seeker_id || !Jobseeker::where('seeker_id', $seeker_id)->first()) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        
        $applications = Application::where('seeker_id', $seeker_id)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get(['application_id', 'status', 'job_id', 'created_at'])
            ->keyBy('application_id');
    
        if (!$applications) {
            return response()->json(status: 204);
        }
    
        $jobs = Jobpost::whereIn('job_id', $applications->pluck('job_id'))
            ->get(['job_id', 'job_title', 'job_type', 'job_country', 'job_city', 'company_id'])
            ->keyBy('job_id');
    
        $companies = Company::whereIn('company_id', $jobs->pluck('company_id'))
            ->get(['company_id', 'company_name', 'user_id'])
            ->keyBy('company_id');
    
        $users = User::whereIn('user_id', $companies->pluck('user_id'))
            ->get(['user_id', 'profile_img'])
            ->keyBy('user_id');
    
        $formattedApplications = $applications->map(function ($app) use ($jobs, $companies, $users) {
            $job = $jobs->get($app->job_id);
            $company = $job ? $companies->get($job->company_id) : null;
            $user = $company ? $users->get($company->user_id) : null;
    
            return [
                'application_id' => $app->application_id,
                'status' => $app->status,
                'time_ago' => $this->getTimeAgo($app->created_at),
                'job' => $job ? [
                    'job_id' => $job->job_id,
                    'job_title' => $job->job_title,
                    'job_type' => $job->job_type,
                    'job_country' => $job->job_country,
                    'job_city' => $job->job_city,
                    'company' => $company ? [
                        'company_id' => $company->company_id,
                        'company_name' => $company->company_name,
                        'profile_img' => $user->profile_img,
                    ] : null
                ] : null
            ];
        })->values();
    
        return response()->json(['applications' => $formattedApplications]);
    }



    
    public function showapplicationseeker($application_id) {
        $application = Application::where('application_id', $application_id)->first();
        if (!$application || !$application_id) {
            return response()->json(['message' => 'Application not found'], 404);
        }
        $job = Jobpost::where('job_id', $application->job_id)->first();
        $answers = Answer::where('application_id', $application_id)->get();
        $questions = Question::where('job_id', $application->job_id)->get();

        $formattedQuestions = $questions->map(function ($question) use ($answers) {
            $answer = $answers->firstWhere('question_id', $question->question_id);
            
            return [
                'question_id' => $question->question_id,
                'question_text' => $question->question,
                'answer' => $answer ? $answer->answer : null // Get answer text or null
            ];
        });


        return response()->json([
            'application_id' => $application->application_id,
            'first_name' => $application->first_name,
            'last_name' => $application->last_name,
            'phone' => $application->phone,
            'email' => $application->email,
            'country' => $application->country,
            'city' => $application->city,
            'resume' => $application->resume,
            'questions' => $formattedQuestions
        ]);

        
    } 
    





    public function updateapplication(UpdateApplicationRequest $request, $application_id) {
        $application = Application::where('application_id', $application_id)->first();
        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        if ($application->status !== 'Pending') {
            return response()->json(['message' => 'Cannot update Application is not in pending status'], 400);
        }

        if ($request->hasFile('resume')) {
            $this->deleteOldFile($application->resume, 'application_resumes');
            $resumeName = $this->handleFileUpload($request, 'resume', 'application_resumes');
            $application->resume = $resumeName;
        };

        $application->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'country' => $request->country,
            'city' => $request->city,
            'phone' => $request->phone,
        ]);

        if (isset($resumeName)) {
            $application->resume = $resumeName;
            $application->save();
        }


        if ($request->has('answers')) {
            foreach ($request->answers as $question_id => $answerText) {
                Answer::where('application_id', $application_id)
                    ->where('question_id', $question_id)
                    ->update(['answer' => $answerText]);
            }
        }
        


        return response()->json(['message' => 'Application updated successfully']);


    }




      
    public function deleteapplication($application_id) {
        $application = Application::where('application_id', $application_id)->get()->first();
        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }
        $filePath = basename($application->resume);
        Storage::disk('application_resumes')->delete($filePath);
        $application->delete();
    
            return response()->json(['message' => 'Application deleted successfully']);
    
    }

    

}
