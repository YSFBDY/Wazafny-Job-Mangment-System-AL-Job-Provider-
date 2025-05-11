<?php
use App\Mail\ResetPassword;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Both\AuthController;
use App\Http\Controllers\Both\SkillSearchController;
use App\Http\Controllers\Both\ImagesController;


use App\Http\Controllers\Seeker\SeekerApplicationController;
use App\Http\Controllers\Seeker\SeekerProfileController;
use App\Http\Controllers\Seeker\SeekerJobPostController;
use App\Http\Controllers\Seeker\CompainesController;
use App\Http\Controllers\Seeker\FollowController;
use App\Http\Controllers\Seeker\NotificationController;


use App\Http\Controllers\Company\CompanyApplicationController;
use App\Http\Controllers\Company\CompanyJobPostController;
use App\Http\Controllers\Company\CompanyProfileController;
use App\Http\Controllers\Company\CompanyStaticsController;


use App\Http\Middleware\TokenAuthMiddleware;



// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');




//->middleware(TokenAuthMiddleware::class)




//*******************************************//
//////////////// Both Routes ////////////////
//*******************************************//

Route::controller(AuthController::class)->group(function () {
    Route::post('register/{role}', 'Register'); //for both
    Route::get('check/{user_id}', 'check'); //for both

    Route::post('login', 'Login'); //for both
    Route::post('logout', 'Logout')->middleware('auth:sanctum'); //for both

    Route::post('generate-otp', 'GenerateOtp'); //for both
    Route::post('verify-otp', 'verifyOtp'); //for both
    Route::post('reset-password', 'ResetPassword'); //for both

});





Route::controller(SkillSearchController::class)->group(function () {
    Route::get('skill-search', 'skillSearch');
});





Route::controller(ImagesController::class)->group(function () {
    Route::get('show-profile-img/{user_id}', 'showprofileimg'); //for Both
    Route::post('update-profile-img', 'updateprofileimg'); //for Both
    Route::delete('delete-profile-img/{user_id}', 'deleteProfileImage'); //for Both

    Route::get('show-cover-img/{user_id}', 'showcoverimg'); //for Both
    Route::post('update-cover-img', 'updatecoverimg'); //for Both
    Route::delete('delete-cover-img/{user_id}', 'deleteCoverImage'); //for Both
});















//*******************************************//
//////////////// Seeker Routes ////////////////
//*******************************************//
 
Route::controller(SeekerApplicationController::class)->group(function () {
    Route::post('create-application', 'createappication'); //for seeker

    Route::get('show-applications-seeker/{seeker_id}/{status?}', 'showapplicationsseeker'); //for seeker
    Route::get('show-applications-seeker/{seeker_id}/Pending', 'showapplicationsseeker'); //for seeker
    Route::get('show-applications-seeker/{seeker_id}/Accepted', 'showapplicationsseeker'); //for seeker
    Route::get('show-applications-seeker/{seeker_id}/Rejected', 'showapplicationsseeker'); //for seeker

    Route::get('show-application-seeker/{application_id}', 'showapplicationseeker'); //for seeker
    Route::post('update-application/{application_id}', 'updateapplication'); //for seeker
    Route::delete('delete-application/{application_id}', 'deleteapplication');//for seeker
    Route::get('show-lastest-application-seeker/{user_id}', 'latestApplicationsSeeker'); //for seeker
    
});







Route::controller(SeekerJobPostController::class)->group(function () {
    Route::get('show-job-post/{job_id}/{seeker_id?}', 'showjobpost'); //for seeker

    Route::get('show-job-posts-profile/{company_id}', 'showjobpostsprofile'); // for seeker

    Route::get('search-job', 'jobsearch'); //for seeker

    Route::post('recommended-jobs-posts', 'recommendedjobsposts'); //for seeker



});




Route::controller(SeekerProfileController::class)->group(function () {
    Route::post('create-location', 'createlocation'); //for seeker
    Route::post('create-headline', 'createheadline'); //for seeker

    Route::get('show-personal-info/{seeker_id}', 'showpersonalinfo'); //for seeker
    Route::post('update-personal-info', 'updatepersonalinfo'); //for seeker
    Route::delete('delete-link/{link_id}', 'deletelink'); //for seeker

    Route::get('show-about/{seeker_id}', 'showabout'); //for seeker
    Route::post('update-about', 'updateabout'); //for seeker

    Route::get('show-resume/{seeker_id}', 'showresume'); //for seeker
    Route::post('update-resume', 'updateresume'); //for seeker
    Route::delete('delete-resume/{seeker_id}', 'deleteResume'); //for seeker

    Route::get('show-skills/{seeker_id}', 'showskills'); //for seeker
    Route::post('update-skills', 'updateskills'); //for seeker
    Route::delete('delete-skill/{seeker_id}/{skill_id}', 'deleteskill'); //for seeker

    Route::get('show-education/{seeker_id}', 'showeducation'); //for seeker
    Route::post('update-education', 'updateeducation'); //for seeker
    Route::delete('delete-education/{seeker_id}', 'deleteeducation'); //for seeker

    Route::post('create-experience', 'createexperience'); //for seeker
    Route::get('show-experience/{experience_id}', 'showexperience'); //for seeker
    Route::put('update-experience/{experience_id}', 'updateexperience'); //for seeker
    Route::delete('delete-experience/{experience_id}', 'deleteexperience'); //for seeker

    Route::get('show-seeker-profile/{seeker_id}', 'showseekerprofile'); //for seeker


});




Route::controller(CompainesController::class)->group(function () {
    Route::get('show-compaines', 'showCompaines');
});




Route::controller(FollowController::class)->group(function () {
    Route::post('follow', 'follow');
    Route::delete('unfollow', 'unfollow');
    Route::get('show-followings/{seeker_id}', 'showfollowings');
    Route::get('show-followers/{seeker_id}/{company_id}', 'getfollowstatus');
    
});




Route::controller(NotificationController::class)->group(function () {
    Route::get('show-notifications/{seeker_id}', 'shownotifications');
    Route::delete('delete-notification/{notification_id}', 'deletenotification');
    Route::delete('delete-all-notifications/{seeker_id}', 'deleteAllNotifications');
    
});















//*******************************************//
///////////////  Company Routes ///////////////
//*******************************************//

Route::controller(CompanyProfileController::class)->group(function () {
    Route::post('update-presonal-info', 'updatepersonalinfo'); // for company
    Route::get('show-company-personal-info/{company_id}', 'showpersonalinfo'); // for company

    Route::post('update-extra-info', 'updateextrainfo'); // for company
    Route::get('show-company-extra-info/{company_id}', 'showextrainfo'); // for company

    Route::get('show-company-profile/{company_id}/{seeker_id?}', 'showcompanyprofile'); // for company

    


    
});





Route::controller(CompanyApplicationController::class)->group(function () {
    Route::put('update-application-status/{application_id}', 'updateappllicationstatus'); // for company

    Route::get('show-applications-company/{job_id}', 'showapplicationscompany'); // for company
    Route::get('show-application-company/{application_id}', 'showapplicationcompany'); // for company

    Route::get('show-lastest-applications-company/{company_id}', 'lastestApplicationsCompany'); // for company

});





Route::controller(CompanyJobPostController::class)->group(function () {
    Route::post('create-job-post', 'createjobpost'); // for company

    Route::get('show-job-posts/{company_id}', 'showjobposts'); // for company


    Route::get('show-job-post-info/{job_id}', 'showJobPostInfo'); // for company
    Route::put('update-job-post-info/{job_id}', 'updateJobPostInfo'); // for company

    Route::get('show-job-post-skills/{job_id}', 'showJobPostSkills'); // for company
    Route::delete('delete-job-post-skill/{job_id}/{skill_id}', 'deleteJobSkill'); // for company

    Route::get('show-job-post-sections/{job_id}', 'showJobPostSections'); // for company
    Route::delete('delete-job-post-section/{section_id}', 'deleteJobSection'); // for company
    
    Route::get('show-job-post-questions/{job_id}', 'showJobPostQuestions'); //for both
    Route::delete('delete-job-post-question/{question_id}', 'deleteJobQuestion'); // for company

    Route::put('close-job-post/{job_id}', 'closejobpost'); // for company

    Route::put('activate-job-post/{job_id}', 'ActivateJobPost'); // for company

    Route::delete('delete-job-post/{job_id}', 'deletejobpost'); // for company 

    Route::get('lastest-job-posts/{company_id}', 'lastestJobPosts'); // for company

});




Route::controller(CompanyStaticsController::class)->group(function () {
    Route::get('show-statics/{company_id}', 'showStatics');
});














































// Route::controller(JobPostController::class)->middleware('auth:sanctum')->group(function () {
//     //
// });


























