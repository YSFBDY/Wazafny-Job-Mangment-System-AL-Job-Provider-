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
        $seeker ->update([
                'country' => $request->country,
                'city' => $request->city
            ]
        );
    
        return response()->json(['message' => 'Location created successfully']);
    }







    public function createheadline(HeadlineRequest $request) {
        $seeker = Jobseeker::where('seeker_id', $request->seeker_id)->first();
        User::where('user_id', $seeker->user_id) ->update([
                'headline' => $request->headline
            ]
        );
    
        return response()->json(['message' => 'Headline created successfully']);
    }







    public function showpersonalinfo($seeker_id) {
        $seeker = Jobseeker::where('seeker_id', $seeker_id)->first();
        $user = User::select( 'headline')->where('user_id', $seeker->user_id)->first();
        $links = Link::select('link_id', 'link_name', 'link')->where('seeker_id', $seeker->seeker_id)->get();

        return response()->json([
            'seeker_id' => $seeker->seeker_id,
            'first_name' => $seeker->first_name,
            'last_name' => $seeker->last_name,
            'country' => $seeker->country,
            'city' => $seeker->city,
            'headline' => $user->headline,
            'links' => $links
        ]);
    }


    public function updatepersonalinfo(UpdatePersonalInfoRequest $request) {
        $seeker = Jobseeker::where('seeker_id', $request->seeker_id)->first();
        User::where('user_id', $seeker->user_id) ->update([
            'headline' => $request->headline
        ]);

        $seeker ->update([
            'fisrt_name' => $request->first_name,
            'last_name' => $request->last_name,
            'country' => $request->country,
            'city' => $request->city
        ]);

        foreach ($request->input('links', []) as $link) {
            Link::updateOrCreate(
                [
                    'seeker_id' => $seeker->seeker_id,  
                    'link_name' => $link['link_name'], 
                ],
                [
                    'link' => $link['link'],
                ]
            );
        }

        return response()->json(['message' => 'Personal info updated successfully']);



    }



    public function deletelink($link_id) {
        $link = Link::where('link_id', $link_id)->first();
        $link->delete();
        return response()->json(['message' => 'Link deleted successfully']);
    }














    public function showabout($seeker_id) {
        $seeker = Jobseeker::where('seeker_id', $seeker_id)->first();
        $user = User::select('user_id', 'about')->where('user_id', $seeker->user_id)->first();
        return response()->json([
            'about' => $user->about
        ]);
    }


    public function updateabout(UpdateAboutRequest $request) {
        $seeker = Jobseeker::where('seeker_id', $request->seeker_id)->first();
        $user = User::where('user_id', $seeker->user_id)->first();
        $user->update(['about' => $request->about]);
        return response()->json(['message' => 'About updated successfully']);
    }






    public function showresume($seeker_id) {
        $seeker = Jobseeker::select( 'resume')->where('seeker_id', $seeker_id)->first();
        if (!$seeker->resume) {
            return response()->json([
                'message' => "Resume not found"
            ]);
        }
        return response()->json([
            'resume' => $seeker->resume
        ]);
    }


    public function updateresume(UpdateProfileResumeRequest $request) {
        $seeker = Jobseeker::where('seeker_id', $request->seeker_id)->first();
        $this->deleteOldFile($seeker->resume, 'profile_resumes');
        $newFileName = $this->handleFileUpload($request, 'resume', 'profile_resumes');
        $seeker->update(['resume' => $newFileName]);
        return response()->json(['message' => 'Resume updated successfully']);
    }


    public function deleteResume($seeker_id) {
        $seeker = Jobseeker::where('seeker_id', $seeker_id)->first();
        $this->deleteOldFile($seeker->resume, 'profile_resumes');
        $seeker->update(['resume' => null]);
        return response()->json(['message' => 'Resume deleted successfully']);
    }







    public function showskills($seeker_id) {
        $skillsId = SeekerSkill::select('skill_id')->where('seeker_id', $seeker_id)->pluck('skill_id');
        $skills = Skill::select('skill_id', 'skill')->whereIn('skill_id', $skillsId)->get();
        return response()->json($skills);
    }


    public function updateskills(UpdateSeekerSkillsRequest $request) {
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
        SeekerSkill::where('seeker_id', $seeker_id)->where('skill_id', $skill_id)->delete();
        return response()->json(['message' => 'Skill removed from job post successfully']);
    }







    public function showeducation($seeker_id) {
        $education = Education::where('seeker_id', $seeker_id)->first();
        return response()->json([
            'education' => $education
        ]);
        
    }


    public function updateeducation(UpdateEducationRequest $request) {
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


    public function deleteeducation($education_id) {
        Education::where('education_id', $education_id)->delete();
        return response()->json(['message' => 'Education deleted successfully']);
        
    }







    public function createexperience(CreateExprienceRequest $request) {
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
        $experience = Experience::where('experience_id', $experience_id)->first();
        return response()->json([
            'experience' => $experience
        ]);
        
    }


    public function updateexperience(UpdateExprienceRequest $request,$experience_id) {
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
        Experience::where('experience_id', $experience_id)->delete();
        return response()->json(['message' => 'Experience deleted successfully']);
        
    }








    public function showSeekerProfile($seeker_id) {
        $seeker = Jobseeker::where('seeker_id', $seeker_id)->first();
        
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
    
        return response()->json([
            'personal_info' => [
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
            'education' => $education
        ]);
    }
    



    
}
