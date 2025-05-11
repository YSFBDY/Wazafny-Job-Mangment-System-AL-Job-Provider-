<?php

namespace App\Http\Controllers\Seeker;
use App\Http\Controllers\Controller;


use App\Models\Jobseeker;
use App\Models\User;
use App\Models\Link;
use App\Models\SeekerSkill;
use App\Models\Skill;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Follower;
use App\Models\Company;




use Illuminate\Http\Request;
use App\Http\Requests\SeekerProfile\LocationRequest;
use App\Http\Requests\SeekerProfile\HeadlineRequest;
use App\Http\Requests\SeekerProfile\UpdatePersonalInfoRequest;
use App\Http\Requests\SeekerProfile\UpdateProfileResumeRequest;
use App\Http\Requests\SeekerProfile\UpdateAboutRequest;
use App\Http\Requests\SeekerProfile\UpdateSeekerSkillsRequest;
use App\Http\Requests\SeekerProfile\UpdateEducationRequest;
use App\Http\Requests\SeekerProfile\UpdateExprienceRequest;
use App\Http\Requests\SeekerProfile\CreateExprienceRequest;

use Illuminate\Support\Facades\Storage;
use App\Traits\HandleFiles;



use Illuminate\Contracts\Queue\Job;

use function Laravel\Prompts\select;

class SeekerProfileController extends Controller
{
    use HandleFiles;

    public function createlocation(LocationRequest $request) {
        $seeker = Jobseeker::where('seeker_id', $request->seeker_id)->first();
        if (!$seeker || !$request->seeker_id) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        
        $seeker ->update([
                'country' => $request->country,
                'city' => $request->city
            ]
        );
    
        return response()->json(['message' => 'Location created successfully']);
    }







    public function createheadline(HeadlineRequest $request) {
        $seeker = Jobseeker::where('seeker_id', $request->seeker_id)->first();
        if (!$seeker || !$request->seeker_id) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        User::where('user_id', $seeker->user_id) ->update([
                'headline' => $request->headline
            ]
        );
    
        return response()->json(['message' => 'Headline created successfully']);
    }







    public function showpersonalinfo($seeker_id) {
        $seeker = Jobseeker::where('seeker_id', $seeker_id)->first();
        if (!$seeker || !$seeker_id) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        $user = User::select( 'headline', 'profile_img')->where('user_id', $seeker->user_id)->first();
        $links = Link::select('link_id', 'link_name', 'link')->where('seeker_id', $seeker->seeker_id)->get();

        return response()->json([
            'seeker_id' => $seeker->seeker_id,
            'first_name' => $seeker->first_name,
            'last_name' => $seeker->last_name,
            'country' => $seeker->country,
            'city' => $seeker->city,
            'headline' => $user->headline,
            'profile_img' => $user->profile_img,
            'links' => $links
        ]);
    }


    public function updatepersonalinfo(UpdatePersonalInfoRequest $request) {
        $seeker = Jobseeker::where('seeker_id', $request->seeker_id)->first();
        if (!$seeker || !$request->seeker_id) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        User::where('user_id', $seeker->user_id) ->update([
            'headline' => $request->headline
        ]);

        $seeker ->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'country' => $request->country,
            'city' => $request->city
        ]);

        Link::where('seeker_id', $seeker->seeker_id)->delete();

        foreach ($request->input('links', []) as $link) {
            Link::create([
                'seeker_id' => $seeker->seeker_id,
                'link_name' => $link['link_name'],
                'link' => $link['link']
            ]);
        }

        return response()->json(['message' => 'Personal info updated successfully']);



    }



    public function deletelink($link_id) {
        $link = Link::where('link_id', $link_id)->first();
        if (!$link || !$link_id) {
            return response()->json(['message' => 'Link not found'], 404);
        }
        $link->delete();
        return response()->json(['message' => 'Link deleted successfully']);
    }














    public function showabout($seeker_id) {
        $seeker = Jobseeker::where('seeker_id', $seeker_id)->first();
        if (!$seeker || !$seeker_id) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        $user = User::select('user_id', 'about')->where('user_id', $seeker->user_id)->first();

        if(empty($user->about)) {
            return response()->json(status: 204);
        }

        return response()->json([
            'about' => $user->about
        ]);
    }


    public function updateabout(UpdateAboutRequest $request) {
        $seeker = Jobseeker::where('seeker_id', $request->seeker_id)->first();
        if (!$seeker || !$request->seeker_id) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        $user = User::where('user_id', $seeker->user_id)->first();
        $user->update(['about' => $request->about]);
        return response()->json(['message' => 'About updated successfully']);
    }






    public function showresume($seeker_id) {
        if (!$seeker_id) {
            return response()->json([
                'message' => "Seeker not found"
            ],404);
        }
        $seeker = Jobseeker::select( 'resume')->where('seeker_id', $seeker_id)->first();
        if (empty($seeker->resume)) {
            return response()->json(status: 204);

        }
        return response()->json([
            'resume' => $seeker->resume
        ]);
    }


    public function updateresume(UpdateProfileResumeRequest $request) {
        $seeker = Jobseeker::where('seeker_id', $request->seeker_id)->first();
        if (!$seeker || !$request->seeker_id) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        $this->deleteOldFile($seeker->resume, 'profile_resumes');
        $newFileName = $this->handleFileUpload($request, 'resume', 'profile_resumes');
        $seeker->update(['resume' => $newFileName]);
        return response()->json(['message' => 'Resume updated successfully']);
    }


    public function deleteResume($seeker_id) {
        $seeker = Jobseeker::where('seeker_id', $seeker_id)->first();
        if (!$seeker || !$seeker_id) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        $this->deleteOldFile($seeker->resume, 'profile_resumes');
        $seeker->update(['resume' => null]);
        return response()->json(['message' => 'Resume deleted successfully']);
    }







    public function showskills($seeker_id) {
        if (!$seeker_id || !Jobseeker::where('seeker_id', $seeker_id)->first()) {
            return response()->json([
                'message' => "Seeker not found"
            ],404);
        }
        $skillsId = SeekerSkill::select('skill_id')->where('seeker_id', $seeker_id)->pluck('skill_id');
        if (!$skillsId) {
            return response()->json([
                'message' => "Skills not found"
            ],400);
        }
        $skills = Skill::select('skill_id', 'skill')->whereIn('skill_id', $skillsId)->get();
        if (!$skills) {
            return response()->json(status:204);
        }
        return response()->json($skills);
    }


    public function updateskills(UpdateSeekerSkillsRequest $request) {
        if (!$request->seeker_id || !Jobseeker::where('seeker_id', $request->seeker_id)->first()) {
            return response()->json([
                'message' => "Seeker not found"
            ],404);
        }
        SeekerSkill::where('seeker_id', $request->seeker_id)->delete();
        foreach ($request->input('skills', []) as $skillName) {
            $skill = Skill::firstOrCreate(['skill' => $skillName]);
            SeekerSkill::create([  
                'seeker_id' => $request->seeker_id,
                'skill_id' => $skill->skill_id
            ]);
        }

        return response()->json(['message' => 'Job skills updated successfully']);
    }


    public function deleteskill($seeker_id, $skill_id) {
        if (!$seeker_id || !Jobseeker::where('seeker_id', $seeker_id)->first()) {
            return response()->json([
                'message' => "Seeker not found"
            ],404);
        }
        if (!$skill_id || !Skill::where('skill_id', $skill_id)->first()) {
            return response()->json([
                'message' => "Skill not found"
            ],404);
        }
        SeekerSkill::where('seeker_id', $seeker_id)->where('skill_id', $skill_id)->delete();
        return response()->json(['message' => 'Skill removed from job post successfully']);
    }







    public function showeducation($seeker_id) {
        if (!$seeker_id || !Jobseeker::where('seeker_id', $seeker_id)->first()) {
            return response()->json([
                'message' => "Seeker not found"
            ],404);
        }
        $education = Education::where('seeker_id', $seeker_id)->first();
        if (!$education) {
            return response()->json(status:204);
        }
        return response()->json([
            'education' => $education
        ]);
        
    }


    public function updateeducation(UpdateEducationRequest $request) {
        if (!$request->seeker_id || !Jobseeker::where('seeker_id', $request->seeker_id)->first()) {
            return response()->json([
                'message' => "Seeker not found"
            ],404);
        }
            
        Education::updateOrCreate(
            ['seeker_id' => $request->seeker_id], // Search condition
            [
                'university' => $request->university,
                'college' => $request->college,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        );
        return response()->json(['message' => 'Education updated successfully']);
        
    }


    public function deleteeducation($seeker_id) {
        if (!$seeker_id || !Jobseeker::where('seeker_id', $seeker_id)->first()) {
            return response()->json([
                'message' => "Seeker not found"
            ],404);        
        }
            
        Education::where('seeker_id', $seeker_id)->delete();
        return response()->json(['message' => 'Education deleted successfully']);
        
    }







    public function createexperience(CreateExprienceRequest $request) {
        if (!$request->seeker_id || !Jobseeker::where('seeker_id', $request->seeker_id)->first()) {
            return response()->json([
                'message' => "Seeker not found"
            ],404);
        }
        Experience::Create([
                'company' => $request->company,
                'job_title' => $request->job_title,
                'job_time' => $request->job_time,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'seeker_id' => $request->seeker_id
            ]
        );
        return response()->json(['message' => 'Experience created successfully']);
    }


    public function showexperience($experience_id) {
        if (!$experience_id){
            return response()->json([
                'message' => "Experience not found"
            ],400);
        }

        if (!Experience::where('experience_id', $experience_id)->first()) {
            return response()->json(status:204);
        }
        $experience = Experience::where('experience_id', $experience_id)->first();
        return response()->json([
            'experience' => $experience
        ]);
        
    }


    public function updateexperience(UpdateExprienceRequest $request,$experience_id) {
        if (!$experience_id || !Experience::where('experience_id', $experience_id)->first()) {
            return response()->json([
                'message' => "Experience not found"
            ],404);
        }
        Experience::where('experience_id', $experience_id)->
            update( [
                'company' => $request->company,
                'job_title' => $request->job_title,
                'job_time' => $request->job_time,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]
        );
        return response()->json(['message' => 'Experience updated successfully']);
        
    }


    public function deleteexperience($experience_id) {
        if (!$experience_id || !Experience::where('experience_id', $experience_id)->first()) {
            return response()->json([
                'message' => "Experience not found"
            ],404);
        }
        Experience::where('experience_id', $experience_id)->delete();
        return response()->json(['message' => 'Experience deleted successfully']);
        
    }








    public function showSeekerProfile($seeker_id) {
        $seeker = Jobseeker::where('seeker_id', $seeker_id)->first();
        if (!$seeker) {
            return response()->json(['message' => 'Seeker not found'], 404);
        }
        
        $followingsNo = Follower::where('seeker_id', $seeker_id)->count();


        $user = User::where('user_id', $seeker->user_id)->first();
    
        // Get experience details (multiple experiences)
        $experiences = Experience::where('seeker_id', $seeker->seeker_id)->get();
    
        // Get skills
        $skillsId = SeekerSkill::select('skill_id')->where('seeker_id', $seeker->seeker_id)->pluck('skill_id');
        $skills = Skill::select('skill_id', 'skill')->whereIn('skill_id', $skillsId)->get();
    
        // Get education
        $education = Education::where('seeker_id', $seeker->seeker_id)->first();
    
        // Get links
        $links = Link::where('seeker_id', $seeker->seeker_id)->get();

        $followings = Follower::where('seeker_id', $seeker_id)->get();

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
            'personal_info' => [
                'seeker_id' => $seeker->seeker_id,
                'followings' => $followingsNo,
                'first_name' => $seeker->first_name,
                'last_name' => $seeker->last_name,
                'country' => $seeker->country,
                'city' => $seeker->city,
                'headline' => $user->headline,
                'about' => $user->about,
                'profile_img' => $user->profile_img,
                'cover_img' => $user->cover_img,
                'resume' => $seeker->resume,
                'links' => $links
            ],
            'experiences' => $experiences,
            'skills' => $skills,
            'education' => $education,
            'followings' => $formattedCompanies,

        ]);
    }
    



    
}
