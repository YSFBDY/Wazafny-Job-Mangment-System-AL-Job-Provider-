<?php

namespace App\Http\Controllers\Company;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests\Application\UpdateApplicationStatusRequest;


use App\Models\Application;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Jobpost;
use App\Models\Jobseeker;
use App\Models\User;

use App\Traits\TimeAgo;


class CompanyApplicationController extends Controller
{
    use TimeAgo;
    
    public function showapplicationcompany($application_id){
        $application = Application::where('application_id', $application_id)->first();
        $seeker = Jobseeker::where('seeker_id', $application->seeker_id)->first();
        $user = User::where('user_id', $seeker->user_id)->first();
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
            'seeker_id' => $seeker->seeker_id,
            'application_id' => $application->application_id,
            'profile_img' => $user->profile_img,
            'job_title' => $job->job_title,
            'first_name' => $application->first_name,
            'last_name' => $application->last_name,
            'country' => $application->country,
            'city' => $application->city,
            'email' => $application->email,
            'phone' => $application->phone,
            'resume' => $application->resume,
            'questions' => $formattedQuestions
        ]);

    
    }





    public function showapplicationscompany($job_id){
        $applications = Application::select("application_id","seeker_id","status","created_at")
            ->where('job_id', $job_id)->get()
            ->keyBy('application_id');

        $seekerIds = $applications->pluck('seeker_id')->unique()->filter();

        $seekers = Jobseeker::whereIn('seeker_id', $seekerIds)
            ->select('seeker_id', 'first_name', 'last_name', 'user_id')
            ->get()
            ->keyBy('seeker_id'); // Store by seeker_id for quick lookup

        $userseekerIds = $seekers->pluck('user_id')->unique()->filter();

        $users = User::whereIn('user_id', $userseekerIds)
            ->select('user_id', 'profile_img')
            ->get()
            ->keyBy('user_id'); // Store by user_id for quick lookup


        $formattedApplications = $applications->map(function ($app) use ($seekers, $users) {
            $seeker = $seekers->get($app->seeker_id);
            $user = $seeker ? $users->get($seeker->user_id) : null;
    
            return [
                'application_id' => $app->application_id,
                'status' => $app->status,
                'time_ago' => $this->getTimeAgo($app->created_at),
                'seeker' => $seeker ? [
                    'seeker_id' => $seeker->seeker_id,
                    'first_name' => $seeker->first_name,
                    'last_name' => $seeker->last_name,
                    'profile_img' => $user->profile_img,
                ] : null
            ];
        });

        return response()->json(['applications' => $formattedApplications]);

    }




    
    public function updateappllicationstatus(UpdateApplicationStatusRequest $request, $application_id) {
        $application = Application::where('application_id', $application_id)->first();
        $application->update([
            'status' => $request->status,
            'response' => $request->response
        ]);

        return response()->json(['message' => 'Application status updated successfully']);
    }




    public function lastestApplicationsCompany($company_id){
        $jobposts = Jobpost::select('job_id', 'job_title')
            ->where('company_id', $company_id)
            ->get()
            ->keyBy('job_id');
            
        $jobIds = $jobposts->pluck('job_id')->filter();

        $applications = Application::whereIn('job_id', $jobIds)
            ->select('application_id', 'seeker_id','job_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $seekerIds = $applications->pluck('seeker_id')->filter();

        $seekers = Jobseeker::whereIn('seeker_id', $seekerIds)
            ->select('seeker_id', 'first_name', 'last_name', 'user_id')
            ->get()
            ->keyBy('seeker_id'); // Store by seeker_id for quick lookup

        $userseekerIds = $seekers->pluck('user_id')->unique()->filter();

        $users = User::whereIn('user_id', $userseekerIds)
            ->select('user_id', 'profile_img')
            ->get()
            ->keyBy('user_id'); // Store by user_id for quick lookup

        $formattedApplications = $applications->map(function ($app) use ($seekers, $users, $jobposts) {
            $seeker = $seekers->get($app->seeker_id);
            $job = $jobposts->get($app->job_id);
            $user = $seeker ? $users->get($seeker->user_id) : null;
    
            return [
                'application_id' => $app->application_id,
                'time_ago' => $this->getTimeAgo($app->created_at),
                'job' => $job ? [ // ✅ Add null check
                    'job_id' => $job->job_id,
                    'job_title' => $job->job_title
                ] : null,
                'seeker' => $seeker ? [ // ✅ Add null check
                    'seeker_id' => $seeker->seeker_id,
                    'first_name' => $seeker->first_name,
                    'last_name' => $seeker->last_name,
                    'profile_img' => $user ? $user->profile_img : null,
                ] : null
            ];
        });

        return response()->json(['applications' => $formattedApplications]);
    }





}
