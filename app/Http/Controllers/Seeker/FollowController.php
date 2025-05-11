<?php

namespace App\Http\Controllers\Seeker;
use App\Http\Controllers\Controller;

use App\Models\Follower;
use App\Models\Company;
use App\Models\User;


use Illuminate\Http\Request;
use App\Http\Requests\Follow\FollowRequest;
use App\Models\Jobseeker;

class FollowController extends Controller
{
    public function follow(FollowRequest $request) {
        if(!Jobseeker::where('seeker_id', $request->seeker_id)->first()) {
            return response()->json(['message' => 'Seeker not found'], 404);  
        }

        if(!Company::where('company_id', $request->company_id)->first()) {
            return response()->json(['message' => 'Company not found'], 404);  
        }

        Follower::create([
            'seeker_id' => $request->seeker_id,
            'company_id' => $request->company_id
        ]);


        return response()->json(['message' => 'Followed successfully']);
    }



    public function unfollow(FollowRequest $request) {
        if(!Jobseeker::where('seeker_id', $request->seeker_id)->first()) {
            return response()->json(['message' => 'Seeker not found'], 404);  
        }

        if(!Company::where('company_id', $request->company_id)->first()) {
            return response()->json(['message' => 'Company not found'], 404);  
        }

        Follower::where('seeker_id', $request->seeker_id)->where('company_id', $request->company_id)->delete();
        return response()->json(['message' => 'Unfollowed successfully']);

        

    }


    public function showfollowings($seeker_id) {
        if (!$seeker_id) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }

        $followings = Follower::where('seeker_id', $seeker_id)->get();

        if ($followings->isEmpty()) {
            return response()->json(status: 204);
        }
        $comaniesIds = $followings->pluck('company_id')->filter();

        $companies = Company::whereIn('company_id', $comaniesIds)
        ->select('company_id', 'company_name', 'user_id')
        ->get()
        ->keyBy('company_id'); // Store by company_id for quick lookup

        $userIds = $companies->pluck('user_id')->unique()->filter();

        $users = User::whereIn('user_id', $userIds)
            ->select('user_id', 'profile_img')
            ->get()
            ->keyBy('user_id'); // Store by user_id for quick lookup

        $formattedCompanies = $companies->map(function ($company) use ($users) {
            $user = $users->get($company->user_id);

            return [
                'company_id' => $company->company_id,
                'company_name' => $company->company_name,
                'profile_img' => $user->profile_img,
            ];  
        })->values();

        return response()->json([
            'followings' => $formattedCompanies,
        ]);



    }


    public function getfollowstatus($seeker_id, $company_id) {
        if (!$seeker_id || !$company_id) { 
            return response()->json(['message' => 'Seeker or Company not found'], 404);
        }

        if(!Jobseeker::where('seeker_id', $seeker_id)->first()) {
            return response()->json(['message' => 'Seeker not found'], 404);  
        }

        if(!Company::where('company_id', $company_id)->first()) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        $follow = Follower::where('seeker_id', $seeker_id)->where('company_id', $company_id)->first();

        if ($follow) {
            return response()->json(['followed' => true]);
        } else {
            return response()->json(['followed' => false]);
        }
    }



}
