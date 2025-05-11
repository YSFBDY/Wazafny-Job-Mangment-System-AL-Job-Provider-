<?php

namespace App\Http\Controllers\Seeker;
use App\Http\Controllers\Controller;

use App\Models\Notification;
use App\Models\Jobpost;
use App\Models\Company;
use App\Models\User;

use Illuminate\Http\Request;


use App\Traits\TimeAgo;

class NotificationController extends Controller
{
    use TimeAgo;
    public function shownotifications($seeker_id)
    {
        $notifications = Notification::where('seeker_id',$seeker_id)    
            ->orderBy('created_at', 'desc')
            ->get()
            ->keyBy('notification_id'); 

        $jobIds = $notifications->pluck('job_id')->filter();
        
        $jobs = Jobpost::whereIn('job_id', $jobIds)->get()->keyBy('job_id');
  
        $companyIds = $jobs->pluck('company_id')->unique()->filter();

        $companies = Company::whereIn('company_id', $companyIds)->get()->keyBy('company_id'); 

        $userIds = $companies->pluck('user_id')->unique()->filter();

        $users = User::whereIn('user_id', $userIds)->get()->keyBy('user_id'); 

        $formattedNotifications = $notifications->map(function ($notification) use ($jobs, $companies, $users) {
            $job = $jobs->get($notification->job_id);
            $company = $job ? $companies->get($job->company_id) : null;
            $user = $company ? $users->get($company->user_id) : null;

            return [
                'notification_id' => $notification->notification_id,
                'job_id' => $job ? $job->job_id : null,
                'message' => $notification->message,
                'profile_img' => $user ? $user->profile_img : null,
                'time_ago' => $this->getTimeAgo($notification->created_at),
            ];
        });

        return response()->json(['notifications' => $formattedNotifications]);
    }






    public function deletenotification($notification_id)
    {
        $notification = Notification::where('notification_id', $notification_id)->first();

            $notification->delete();
            return response()->json(['message' => 'Notification deleted successfully']);
        
    }





    public function deleteallnotifications($seeker_id)
    {
        Notification::where('seeker_id', $seeker_id)->delete();
        return response()->json(['message' => 'All notifications deleted successfully']);
    }


}
